<?php

namespace App\Console\Commands;

use App\Jobs\SendOfflineOrderToMindbox;
use App\Models\VistaOfflineOrderSyncLog;
use App\Models\VistaOfflineOrderSyncState;
use App\Services\VistaOfflineOrders\VistaOfflineOrdersAggregator;
use App\Services\VistaOfflineOrders\VistaOfflineOrdersQuery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncVistaOfflineOrders extends Command
{
    protected $signature = 'sync:vista-offline-orders {--dry-run : Не создавать jobs, только показать количество}';

    protected $description = 'Инкрементальная синхронизация оффлайн-заказов из Vista (SQL Server) в Mindbox';

    public function handle(
        VistaOfflineOrdersQuery $query,
        VistaOfflineOrdersAggregator $aggregator,
    ): int {
        $dryRun = (bool) $this->option('dry-run');

        $state = VistaOfflineOrderSyncState::query()->first();
        if (!$state) {
            $state = VistaOfflineOrderSyncState::query()->create([
                'last_processed_transaction_id' => 0,
                'target_transaction_id' => null,
            ]);
        }

        // Сначала пытаемся завершить предыдущий запуск (продвинуть last_processed_transaction_id)
        $this->finalizeIfPossible($state);

        // Bootstrap для первого запуска: нельзя забирать всю историю.
        // Если last_processed_transaction_id = 0, ограничиваемся заказами за последний час:
        // вычисляем минимальный transaction_id за последний час и выставляем last_processed = minId - 1,
        // чтобы основной (обязательный) SQL выбрал только этот диапазон.
        if ((int) $state->last_processed_transaction_id === 0 && $state->target_transaction_id === null) {
            $bootstrapMinId = $this->getBootstrapMinTransactionIdLastHour();
            if ($bootstrapMinId !== null && $bootstrapMinId > 0) {
                $state->update([
                    'last_processed_transaction_id' => $bootstrapMinId - 1,
                ]);
            }
        }

        $lastProcessed = (int) $state->last_processed_transaction_id;
        $this->info("last_processed_transaction_id = {$lastProcessed}");

        $rows = $query->stream($lastProcessed);
        $orders = $aggregator->aggregate($rows);

        $countOrders = 0;
        $maxTransactionId = null;

        foreach ($orders as $order) {
            $transactionId = (int) ($order['transaction_id'] ?? 0);
            $maxTransactionId = $maxTransactionId === null ? $transactionId : max($maxTransactionId, $transactionId);
            $countOrders++;

            $header = $order['header'] ?? [];
            $membershipId = (string) ($header['transaction_membershipid'] ?? '');

            // Лог всегда создаём, чтобы видеть это в админке
            $log = VistaOfflineOrderSyncLog::query()->firstOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'status' => VistaOfflineOrderSyncLog::STATUS_PENDING,
                    'attempts' => 0,
                    'source_data' => $order,
                ]
            );

            // Если membershipID отсутствует — помечаем failed и НЕ диспатчим job.
            // Но оставляем возможность Retry из админки (вдруг данные появятся позже).
            if (trim($membershipId) === '') {
                $log->update([
                    'status' => VistaOfflineOrderSyncLog::STATUS_FAILED,
                    'source_data' => $order,
                    'error_message' => 'Отсутствует обязательный transaction_membershipid (membershipID)',
                ]);
                continue;
            }

            // Обновляем source_data, чтобы Retry работал даже если данные изменились
            $log->update([
                'status' => VistaOfflineOrderSyncLog::STATUS_PENDING,
                'source_data' => $order,
                'error_message' => null,
            ]);

            if (!$dryRun) {
                SendOfflineOrderToMindbox::dispatch($order);
            }
        }

        $this->info("Найдено заказов: {$countOrders}");

        if ($dryRun) {
            $this->warn('DRY RUN: jobs не создавались');
            return Command::SUCCESS;
        }

        // Фиксируем watermark (target_transaction_id) только если реально были новые заказы
        if ($maxTransactionId !== null) {
            $state->update([
                'target_transaction_id' => $maxTransactionId,
            ]);
            $this->info("target_transaction_id установлен в {$maxTransactionId}");
        }

        return Command::SUCCESS;
    }

    /**
     * Продвигает last_processed_transaction_id только если ВСЕ заказы
     * от (last_processed + 1) до target_transaction_id имеют статус success.
     */
    private function finalizeIfPossible(VistaOfflineOrderSyncState $state): void
    {
        if ($state->target_transaction_id === null) {
            return;
        }

        $last = (int) $state->last_processed_transaction_id;
        $target = (int) $state->target_transaction_id;

        if ($target <= $last) {
            $state->update(['target_transaction_id' => null]);
            return;
        }

        // Проверяем, есть ли НЕуспешные или отсутствующие логи в диапазоне
        $totalInRange = VistaOfflineOrderSyncLog::query()
            ->whereBetween('transaction_id', [$last + 1, $target])
            ->count();

        $successInRange = VistaOfflineOrderSyncLog::query()
            ->whereBetween('transaction_id', [$last + 1, $target])
            ->where('status', VistaOfflineOrderSyncLog::STATUS_SUCCESS)
            ->count();

        if ($totalInRange === 0) {
            // Нечего финализировать — защитный кейс
            return;
        }

        if ($totalInRange === $successInRange) {
            $state->update([
                'last_processed_transaction_id' => $target,
                'target_transaction_id' => null,
            ]);
            Log::info('Vista offline orders sync finalized', [
                'last_processed_transaction_id' => $target,
            ]);
        }
    }

    /**
     * Возвращает минимальный transaction_id за последний час (с теми же базовыми фильтрами),
     * чтобы сделать безопасный первый запуск без чтения всей базы.
     */
    private function getBootstrapMinTransactionIdLastHour(): ?int
    {
        $sql = <<<SQL
SELECT MIN(trans.transaction_id) AS min_id
FROM [VISTALOYALTY].[dbo].[cognetic_data_transaction] AS trans
WHERE
    trans.transaction_salesChannel IN (1, 2, 8)
    AND trans.transaction_time >= DATEADD(HOUR, -1, GETDATE())
SQL;

        $row = DB::connection('vista')->selectOne($sql);
        if (!$row) {
            return null;
        }

        // selectOne возвращает stdClass
        $minId = $row->min_id ?? null;
        if ($minId === null) {
            return null;
        }

        return (int) $minId;
    }
}


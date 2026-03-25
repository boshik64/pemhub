<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MindboxUpdateVistaOfflineOrderStatus extends Command
{
    protected $signature = 'mindbox:update-vista-offline-order-status
        {--min-transaction-id=864794697 : transaction_id > ...}
        {--created-before="2026-03-21 08:42:12" : created_at < ...}
        {--order-lines-status=completed : orderLinesStatus для Mindbox}
        {--limit=500 : ограничение на количество обработанных строк}
        {--throttle-ms=200 : пауза между запросами, мс}
        {--dry-run : только вывести какие запросы были бы отправлены}';

    protected $description = 'Send Website.UpdateOrderStatus to Mindbox for selected vista_offline_order_sync_logs rows (read-only local statuses)';

    public function handle(): int
    {
        $minTransactionId = (int) $this->option('min-transaction-id');
        $createdBeforeRaw = (string) $this->option('created-before');
        $orderLinesStatus = (string) $this->option('order-lines-status');
        $limit = (int) $this->option('limit');
        $throttleMs = (int) $this->option('throttle-ms');
        $dryRun = (bool) $this->option('dry-run');

        $createdBefore = $this->parseDateTime($createdBeforeRaw);

        $baseUrl = rtrim((string) config('services.mindbox.base_url'), '/');
        $endpointId = (string) config('services.mindbox.endpoint_id');
        $secretKey = (string) config('services.mindbox.secret_key');
        $timeout = (int) config('services.mindbox.timeout', 20);

        if ($baseUrl === '' || $endpointId === '' || $secretKey === '') {
            $this->error('Mindbox конфигурация не настроена (MINDBOX_BASE_URL / MINDBOX_ENDPOINT_ID / MINDBOX_SECRET_KEY).');
            return self::FAILURE;
        }

        $url = $baseUrl . '/v3/operations/sync'
            . '?endpointId=' . urlencode($endpointId)
            . '&operation=' . urlencode('Website.UpdateOrderStatus');

        // Важно: локальные статусы не трогаем (только читаем transaction_id).
        $rows = DB::table(DB::raw('pemdb.vista_offline_order_sync_logs'))
            ->where('transaction_id', '>', $minTransactionId)
            ->where('created_at', '<', $createdBefore)
            ->orderBy('transaction_id', 'asc')
            ->limit($limit)
            ->get(['transaction_id']);

        $this->info(sprintf(
            'Selected %d rows (transaction_id > %d, created_at < %s).',
            $rows->count(),
            $minTransactionId,
            $createdBefore
        ));

        if ($rows->isEmpty()) {
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('DRY RUN: Mindbox не вызываем.');
        }

        $processed = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $transactionId = (int) $row->transaction_id;
            // В БД у тебя лежит только transaction_id, в Mindbox нужно "vista_transaction_id_{id}"
            $websiteId = 'vista_transaction_id_' . $transactionId;

            $payload = [
                'orderLinesStatus' => $orderLinesStatus,
                'order' => [
                    'ids' => [
                        'websiteID' => $websiteId,
                    ],
                ],
            ];

            $processed++;

            if ($dryRun) {
                $this->line("[dry-run] transaction_id={$transactionId} websiteID={$websiteId} orderLinesStatus={$orderLinesStatus}");
                continue;
            }

            try {
                $response = Http::timeout($timeout)
                    ->acceptJson()
                    ->withHeaders([
                        'Authorization' => 'SecretKey ' . $secretKey,
                        'Content-Type' => 'application/json; charset=utf-8',
                    ])
                    ->post($url, $payload);

                if (!$response->successful()) {
                    $failed++;
                    $this->warn("FAIL transaction_id={$transactionId} HTTP={$response->status()}");

                    Log::error('Mindbox Website.UpdateOrderStatus failed', [
                        'transaction_id' => $transactionId,
                        'websiteID' => $websiteId,
                        'http_status' => $response->status(),
                        'body' => (string) $response->body(),
                    ]);
                    continue;
                }

                $this->line("OK transaction_id={$transactionId} websiteID={$websiteId}");
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("EXCEPTION transaction_id={$transactionId}: {$e->getMessage()}");

                Log::error('Mindbox Website.UpdateOrderStatus exception', [
                    'transaction_id' => $transactionId,
                    'websiteID' => $websiteId,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                if ($throttleMs > 0) {
                    usleep($throttleMs * 1000);
                }
            }
        }

        $this->info("Done. processed={$processed}, failed={$failed}");
        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function parseDateTime(string $value): string
    {
        // Пользователь/CLI иногда передаёт дату в кавычках, например: "\"2026-03-21 08:42:12\""
        // Нужен формат как в MySQL: 'Y-m-d H:i:s'
        $normalized = trim($value);
        $normalized = trim($normalized, "\"'");

        return Carbon::parse($normalized)->format('Y-m-d H:i:s');
    }
}


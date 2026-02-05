<?php

namespace App\Jobs;

use App\Models\VistaOfflineOrderSyncLog;
use App\Services\Mindbox\MindboxClient;
use App\Services\Mindbox\OfflineOrderPayloadBuilder;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOfflineOrderToMindbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array{transaction_id:int, header:array<string,mixed>, lines:array<int,array<string,mixed>>}
     */
    public array $order;

    public int $transactionId;

    public int $tries = 5;

    public function __construct(array $order)
    {
        $this->order = $order;
        $this->transactionId = (int) ($order['transaction_id'] ?? 0);
    }

    public function handle(MindboxClient $client, OfflineOrderPayloadBuilder $builder): void
    {
        $log = VistaOfflineOrderSyncLog::query()
            ->firstOrCreate(
                ['transaction_id' => $this->transactionId],
                [
                    'status' => VistaOfflineOrderSyncLog::STATUS_PENDING,
                    'attempts' => 0,
                    'source_data' => $this->order,
                ],
            );

        $header = $this->order['header'] ?? [];
        $membershipId = (string) ($header['transaction_membershipid'] ?? '');

        // membershipID обязателен. Если его нет — фиксируем ошибку и не шлем в Mindbox.
        if (trim($membershipId) === '') {
            $log->update([
                'status' => VistaOfflineOrderSyncLog::STATUS_FAILED,
                'attempts' => $log->attempts + 1,
                'source_data' => $this->order,
                'error_message' => 'Отсутствует обязательный transaction_membershipid (membershipID)',
            ]);
            return;
        }

        $payload = $builder->build($this->order);

        $log->update([
            'status' => VistaOfflineOrderSyncLog::STATUS_PENDING,
            'attempts' => $log->attempts + 1,
            'source_data' => $this->order,
            'request_payload' => $payload,
            'error_message' => null,
        ]);

        try {
            $response = $client->sendOfflineOrderWithQuery($payload);

            $log->update([
                'response_payload' => $response->json(),
            ]);

            if ($response->successful()) {
                $log->update([
                    'status' => VistaOfflineOrderSyncLog::STATUS_SUCCESS,
                    'error_message' => null,
                ]);
            } else {
                $log->update([
                    'status' => VistaOfflineOrderSyncLog::STATUS_FAILED,
                    'error_message' => 'Mindbox response not successful: ' . $response->status(),
                ]);

                // Даем Laravel Queue повторить
                throw new Exception('Mindbox error: ' . $response->status());
            }
        } catch (Exception $e) {
            Log::error('Ошибка отправки оффлайн-заказа в Mindbox', [
                'transaction_id' => $this->transactionId,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => VistaOfflineOrderSyncLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}


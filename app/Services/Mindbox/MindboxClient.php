<?php

namespace App\Services\Mindbox;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MindboxClient
{
    /**
     * Mindbox требует endpointId и operation в query string.
     */
    public function sendOfflineOrderWithQuery(array $payload): Response
    {
        $baseUrl = rtrim((string) config('services.mindbox.base_url'), '/');
        $endpointId = (string) config('services.mindbox.endpoint_id');
        $secretKey = (string) config('services.mindbox.secret_key');
        $timeout = (int) config('services.mindbox.timeout', 20);

        if ($baseUrl === '' || $endpointId === '' || $secretKey === '') {
            throw new RuntimeException('Mindbox конфигурация не настроена (MINDBOX_BASE_URL / MINDBOX_ENDPOINT_ID / MINDBOX_SECRET_KEY)');
        }

        $url = $baseUrl . '/v3/operations/async'
            . '?endpointId=' . urlencode($endpointId)
            . '&operation=' . urlencode('Offline.SaveOfflineOrder');

        return Http::timeout($timeout)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'SecretKey ' . $secretKey,
                'Content-Type' => 'application/json',
            ])
            ->post($url, $payload);
    }
}


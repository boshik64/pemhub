<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CertExpirationAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда уведомляет о просроченных сертификатах';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $merchants = Merchant::all();
        $expiresMerchants = [];
        $expiredMerchants = [];

        foreach ($merchants as $merchant) {
            if ($merchant->getExpiryStatus() == Merchant::CERT_EXPIRES) {
                $expiresMerchants[] = $merchant;
            } elseif ($merchant->getExpiryStatus() == Merchant::CERT_EXPIRED) {
                $expiredMerchants[] = $merchant;
            }
        }

        if (empty($expiresMerchants) && empty($expiresMerchants)) {
            return 0;
        }

        $message = '';

        if (!empty($expiredMerchants)) {
            $message .= 'Список истёкших сертификатов' . PHP_EOL;

            foreach ($expiredMerchants as $merchant) {
                $message .= '- MID: ' . $merchant->mid . '  👀||👀  ' . $merchant->department_name . PHP_EOL;
            }
        }

        if (!empty($expiresMerchants)) {
            $message .= 'Список истекающих сертификатов' . PHP_EOL;

            foreach ($expiresMerchants as $merchant) {
                $message .= '- MID: ' . $merchant->mid . '  👀||👀  ' . $merchant->department_name . PHP_EOL;
            }
        }

        $this->sendToTelegram($message);
    }

    public function sendToTelegram(string $message)
    {
        $telegramToken = config('services.telegram.token');
        $chats = config('services.telegram.chats');
        $apiUrl = "https://api.telegram.org/bot$telegramToken/sendMessage";

        foreach ($chats as $chat_id) {
            Http::post($apiUrl, [
                'text' => $message,
                'chat_id' => $chat_id
            ]);
        }
    }
}

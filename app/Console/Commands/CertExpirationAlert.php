<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use Carbon\Carbon;
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
        $merchants = Merchant::all()->sortByDesc('workstation_id');
        $expiresMerchants = [];
        $expiredMerchants = [];

        foreach ($merchants as $merchant) {
            if ($merchant->getExpiryStatus() == Merchant::CERT_EXPIRES) {
                $expiresMerchants[] = $merchant;
            } elseif ($merchant->getExpiryStatus() == Merchant::CERT_EXPIRED) {
                $expiredMerchants[] = $merchant;
            }
        }

        if (empty($expiresMerchants) && empty($expiredMerchants)) {
            return 0;
        }

        $message = '';

        if (!empty($expiredMerchants)) {
            $message .= '<b>🛑 Список истёкших сертификатов</b>' . PHP_EOL;

            foreach ($expiredMerchants as $merchant) {
                $message .= $merchant->mid . ' ‼️ ' . $merchant->department_name . ' 🕕 ' . 'Просрочено на: ' . Carbon::now()->diffInDays($merchant->next_update) . 'д.' . PHP_EOL;
            }
        }

        if (!empty($expiresMerchants)) {
            $message .= '<b>⚠️ Список истекающих сертификатов</b>' . PHP_EOL;

            foreach ($expiresMerchants as $merchant) {
                $message .= $merchant->mid . ' ❗️ ' . $merchant->department_name . ' 🕕 ' . 'Осталось: ' . Carbon::now()->diffInDays($merchant->next_update) . 'д.' . PHP_EOL;
            }
        }
//        dd($message);
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
                'chat_id' => $chat_id,
                'parse_mode' => 'html'
            ]);
        }
    }
}

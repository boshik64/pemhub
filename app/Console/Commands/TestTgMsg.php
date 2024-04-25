<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTgMsg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:test-msg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing msg for cert';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendToTelegram('Он вам не Тикуш');
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

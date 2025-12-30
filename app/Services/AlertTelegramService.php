<?php

namespace App\Services;

use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AlertTelegramService
{
    private string $token;
    private array $chats;

    public function __construct()
    {
        $this->token = config('services.alert_telegram.token');
        $this->chats = config('services.alert_telegram.chats', []);

        if (empty($this->token)) {
            throw new Exception('Telegram токен для алертов не настроен');
        }

        if (empty($this->chats)) {
            Log::warning('Telegram chat_id для алертов не настроен - сообщения не будут отправлены');
        }
    }

    /**
     * Отправляет сообщение в Telegram
     * Разбивает длинные сообщения на части (лимит Telegram ~4096 символов)
     *
     * @param string $message
     * @return bool
     */
    public function sendMessage(string $message): bool
    {
        if (empty($message)) {
            return false;
        }

        if (empty($this->chats)) {
            Log::warning('Попытка отправить сообщение в Telegram, но chat_ids не настроены', [
                'message_preview' => substr($message, 0, 100),
            ]);
            return false;
        }

        $apiUrl = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $maxLength = 4000; // Оставляем запас от лимита 4096
        $success = true;

        // Разбиваем сообщение на части, если оно слишком длинное
        $messages = $this->splitMessage($message, $maxLength);

        foreach ($this->chats as $chatId) {
            foreach ($messages as $index => $messagePart) {
                try {
                    $response = Http::timeout(10)->post($apiUrl, [
                        'text' => $messagePart,
                        'chat_id' => $chatId,
                        'parse_mode' => 'HTML',
                    ]);

                    if (!$response->successful()) {
                        Log::error('Ошибка отправки сообщения в Telegram', [
                            'chat_id' => $chatId,
                            'part' => $index + 1,
                            'response' => $response->body(),
                        ]);
                        $success = false;
                    } else {
                        // Небольшая задержка между сообщениями, чтобы не превысить rate limit
                        if ($index < count($messages) - 1) {
                            usleep(500000); // 0.5 секунды
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Исключение при отправке сообщения в Telegram', [
                        'chat_id' => $chatId,
                        'part' => $index + 1,
                        'error' => $e->getMessage(),
                    ]);
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Разбивает сообщение на части по максимальной длине
     *
     * @param string $message
     * @param int $maxLength
     * @return array
     */
    private function splitMessage(string $message, int $maxLength): array
    {
        if (mb_strlen($message) <= $maxLength) {
            return [$message];
        }

        $parts = [];
        $lines = explode("\n", $message);
        $currentPart = '';
        $partNumber = 1;

        foreach ($lines as $line) {
            $lineWithNewline = $line . "\n";
            
            // Если добавление строки не превысит лимит
            if (mb_strlen($currentPart . $lineWithNewline) <= $maxLength) {
                $currentPart .= $lineWithNewline;
            } else {
                // Сохраняем текущую часть
                if (!empty($currentPart)) {
                    $parts[] = trim($currentPart);
                    $currentPart = '';
                }
                
                // Если сама строка длиннее лимита, разбиваем её
                if (mb_strlen($line) > $maxLength) {
                    $chunks = mb_str_split($line, $maxLength - 10);
                    foreach ($chunks as $chunk) {
                        $parts[] = $chunk;
                    }
                } else {
                    $currentPart = $lineWithNewline;
                }
            }
        }

        // Добавляем последнюю часть
        if (!empty($currentPart)) {
            $parts[] = trim($currentPart);
        }

        // Добавляем нумерацию частей, если сообщение разбито
        if (count($parts) > 1) {
            $totalParts = count($parts);
            foreach ($parts as $index => &$part) {
                $part = "📄 <b>Часть " . ($index + 1) . " из {$totalParts}</b>\n\n" . $part;
            }
        }

        return $parts;
    }

    /**
     * Форматирует сообщение для незавершенных задач заказов
     *
     * @param array $tasks
     * @return string
     */
    public function formatUnfinishedOrderTasksMessage(array $tasks): string
    {
        if (empty($tasks)) {
            return '';
        }

        $yesterdayDate = (new DateTime('yesterday'))->format('d.m.Y');

        $message = "⚠️ <b>Отложенные возвраты за {$yesterdayDate}</b>\n\n";

        foreach ($tasks as $task) {
            $bookingId = $task['booking_id'] ?? 'N/A';
            $theatreName = $task['name'] ?? 'N/A';
            $vistaCinemaId = $task['vista_cinema_id'] ?? 'N/A';
            $taskType = $task['task'] ?? 'N/A';
            $key = $task['key'] ?? 'N/A';
            $createdAt = $task['created_at'] ?? 'N/A';


            $message .= "<b>🔙 Данные по возврату:</b>\n";
            $message .= "Дата создания: <b>{$createdAt}</b>\n";
            $message .= "Код брони: <b>{$bookingId}</b>\n";
            $message .= "Кинотеатр: <b>{$theatreName}</b>\n";
            $message .= "Vista Cinema ID: <b>{$vistaCinemaId}</b>\n";
            $message .= "Тип задачи: <b>{$taskType}</b>\n";
            $message .= "Ключ заказа: <b>{$key}</b>\n\n";
        }

        return $message;
    }

    /**
     * Форматирует сообщение для незавершенных автовозвратов
     *
     * @param array $refunds
     * @return string
     */
    public function formatUnfinishedAutoRefundsMessage(array $refunds): string
    {
        if (empty($refunds)) {
            return '';
        }

        $yesterdayDate = (new DateTime('yesterday'))->format('d.m.Y');

        $message = "❗️ <b>Незавершенные автовозвраты из формы за {$yesterdayDate}</b>\n\n";
    
        foreach ($refunds as $refund) {
            $bookingId = $refund['booking_id'] ?? 'N/A';
            $theatreName = $refund['name'] ?? 'N/A';
            $client = $refund['client'] ?? 'N/A';
            $email = $refund['email'] ?? 'N/A';
            $phone = $refund['phone'] ?? 'N/A';
            $createdAt = $refund['created_at'] ?? 'N/A';

            $message .= "<b>🎫 Данные по заказу:</b>\n";
            $message .= "Дата создания: <b>{$createdAt}</b>\n";
            $message .= "Код брони: <b>{$bookingId}</b>\n";
            $message .= "Кинотеатр: <b>{$theatreName}</b>\n";
            $message .= "Канал продаж: <b>{$client}</b>\n";
            $message .= "Email: <b>{$email}</b>\n";
            $message .= "Телефон: <b>+7{$phone}</b>\n\n";
        }

        return $message;
    }
}


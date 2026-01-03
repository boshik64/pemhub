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
    private array $territoryChats;
    private array $commonChatIds;
    private array $cinemaTerritoryMap;
    private ?string $defaultTerritory;
    private bool $silentFail;

    public function __construct()
    {
        $this->token = config('services.alert_telegram.token');
        $this->chats = config('services.alert_telegram.chats', []);
        $this->territoryChats = config('services.alert_telegram.territories', []);
        $this->commonChatIds = config('services.alert_telegram.common_chat_ids', []);
        $this->cinemaTerritoryMap = config('cinema_territories.cinema_territory_map', []);
        $this->defaultTerritory = config('cinema_territories.default_territory');
        $this->silentFail = config('cinema_territories.silent_fail', true);

        if (empty($this->token)) {
            throw new Exception('Telegram токен для алертов не настроен');
        }

        if (empty($this->chats) && empty($this->commonChatIds)) {
            Log::warning('Telegram chat_id для алертов не настроен - сообщения не будут отправлены');
        }
    }

    /**
     * Определяет территорию для указанного cinema_id
     *
     * @param string|int|null $cinemaId Vista Cinema ID из внешней БД
     * @return string|null Код территории или null, если не найдена
     */
    public function getTerritoryByCinemaId($cinemaId): ?string
    {
        if ($cinemaId === null || $cinemaId === 'N/A' || $cinemaId === '') {
            return $this->defaultTerritory;
        }

        // Преобразуем в строку для сравнения
        $cinemaIdStr = (string) $cinemaId;

        // Ищем в маппинге
        if (isset($this->cinemaTerritoryMap[$cinemaIdStr])) {
            return $this->cinemaTerritoryMap[$cinemaIdStr];
        }

        // Если не найдено, возвращаем территорию по умолчанию
        return $this->defaultTerritory;
    }

    /**
     * Получает список чатов для указанной территории
     *
     * @param string|null $territoryCode Код территории
     * @return array Массив chat_id для территории
     */
    public function getChatsForTerritory(?string $territoryCode): array
    {
        if ($territoryCode === null) {
            return [];
        }

        return $this->territoryChats[$territoryCode] ?? [];
    }

    /**
     * Отправляет сообщение в указанные чаты
     * Разбивает длинные сообщения на части (лимит Telegram ~4096 символов)
     *
     * @param string $message Текст сообщения
     * @param array $chatIds Массив chat_id для отправки
     * @return bool Успешность отправки
     */
    public function sendMessageToChats(string $message, array $chatIds): bool
    {
        if (empty($message) || empty($chatIds)) {
            return false;
        }

        $apiUrl = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $maxLength = 4000; // Оставляем запас от лимита 4096
        $success = true;

        // Разбиваем сообщение на части, если оно слишком длинное
        $messages = $this->splitMessage($message, $maxLength);

        foreach ($chatIds as $chatId) {
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
     * Отправляет сообщение в Telegram (старый метод для обратной совместимости)
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

        // Используем старые чаты, если новые не настроены
        $chatsToUse = !empty($this->chats) ? $this->chats : $this->commonChatIds;

        if (empty($chatsToUse)) {
            Log::warning('Попытка отправить сообщение в Telegram, но chat_ids не настроены', [
                'message_preview' => substr($message, 0, 100),
            ]);
            return false;
        }

        return $this->sendMessageToChats($message, $chatsToUse);
    }

    /**
     * Отправляет алерт с маршрутизацией по территориям
     * 
     * Логика:
     * 1. Определяет территорию по cinema_id
     * 2. Отправляет алерт в чаты соответствующей территории
     * 3. Дублирует ВСЕ алерты в общий чат
     *
     * @param string $message Текст сообщения
     * @param string|int|null $cinemaId Vista Cinema ID из алерта
     * @return bool Успешность отправки
     */
    public function sendAlertWithRouting(string $message, $cinemaId = null): bool
    {
        if (empty($message)) {
            return false;
        }

        $success = true;

        // Определяем территорию
        $territory = $this->getTerritoryByCinemaId($cinemaId);
        $territoryChatIds = [];

        // Получаем чаты для территории
        if ($territory !== null) {
            $territoryChatIds = $this->getChatsForTerritory($territory);

            if (empty($territoryChatIds)) {
                if ($this->silentFail) {
                    Log::warning('Чат для территории не найден, алерт будет отправлен только в общий чат', [
                        'territory' => $territory,
                        'cinema_id' => $cinemaId,
                    ]);
                } else {
                    throw new Exception("Чат для территории '{$territory}' не настроен");
                }
            } else {
                // Отправляем в чаты территории
                $territorySuccess = $this->sendMessageToChats($message, $territoryChatIds);
                $success = $success && $territorySuccess;
            }
        } else {
            // Неизвестный cinema_id - логируем
            Log::info('Cinema ID не найден в маппинге, алерт будет отправлен только в общий чат', [
                'cinema_id' => $cinemaId,
            ]);
        }

        // Всегда отправляем в общий чат (дублирование)
        if (!empty($this->commonChatIds)) {
            $commonSuccess = $this->sendMessageToChats($message, $this->commonChatIds);
            $success = $success && $commonSuccess;
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
     * Форматирует одно сообщение для задачи заказа
     *
     * @param array $task Данные задачи
     * @param string $yesterdayDate Дата вчерашнего дня
     * @return string
     */
    private function formatSingleOrderTaskMessage(array $task, string $yesterdayDate): string
    {
        $bookingId = $task['booking_id'] ?? 'N/A';
        $theatreName = $task['name'] ?? 'N/A';
        $vistaCinemaId = $task['vista_cinema_id'] ?? 'N/A';
        $taskType = $task['task'] ?? 'N/A';
        $key = $task['key'] ?? 'N/A';
        $createdAt = $task['created_at'] ?? 'N/A';

        $message = "⚠️ <b>Отложенный возврат за {$yesterdayDate}</b>\n\n";
        $message .= "<b>🔙 Данные по возврату:</b>\n";
        $message .= "Дата создания: <b>{$createdAt}</b>\n";
        $message .= "Код брони: <b>{$bookingId}</b>\n";
        $message .= "Кинотеатр: <b>{$theatreName}</b>\n";
        $message .= "Vista Cinema ID: <b>{$vistaCinemaId}</b>\n";
        $message .= "Тип задачи: <b>{$taskType}</b>\n";
        $message .= "Ключ заказа: <b>{$key}</b>";

        return $message;
    }

    /**
     * Форматирует сообщение для незавершенных задач заказов (с группировкой по территориям)
     *
     * @param array $tasks
     * @return array Массив сообщений, сгруппированных по территориям: ['IT_T3ode' => 'message', ...]
     */
    private function formatOrderTasksByTerritory(array $tasks): array
    {
        if (empty($tasks)) {
            return [];
        }

        $yesterdayDate = (new DateTime('yesterday'))->format('d.m.Y');
        $groupedByTerritory = [];

        foreach ($tasks as $task) {
            $vistaCinemaId = $task['vista_cinema_id'] ?? null;
            $territory = $this->getTerritoryByCinemaId($vistaCinemaId) ?? 'unknown';

            if (!isset($groupedByTerritory[$territory])) {
                $groupedByTerritory[$territory] = [];
            }

            $groupedByTerritory[$territory][] = $task;
        }

        $messages = [];
        foreach ($groupedByTerritory as $territory => $territoryTasks) {
            $message = "⚠️ <b>Отложенные возвраты за {$yesterdayDate}</b>\n\n";
            $message .= "<b>Территория: {$territory}</b>\n";
            $message .= "<b>Количество: " . count($territoryTasks) . "</b>\n\n";

            foreach ($territoryTasks as $task) {
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

            $messages[$territory] = trim($message);
        }

        return $messages;
    }

    /**
     * Форматирует сообщение для незавершенных задач заказов (старый метод для обратной совместимости)
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
     * Отправляет алерты о незавершенных задачах заказов с маршрутизацией по территориям
     *
     * @param array $tasks Массив задач
     * @return bool Успешность отправки
     */
    public function sendUnfinishedOrderTasksWithRouting(array $tasks): bool
    {
        if (empty($tasks)) {
            return true;
        }

        $success = true;

        // Группируем по территориям
        $groupedMessages = $this->formatOrderTasksByTerritory($tasks);

        // Отправляем в чаты по территориям
        foreach ($groupedMessages as $territory => $message) {
            if ($territory === 'unknown') {
                // Для неизвестных территорий отправляем только в общий чат
                if (!empty($this->commonChatIds)) {
                    $commonSuccess = $this->sendMessageToChats($message, $this->commonChatIds);
                    $success = $success && $commonSuccess;
                }
            } else {
                // Отправляем в чаты территории
                $territoryChatIds = $this->getChatsForTerritory($territory);
                if (!empty($territoryChatIds)) {
                    $territorySuccess = $this->sendMessageToChats($message, $territoryChatIds);
                    $success = $success && $territorySuccess;
                } elseif ($this->silentFail) {
                    Log::warning('Чат для территории не найден', [
                        'territory' => $territory,
                    ]);
                } else {
                    throw new Exception("Чат для территории '{$territory}' не настроен");
                }
            }
        }

        // Всегда отправляем общее сообщение в общий чат
        if (!empty($this->commonChatIds)) {
            $allTasksMessage = $this->formatUnfinishedOrderTasksMessage($tasks);
            $commonSuccess = $this->sendMessageToChats($allTasksMessage, $this->commonChatIds);
            $success = $success && $commonSuccess;
        }

        return $success;
    }

    /**
     * Форматирует сообщение для незавершенных автовозвратов (с группировкой по территориям)
     *
     * @param array $refunds
     * @return array Массив сообщений, сгруппированных по территориям: ['IT_T3ode' => 'message', ...]
     */
    private function formatAutoRefundsByTerritory(array $refunds): array
    {
        if (empty($refunds)) {
            return [];
        }

        $yesterdayDate = (new DateTime('yesterday'))->format('d.m.Y');
        $groupedByTerritory = [];

        foreach ($refunds as $refund) {
            // Получаем vista_cinema_id, если он есть (может отсутствовать в текущей версии)
            $vistaCinemaId = $refund['vista_cinema_id'] ?? null;
            $territory = $this->getTerritoryByCinemaId($vistaCinemaId) ?? 'unknown';

            if (!isset($groupedByTerritory[$territory])) {
                $groupedByTerritory[$territory] = [];
            }

            $groupedByTerritory[$territory][] = $refund;
        }

        $messages = [];
        foreach ($groupedByTerritory as $territory => $territoryRefunds) {
            $message = "❗️ <b>Незавершенные автовозвраты из формы за {$yesterdayDate}</b>\n\n";
            $message .= "<b>Территория: {$territory}</b>\n";
            $message .= "<b>Количество: " . count($territoryRefunds) . "</b>\n\n";

            foreach ($territoryRefunds as $refund) {
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

            $messages[$territory] = trim($message);
        }

        return $messages;
    }

    /**
     * Форматирует сообщение для незавершенных автовозвратов (старый метод для обратной совместимости)
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

    /**
     * Отправляет алерты о незавершенных автовозвратах с маршрутизацией по территориям
     *
     * @param array $refunds Массив автовозвратов
     * @return bool Успешность отправки
     */
    public function sendUnfinishedAutoRefundsWithRouting(array $refunds): bool
    {
        if (empty($refunds)) {
            return true;
        }

        $success = true;

        // Группируем по территориям
        $groupedMessages = $this->formatAutoRefundsByTerritory($refunds);

        // Отправляем в чаты по территориям
        foreach ($groupedMessages as $territory => $message) {
            if ($territory === 'unknown') {
                // Для неизвестных территорий отправляем только в общий чат
                if (!empty($this->commonChatIds)) {
                    $commonSuccess = $this->sendMessageToChats($message, $this->commonChatIds);
                    $success = $success && $commonSuccess;
                }
            } else {
                // Отправляем в чаты территории
                $territoryChatIds = $this->getChatsForTerritory($territory);
                if (!empty($territoryChatIds)) {
                    $territorySuccess = $this->sendMessageToChats($message, $territoryChatIds);
                    $success = $success && $territorySuccess;
                } elseif ($this->silentFail) {
                    Log::warning('Чат для территории не найден', [
                        'territory' => $territory,
                    ]);
                } else {
                    throw new Exception("Чат для территории '{$territory}' не настроен");
                }
            }
        }

        // Всегда отправляем общее сообщение в общий чат
        if (!empty($this->commonChatIds)) {
            $allRefundsMessage = $this->formatUnfinishedAutoRefundsMessage($refunds);
            $commonSuccess = $this->sendMessageToChats($allRefundsMessage, $this->commonChatIds);
            $success = $success && $commonSuccess;
        }

        return $success;
    }
}


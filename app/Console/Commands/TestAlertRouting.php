<?php

namespace App\Console\Commands;

use App\Services\AlertTelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestAlertRouting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:alert-routing {--cinema-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование маршрутизации алертов по территориям';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Диагностика конфигурации маршрутизации алертов ===');
        $this->newLine();

        // Проверка конфигурации
        $this->info('1. Проверка конфигурации:');
        $token = config('services.alert_telegram.token');
        $commonChatIds = config('services.alert_telegram.common_chat_ids', []);
        $territories = config('services.alert_telegram.territories', []);
        $cinemaMap = config('cinema_territories.cinema_territory_map', []);

        $this->line('   Token: ' . ($token ? '✅ Настроен' : '❌ Не настроен'));
        $this->line('   Common Chat IDs: ' . (empty($commonChatIds) ? '❌ Не настроен (ОБЯЗАТЕЛЬНО!)' : '✅ ' . count($commonChatIds) . ' чат(ов): ' . implode(', ', $commonChatIds)));
        $this->line('   Territories configured: ' . count(array_filter($territories, fn($t) => !empty($t))) . ' из 4');
        $this->line('   Cinema mapping: ' . count($cinemaMap) . ' записей');
        $this->newLine();

        if (empty($commonChatIds)) {
            $this->error('ОШИБКА: ALERT_TELEGRAM_COMMON_CHAT_IDS не настроен!');
            $this->error('Добавьте в .env: ALERT_TELEGRAM_COMMON_CHAT_IDS=ваш_chat_id');
            return Command::FAILURE;
        }

        // Проверка территорий
        $this->info('2. Детали территорий:');
        foreach (['IT_T1', 'IT_T2', 'IT_T3', 'IT_T4'] as $territory) {
            $chats = $territories[$territory] ?? [];
            $status = empty($chats) ? '⚠️  Не настроен' : '✅ ' . count($chats) . ' чат(ов)';
            $this->line("   {$territory}: {$status}");
            if (!empty($chats)) {
                $this->line("      Chat IDs: " . implode(', ', $chats));
            }
        }
        $this->newLine();

        // Проверка маппинга
        $this->info('3. Маппинг cinema_id → территория:');
        if (empty($cinemaMap)) {
            $this->warn('   ⚠️  Маппинг пустой - все алерты будут отправляться только в общий чат');
        } else {
            foreach ($cinemaMap as $cinemaId => $territory) {
                $this->line("   cinema_id {$cinemaId} → {$territory}");
            }
        }
        $this->newLine();

        // Тест отправки
        $cinemaId = $this->option('cinema-id');
        if ($cinemaId !== null) {
            $this->info('4. Тест отправки сообщения:');
            try {
                $service = new AlertTelegramService();
                $territory = $service->getTerritoryByCinemaId($cinemaId);
                $this->line("   Cinema ID: {$cinemaId}");
                $this->line("   Определенная территория: " . ($territory ?? 'не найдена (будет отправлено только в общий чат)'));
                
                $testMessage = "🧪 <b>Тестовое сообщение</b>\n\n";
                $testMessage .= "Cinema ID: <b>{$cinemaId}</b>\n";
                $testMessage .= "Территория: <b>" . ($territory ?? 'неизвестна') . "</b>\n";
                $testMessage .= "Время: " . now()->format('d.m.Y H:i:s');

                $this->line("   Отправка тестового сообщения...");
                $success = $service->sendAlertWithRouting($testMessage, $cinemaId);
                
                if ($success) {
                    $this->info("   ✅ Сообщение отправлено успешно");
                } else {
                    $this->error("   ❌ Ошибка при отправке сообщения");
                    return Command::FAILURE;
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Ошибка: " . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $this->info('4. Для тестовой отправки запустите команду с опцией --cinema-id:');
            $this->line('   php artisan test:alert-routing --cinema-id=0000000001');
        }

        $this->newLine();
        $this->info('=== Диагностика завершена ===');

        return Command::SUCCESS;
    }
}



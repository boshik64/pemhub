<?php

namespace App\Console\Commands;

use App\Services\AlertTelegramService;
use App\Services\ExternalDatabaseService;
use App\Services\SshTunnelService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckExternalAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:external-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяет незавершенные задачи и автовозвраты во внешней БД и отправляет алерты в Telegram';

    private ExternalDatabaseService $dbService;
    private AlertTelegramService $telegramService;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Начало проверки внешних алертов...');

        try {
            // Инициализируем сервисы
            $sshTunnel = new SshTunnelService();
            $this->dbService = new ExternalDatabaseService($sshTunnel);
            $this->telegramService = new AlertTelegramService();

            // Подключаемся к внешней БД
            $this->info('Подключение к внешней БД через SSH туннель...');
            $this->dbService->connect();
            $this->info('Подключение установлено');

            // Проверка 1: Незавершенные задачи заказов
            $this->info('Проверка незавершенных задач заказов...');
            $this->checkUnfinishedOrderTasks();

            // Проверка 2: Незавершенные автовозвраты
            $this->info('Проверка незавершенных автовозвратов...');
            $this->checkUnfinishedAutoRefunds();

            // Отключаемся от БД
            $this->dbService->disconnect();
            $this->info('Проверка завершена успешно');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Ошибка при выполнении проверки: ' . $e->getMessage());
            Log::error('Ошибка в команде check:external-alerts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Пытаемся отключиться от БД в случае ошибки
            if (isset($this->dbService)) {
                try {
                    $this->dbService->disconnect();
                } catch (Exception $disconnectException) {
                    Log::error('Ошибка при отключении от БД', [
                        'error' => $disconnectException->getMessage(),
                    ]);
                }
            }

            return Command::FAILURE;
        }
    }

    /**
     * Проверяет незавершенные задачи заказов
     *
     * @return void
     * @throws Exception
     */
    private function checkUnfinishedOrderTasks(): void
    {
        try {
            $tasks = $this->dbService->getUnfinishedOrderTasks();

            if (!empty($tasks)) {
                $this->warn('Найдено незавершенных задач: ' . count($tasks));
                $message = $this->telegramService->formatUnfinishedOrderTasksMessage($tasks);
                $this->telegramService->sendMessage($message);
                $this->info('Алерт отправлен в Telegram');
            } else {
                $this->info('Незавершенных задач не найдено');
            }
        } catch (Exception $e) {
            Log::error('Ошибка при проверке незавершенных задач заказов', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Проверяет незавершенные автовозвраты
     *
     * @return void
     * @throws Exception
     */
    private function checkUnfinishedAutoRefunds(): void
    {
        try {
            $refunds = $this->dbService->getUnfinishedAutoRefunds();

            if (!empty($refunds)) {
                $this->warn('Найдено незавершенных автовозвратов: ' . count($refunds));
                $message = $this->telegramService->formatUnfinishedAutoRefundsMessage($refunds);
                $this->telegramService->sendMessage($message);
                $this->info('Алерт отправлен в Telegram');
            } else {
                $this->info('Незавершенных автовозвратов не найдено');
            }
        } catch (Exception $e) {
            Log::error('Ошибка при проверке незавершенных автовозвратов', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}


<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ExternalDatabaseService
{
    private SshTunnelService $sshTunnel;
    private bool $tunnelCreated = false;

    public function __construct(SshTunnelService $sshTunnel)
    {
        $this->sshTunnel = $sshTunnel;
    }

    /**
     * Подключается к внешней БД через SSH туннель
     *
     * @return void
     * @throws Exception
     */
    public function connect(): void
    {
        try {
            $this->sshTunnel->createTunnel();
            $this->tunnelCreated = true;

            // Обновляем конфигурацию БД с локальным портом из туннеля
            $localPort = $this->sshTunnel->getLocalPort();
            config(['database.connections.external_karo.host' => '127.0.0.1']);
            config(['database.connections.external_karo.port' => $localPort]);

            // Очищаем кэш подключения, чтобы применить новую конфигурацию
            DB::purge('external_karo');
        } catch (Exception $e) {
            Log::error('Ошибка создания SSH туннеля', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Не удалось подключиться к внешней БД: ' . $e->getMessage());
        }
    }

    /**
     * Отключается от внешней БД
     *
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->tunnelCreated) {
            $this->sshTunnel->closeTunnel();
            $this->tunnelCreated = false;
        }
    }

    /**
     * Выполняет SQL запрос к внешней БД
     *
     * @param string $query
     * @param array $bindings
     * @return array
     * @throws Exception
     */
    public function query(string $query, array $bindings = []): array
    {
        if (!$this->tunnelCreated) {
            $this->connect();
        }

        try {
            $results = DB::connection('external_karo')->select($query, $bindings);
            return array_map(function ($row) {
                return (array) $row;
            }, $results);
        } catch (Exception $e) {
            Log::error('Ошибка выполнения запроса к внешней БД', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Ошибка выполнения запроса: ' . $e->getMessage());
        }
    }

    /**
     * Получает незавершенные задачи заказов
     *
     * @return array
     * @throws Exception
     */
    public function getUnfinishedOrderTasks(): array
    {
        $query = "
            SELECT 
                ot.order_id, 
                ot.task, 
                ot.created_at, 
                ot.finished_at, 
                o.booking_id, 
                o.`key` ,
                t.name, 
                vc.vista_cinema_id
            FROM karo.order_task AS ot
            LEFT JOIN karo.orders AS o ON ot.order_id = o.id
            LEFT JOIN karo.theatre AS t ON t.id = o.theatre_id
            LEFT JOIN karo.vista_cinema AS vc ON vc.theatre_id = o.theatre_id
            WHERE ot.finished_at IS NULL
              AND ot.task = 'postcharge_refund'
              AND ot.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
        ";

        return $this->query($query);
    }

    /**
     * Получает незавершенные автовозвраты из формы
     *
     * @return array
     * @throws Exception
     */
    public function getUnfinishedAutoRefunds(): array
    {
        $query = "
            SELECT 
                rf.id, 
                t.name, 
                rf.booking_id, 
                rf.client, 
                rf.email, 
                rf.phone, 
                rf.created_at, 
                rf.updated_at, 
                rf.status, 
                rf.type
            FROM karo.refund_form AS rf
            LEFT JOIN karo.theatre AS t ON rf.theatre_id = t.id
            WHERE rf.status = 'INPROGRESS'
              AND rf.type = 'AUTO'
              AND rf.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
              AND rf.created_at <= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ";

        return $this->query($query);
    }
}


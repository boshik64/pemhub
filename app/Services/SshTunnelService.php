<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class SshTunnelService
{
    private $tunnelProcess = null;
    private string $localPort;
    private string $sshHost;
    private string $sshUser;
    private string $sshPort;
    private string $remoteHost;
    private string $remotePort;
    private ?string $sshKeyPath = null;
    private ?string $sshPassword = null;

    public function __construct()
    {
        $sshHost = config('services.ssh_tunnel.host');
        $sshUser = config('services.ssh_tunnel.user');
        $sshPort = config('services.ssh_tunnel.port', '22');
        $remoteHost = config('services.ssh_tunnel.remote_db_host', '127.0.0.1');
        $remotePort = config('services.ssh_tunnel.remote_db_port', '3306');
        $localPort = config('services.ssh_tunnel.local_port', '13306');
        $sshKeyPath = config('services.ssh_tunnel.key_path');
        $sshPassword = config('services.ssh_tunnel.password');

        // Валидация конфигурации
        if (empty($sshHost)) {
            throw new Exception('SSH туннель не настроен: требуется SSH_TUNNEL_HOST в .env файле');
        }

        if (empty($sshUser)) {
            throw new Exception('SSH туннель не настроен: требуется SSH_TUNNEL_USER в .env файле');
        }

        if (empty($sshKeyPath) && empty($sshPassword)) {
            throw new Exception('SSH туннель не настроен: требуется SSH_TUNNEL_KEY_PATH или SSH_TUNNEL_PASSWORD в .env файле');
        }

        // Присваиваем значения только после валидации
        $this->sshHost = $sshHost;
        $this->sshUser = $sshUser;
        $this->sshPort = $sshPort;
        $this->remoteHost = $remoteHost;
        $this->remotePort = $remotePort;
        $this->localPort = $localPort;
        $this->sshKeyPath = $sshKeyPath;
        $this->sshPassword = $sshPassword;
    }

    /**
     * Создает SSH туннель
     *
     * @return bool
     * @throws Exception
     */
    public function createTunnel(): bool
    {
        if ($this->isTunnelActive()) {
            return true;
        }

        // Проверяем, что локальный порт свободен
        if ($this->isPortInUse($this->localPort)) {
            // Пытаемся убить висячие процессы перед созданием нового туннеля
            Log::warning('Локальный порт занят, пытаемся очистить висячие процессы', [
                'port' => $this->localPort,
            ]);
            $this->killTunnelProcesses();
            sleep(2);
            
            // Проверяем снова
            if ($this->isPortInUse($this->localPort)) {
                throw new Exception("Локальный порт {$this->localPort} уже занят. Попробуйте выполнить команду через несколько секунд.");
            }
        }

        $command = $this->buildSshCommand();

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $this->tunnelProcess = @proc_open($command, $descriptorspec, $pipes);

        if (!is_resource($this->tunnelProcess)) {
            throw new Exception('Не удалось создать SSH туннель');
        }

        // Закрываем stdin (не нужен для SSH туннеля)
        if (isset($pipes[0])) {
            fclose($pipes[0]);
        }

        // Читаем stderr для диагностики
        $errorOutput = '';
        if (isset($pipes[2])) {
            stream_set_blocking($pipes[2], false);
            $errorOutput = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
        }

        // Закрываем stdout
        if (isset($pipes[1])) {
            fclose($pipes[1]);
        }

        // Даем время туннелю установиться
        sleep(3);

        if (!$this->isTunnelActive()) {
            $this->closeTunnel();
            Log::error('SSH туннель не был установлен', [
                'command' => $command,
                'error_output' => $errorOutput,
            ]);
            throw new Exception('SSH туннель не был установлен. Проверьте логи для деталей. Ошибка: ' . $errorOutput);
        }

        Log::info('SSH туннель успешно создан', [
            'local_port' => $this->localPort,
            'remote_host' => $this->remoteHost,
            'remote_port' => $this->remotePort,
        ]);

        return true;
    }

    /**
     * Проверяет, активен ли туннель
     *
     * @return bool
     */
    public function isTunnelActive(): bool
    {
        if (!is_resource($this->tunnelProcess)) {
            return false;
        }

        $status = proc_get_status($this->tunnelProcess);
        return $status['running'] ?? false;
    }

    /**
     * Закрывает SSH туннель
     *
     * @return void
     */
    public function closeTunnel(): void
    {
        if (is_resource($this->tunnelProcess)) {
            // Сначала пытаемся корректно завершить процесс
            $status = proc_get_status($this->tunnelProcess);
            if ($status && $status['running']) {
                proc_terminate($this->tunnelProcess, SIGTERM);
                // Даем время на корректное завершение
                sleep(1);
                
                // Проверяем, завершился ли процесс
                $status = proc_get_status($this->tunnelProcess);
                if ($status && $status['running']) {
                    // Принудительно убиваем, если не завершился
                    proc_terminate($this->tunnelProcess, SIGKILL);
                    sleep(1);
                }
            }
            proc_close($this->tunnelProcess);
            $this->tunnelProcess = null;
        }
        
        // Дополнительно убиваем все SSH процессы, использующие этот порт
        $this->killTunnelProcesses();
        
        Log::info('SSH туннель закрыт', ['local_port' => $this->localPort]);
    }

    /**
     * Убивает все SSH процессы, использующие локальный порт
     *
     * @return void
     */
    private function killTunnelProcesses(): void
    {
        // Ищем процессы SSH, использующие наш локальный порт
        $command = "ps aux | grep 'ssh.*{$this->localPort}' | grep -v grep | awk '{print \$2}'";
        $output = [];
        $returnVar = 0;
        @exec($command, $output, $returnVar);
        
        foreach ($output as $pid) {
            $pid = trim($pid);
            if (is_numeric($pid) && $pid > 0) {
                @exec("kill -9 {$pid} 2>/dev/null");
                Log::info('Убит SSH процесс', ['pid' => $pid, 'port' => $this->localPort]);
            }
        }
        
        // Также убиваем sshpass процессы, если они есть
        $command = "ps aux | grep 'sshpass.*{$this->localPort}' | grep -v grep | awk '{print \$2}'";
        $output = [];
        @exec($command, $output, $returnVar);
        
        foreach ($output as $pid) {
            $pid = trim($pid);
            if (is_numeric($pid) && $pid > 0) {
                @exec("kill -9 {$pid} 2>/dev/null");
                Log::info('Убит sshpass процесс', ['pid' => $pid, 'port' => $this->localPort]);
            }
        }
    }

    /**
     * Получает локальный порт для подключения к БД
     *
     * @return string
     */
    public function getLocalPort(): string
    {
        return $this->localPort;
    }

    /**
     * Строит команду SSH для создания туннеля
     *
     * @return string
     */
    private function buildSshCommand(): string
    {
        $command = sprintf(
            'ssh -N -L %s:%s:%s -p %s %s@%s',
            $this->localPort,
            $this->remoteHost,
            $this->remotePort,
            $this->sshPort,
            $this->sshUser,
            $this->sshHost
        );

        // Добавляем опции для подавления интерактивных запросов
        $command .= ' -o StrictHostKeyChecking=no';
        $command .= ' -o UserKnownHostsFile=/dev/null';
        $command .= ' -o LogLevel=ERROR';
        $command .= ' -o PreferredAuthentications=password';
        $command .= ' -o PubkeyAuthentication=no';

        // Используем ключ или пароль
        if ($this->sshKeyPath) {
            $command .= ' -i ' . escapeshellarg($this->sshKeyPath);
        } elseif ($this->sshPassword) {
            // Для пароля используем sshpass (если установлен) или expect скрипт
            if ($this->isSshpassAvailable()) {
                $command = 'sshpass -p ' . escapeshellarg($this->sshPassword) . ' ' . $command;
            } elseif ($this->isExpectAvailable()) {
                // Используем expect скрипт для автоматизации SSH с паролем
                $command = $this->buildExpectCommand($command);
            } else {
                // Используем временный expect скрипт через файл
                $command = $this->buildTemporaryExpectScript($command);
            }
        }

        return $command;
    }

    /**
     * Проверяет доступность sshpass
     *
     * @return bool
     */
    private function isSshpassAvailable(): bool
    {
        $output = [];
        $returnVar = 0;
        @exec('which sshpass', $output, $returnVar);
        return $returnVar === 0;
    }

    /**
     * Проверяет доступность expect
     *
     * @return bool
     */
    private function isExpectAvailable(): bool
    {
        $output = [];
        $returnVar = 0;
        @exec('which expect', $output, $returnVar);
        return $returnVar === 0;
    }

    /**
     * Создает expect скрипт для автоматизации SSH с паролем
     *
     * @param string $sshCommand
     * @return string
     */
    private function buildExpectCommand(string $sshCommand): string
    {
        $expectScript = sprintf(
            'expect -c "spawn %s; expect \"password:\"; send \"%s\\r\"; interact"',
            $sshCommand,
            addcslashes($this->sshPassword, '"\\')
        );

        return $expectScript;
    }

    /**
     * Создает временный expect скрипт через файл
     *
     * @param string $sshCommand
     * @return string
     * @throws Exception
     */
    private function buildTemporaryExpectScript(string $sshCommand): string
    {
        $scriptPath = sys_get_temp_dir() . '/ssh_tunnel_' . uniqid() . '.exp';
        $scriptContent = sprintf(
            "#!/usr/bin/expect -f\nset timeout 30\nspawn %s\nexpect {\n    \"password:\" {\n        send \"%s\\r\"\n        exp_continue\n    }\n    \"Password:\" {\n        send \"%s\\r\"\n        exp_continue\n    }\n    timeout {\n        exit 1\n    }\n}\ninteract\n",
            $sshCommand,
            addcslashes($this->sshPassword, '\\'),
            addcslashes($this->sshPassword, '\\')
        );

        if (file_put_contents($scriptPath, $scriptContent) === false) {
            throw new Exception('Не удалось создать временный expect скрипт');
        }

        chmod($scriptPath, 0700);

        // Регистрируем функцию очистки для удаления скрипта после завершения
        register_shutdown_function(function () use ($scriptPath) {
            if (file_exists($scriptPath)) {
                @unlink($scriptPath);
            }
        });

        return $scriptPath;
    }

    /**
     * Проверяет, используется ли порт
     *
     * @param string $port
     * @return bool
     */
    private function isPortInUse(string $port): bool
    {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }

    /**
     * Деструктор - закрывает туннель при уничтожении объекта
     */
    public function __destruct()
    {
        try {
            $this->closeTunnel();
        } catch (\Exception $e) {
            // Игнорируем ошибки в деструкторе, но логируем
            Log::warning('Ошибка при закрытии туннеля в деструкторе', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}


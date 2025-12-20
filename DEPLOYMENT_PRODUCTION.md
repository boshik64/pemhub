# Инструкция по развертыванию на продакшене

## 1. Проверка и настройка .env

Убедитесь, что в `.env` на продакшене настроены все необходимые переменные:

```env
# SSH туннель
SSH_TUNNEL_HOST=82.142.150.146
SSH_TUNNEL_PORT=22232
SSH_TUNNEL_USER=smersh
SSH_TUNNEL_PASSWORD=ваш_пароль
SSH_TUNNEL_REMOTE_DB_HOST=172.20.0.7
SSH_TUNNEL_REMOTE_DB_PORT=3306
SSH_TUNNEL_LOCAL_PORT=13306

# Подключение к БД через туннель
EXTERNAL_KARO_DB_HOST=127.0.0.1
EXTERNAL_KARO_DB_PORT=13306
EXTERNAL_KARO_DB_DATABASE=karo
EXTERNAL_KARO_DB_USERNAME=karo
EXTERNAL_KARO_DB_PASSWORD=ваш_пароль

# Telegram бот для алертов
ALERT_TELEGRAM_BOT_TOKEN=7825393526:AAEdDX9Rf828h8AGUaDI3lds7hDd9r7q5ow
ALERT_TELEGRAM_CHAT_IDS=ваш_chat_id,ваш_group_id
```

**Важно:** Для группы используйте минус перед ID (например: `-4877890060`)

## 2. Установка sshpass в контейнере

Контейнер `pem-cli-cron` должен иметь `sshpass` для работы SSH туннеля с паролем.

### Вариант A: Если контейнер уже запущен (быстрое решение)

```bash
docker exec -u root pem-cli-cron apt-get update
docker exec -u root pem-cli-cron apt-get install -y openssh-client sshpass
```

### Вариант B: Пересобрать контейнер (рекомендуется)

Dockerfile уже обновлен с `sshpass`. Пересоберите контейнер:

```bash
cd docker
docker-compose build pem-cli-cron
docker-compose up -d pem-cli-cron
```

## 3. Проверка работы команды

### Тест вручную:

```bash
# Очистить кэш конфигурации
docker exec pem-cli-cron php artisan config:clear

# Запустить команду вручную
docker exec pem-cli-cron php artisan check:external-alerts
```

### Ожидаемый результат:

- ✅ SSH туннель успешно создан
- ✅ Найдено X незавершенных задач (только с типом postcharge_refund)
- ✅ Найдено X незавершенных автовозвратов
- ✅ Сообщения отправлены в Telegram
- ✅ SSH туннель закрыт
- ✅ Проверка завершена успешно

## 4. Проверка планировщика

Планировщик уже настроен в контейнере `pem-cli-cron` и запускает `schedule:run` каждую минуту.

Команда `check:external-alerts` настроена на запуск **каждый день в 11:00 МСК**.

### Проверка, что планировщик работает:

```bash
# Проверить логи cron
docker exec pem-cli-cron tail -f /var/log/cron.log

# Или проверить процессы
docker exec pem-cli-cron ps aux | grep cron
```

### Проверить расписание команд:

```bash
docker exec pem-cli-cron php artisan schedule:list
```

Вы должны увидеть:
```
0 11 * * *  php artisan check:external-alerts  ...  Next Due: завтра в 11:00
```

## 5. Мониторинг

### Просмотр логов Laravel:

```bash
docker exec pem-cli-cron tail -f /app/storage/logs/laravel.log
```

### Проверка последних запусков:

```bash
# Посмотреть последние записи в логах
docker exec pem-cli-cron grep "check:external-alerts" /app/storage/logs/laravel.log | tail -20
```

## 6. Возможные проблемы

### Проблема: "Локальный порт 13306 уже занят"

**Решение:** Закройте старый SSH туннель:
```bash
docker exec pem-cli-cron pkill -9 -f "ssh.*13306"
```

### Проблема: "SSH туннель не был установлен"

**Проверьте:**
- Правильность SSH credentials в `.env`
- Доступность SSH сервера
- Установлен ли `sshpass` в контейнере

### Проблема: "chat not found" в Telegram

**Решение:**
- Убедитесь, что бот добавлен в группу
- Отключите режим приватности у бота (@BotFather -> Bot Settings -> Group Privacy -> Turn off)
- Проверьте правильность chat_id (для групп нужен минус: `-4877890060`)

## 7. Автоматический запуск

Команда будет автоматически запускаться каждый день в 11:00 МСК через Laravel Scheduler.

Планировщик уже настроен в контейнере `pem-cli-cron` и не требует дополнительной настройки.


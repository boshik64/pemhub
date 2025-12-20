# Настройка команды check:external-alerts

## Проблема с SSH туннелем

Для работы SSH туннеля с паролем требуется установить `sshpass` в Docker контейнере `pem-cli-cron`.

## Решение

### Вариант 1: Установить sshpass в контейнере (быстрое решение)

```bash
docker exec -u root pem-cli-cron apt-get update
docker exec -u root pem-cli-cron apt-get install -y openssh-client sshpass
```

### Вариант 2: Пересобрать контейнер (рекомендуется для production)

Dockerfile уже обновлен. Пересоберите контейнер:

```bash
cd docker
docker-compose build pem-cli-cron
docker-compose up -d pem-cli-cron
```

## Конфигурация .env

**ВАЖНО:** Исправьте конфигурацию в `.env`:

```env
# SSH туннель
SSH_TUNNEL_HOST=82.142.150.146
SSH_TUNNEL_PORT=22232
SSH_TUNNEL_USER=sme
SSH_TUNNEL_PASSWORD=pMeEq*
# Удаленный хост БД (на удаленном сервере через SSH)
SSH_TUNNEL_REMOTE_DB_HOST=172.20.0.7
SSH_TUNNEL_REMOTE_DB_PORT=3306
# Локальный порт для туннеля
SSH_TUNNEL_LOCAL_PORT=13306

# Подключение к БД через туннель (локальный хост!)
EXTERNAL_KARO_DB_HOST=127.0.0.1
EXTERNAL_KARO_DB_PORT=13306
EXTERNAL_KARO_DB_DATABASE=karo
EXTERNAL_KARO_DB_USERNAME=karo
EXTERNAL_KARO_DB_PASSWORD=xhBWF.

# Telegram бот для алертов
ALERT_TELEGRAM_BOT_TOKEN=7825393526:AAEdDX9Rf828h8AGUaDI3lds7hDd9r7q5ow
# Раскомментируйте и укажите ваш chat_id
ALERT_TELEGRAM_CHAT_IDS=123456789
```

**Ключевое отличие:**
- `SSH_TUNNEL_REMOTE_DB_HOST=172.20.0.7` - это IP базы данных на удаленном сервере
- `EXTERNAL_KARO_DB_HOST=127.0.0.1` - это локальный хост через SSH туннель

## Тестирование

После установки sshpass и исправления конфигурации:

```bash
docker exec pem-cli-cron php artisan check:external-alerts
```

## Автоматический запуск

Команда настроена на запуск каждый день в 11:00 МСК через Laravel Scheduler.


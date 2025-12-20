# Исправление ошибок на продакшене

## Проблема 1: "Cannot assign null to property"

**Причина:** Переменные окружения не загружены или не настроены в `.env`

**Решение:**
1. Проверьте, что в `.env` на продакшене есть все необходимые переменные:
   ```bash
   grep -E "(SSH_TUNNEL|EXTERNAL_KARO|ALERT_TELEGRAM)" /var/www/pemhub/.env
   ```

2. Очистите кэш конфигурации:
   ```bash
   docker exec pem-cli-cron php artisan config:clear
   docker exec pem-fpm php artisan config:clear
   ```

3. Проверьте, что переменные загружаются:
   ```bash
   docker exec pem-cli-cron php artisan tinker
   # В tinker выполните:
   config('services.ssh_tunnel.host')
   # Должно вернуть значение, а не null
   ```

## Проблема 2: Группа обновлена до супергруппы

**Решение:** Обновите `ALERT_TELEGRAM_CHAT_IDS` в `.env`:

```env
ALERT_TELEGRAM_CHAT_IDS=731655828,-1003586227187
```

Новый ID супергруппы: `-1003586227187`

## Проблема 3: Предупреждение Dockerfile

**Решение:** Пересоберите контейнер `pem-cli-cron`:

```bash
cd /var/www/pemhub/docker
docker-compose build pem-cli-cron
docker-compose up -d pem-cli-cron
```

## Проверка после исправлений

```bash
# 1. Очистить кэш
docker exec pem-cli-cron php artisan config:clear

# 2. Проверить команду
docker exec pem-cli-cron php artisan check:external-alerts

# 3. Проверить расписание
docker exec pem-cli-cron php artisan schedule:list
```

## Быстрая проверка конфигурации

```bash
# Проверить все переменные SSH
docker exec pem-cli-cron php artisan tinker --execute="dump(config('services.ssh_tunnel'));"

# Проверить переменные Telegram
docker exec pem-cli-cron php artisan tinker --execute="dump(config('services.alert_telegram'));"
```


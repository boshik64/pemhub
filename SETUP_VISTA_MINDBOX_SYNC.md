# Vista → Mindbox: синхронизация оффлайн-заказов

## Что делает модуль

- Инкрементально читает оффлайн-заказы из Microsoft SQL Server (Vista Loyalty) по `transaction_id > last_processed_transaction_id`.
- Агрегирует строки JOIN по `transaction_id` в 1 заказ + N позиций.
- На каждый заказ создаёт отдельный Job в очереди, который отправляет **минимальный payload** в Mindbox (операция `Offline.SaveOfflineOrder`).
- Пишет статусы/пэйлоады/ошибки в локальную БД и показывает это в админке Filament.
- `last_processed_transaction_id` двигается только когда все заказы до watermark обработаны успешно.

## ENV

Добавьте в `.env`:

```env
# Vista Loyalty SQL Server
VISTA_DB_HOST=
VISTA_DB_PORT=1433
VISTA_DB_DATABASE=
VISTA_DB_USERNAME=
VISTA_DB_PASSWORD=

# Mindbox
MINDBOX_BASE_URL=https://api.s.mindbox.ru
MINDBOX_ENDPOINT_ID=
MINDBOX_SECRET_KEY=
MINDBOX_TIMEOUT=20
```

## Миграции

```bash
php artisan migrate
```

Создаются таблицы:
- `vista_offline_order_sync_states`
- `vista_offline_order_sync_logs`

## Очереди

Модуль использует стандартные Laravel Queues (Job: `App\\Jobs\\SendOfflineOrderToMindbox`).

- Для локальной проверки можно оставить `QUEUE_CONNECTION=sync` (все будет выполняться сразу).
- Для production включите Redis/Database driver и запустите воркеры:

```bash
php artisan queue:work
```

## Запуск

### Ручной запуск

```bash
php artisan sync:vista-offline-orders
```

### Важно про первый запуск

При **первом запуске** модуль не читает всю историю.

- Если `last_processed_transaction_id = 0`, то перед основной выборкой вычисляется минимальный `transaction_id` **за последний час**
  (по `transaction_time >= DATEADD(HOUR, -1, GETDATE())` с теми же базовыми фильтрами).
- Далее `last_processed_transaction_id` выставляется в `(minId - 1)`, и основной (обязательный по ТЗ) SQL забирает только “последний час”.

Dry-run (без диспатча jobs):

```bash
php artisan sync:vista-offline-orders --dry-run
```

### Scheduler

Команда добавлена в `app/Console/Kernel.php` и запускается каждые 10 минут.

## Filament

Раздел: **Инструменты → Vista → Mindbox (оффлайн-заказы)**

Возможности:
- просмотр статусов `pending/success/failed`
- фильтр по статусу и дате
- просмотр `source_data`, `request_payload`, `response_payload`, `error_message`
- кнопка **Retry** для принудительной повторной отправки

## Важные правила payload

- `membershipID` обязателен. Если его нет — заказ помечается `failed` (и виден в админке), Mindbox не вызывается.
- `cardNumber` не отправляем.
- Не отправляем `null`, пустые строки, пустые массивы.


---
name: mindbox-status-command
overview: Добавить Artisan-команду для массовой отправки Mindbox Website.UpdateOrderStatus по выборке из локальной таблицы без изменения локальных статусов.
todos:
  - id: cmd-add-file
    content: Добавить команду `app/Console/Commands/MindboxUpdateVistaOfflineOrderStatus.php` с нужными опциями и отправкой Mindbox `Website.UpdateOrderStatus` без UPDATE локальной таблицы.
    status: completed
  - id: cmd-verify
    content: Проверить наличие команды в `php artisan list`, затем прогнать `--dry-run` и только после этого реальный запуск.
    status: completed
isProject: false
---

## Цель

Создать команду, которая:

- выбирает `transaction_id` из `pemdb.vista_offline_order_sync_logs` по условию:
  - `transaction_id > 864794697`
  - `created_at < '2026-03-21 08:42:12'`
- для каждой записи вызывает Mindbox `Website.UpdateOrderStatus` с:
  - `orderLinesStatus = completed`
  - `order.ids.websiteID = 'vista_transaction_id_' + transaction_id`
- не меняет локальные статусы в `vista_offline_order_sync_logs`
- добавляет паузу 200ms между запросами (throttle)

## Изменения в коде

- Добавить новый Artisan command:
  - `app/Console/Commands/MindboxUpdateVistaOfflineOrderStatus.php`

## Логика команды

1. Прочитать опции командной строки (`--min-transaction-id`, `--created-before`, `--order-lines-status`, `--limit`, `--throttle-ms`, `--dry-run`).
2. Сформировать Mindbox URL:
  - `operation=Website.UpdateOrderStatus`
3. Выбрать строки только с `transaction_id` из:
  - `pemdb.vista_offline_order_sync_logs`
4. Для каждой записи сделать POST:
  - body как в твоём `curl`
5. Между запросами: `usleep($throttleMs * 1000)`
6. `--dry-run`: не отправлять в Mindbox, только печатать какие `websiteID` были бы отправлены.

## Проверка

1. После добавления файла:
  - `php artisan list | rg "mindbox:update-vista-offline-order-status"`
2. Dry run:
  - `php artisan mindbox:update-vista-offline-order-status --dry-run --limit=20`
3. Реальная отправка:
  - `php artisan mindbox:update-vista-offline-order-status --limit=500`

## Примечания

- Если Artisan не покажет команду, возможно понадобится `composer dump-autoload` (обычно не требуется).


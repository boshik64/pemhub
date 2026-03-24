<?php

namespace App\Services\Mindbox;

use Carbon\Carbon;

/**
 * Строит минимальный payload для Offline.SaveOfflineOrder.
 *
 * Требования:
 * - НЕ отправлять null
 * - НЕ отправлять пустые строки/массивы
 * - НЕ отправлять неиспользуемые поля
 * - cardNumber не используем вовсе (по текущему решению)
 */
class OfflineOrderPayloadBuilder
{
    /**
     * @param array{transaction_id:int, header:array<string,mixed>, lines:array<int,array<string,mixed>>} $order
     */
    public function build(array $order): array
    {
        $header = $order['header'] ?? [];
        $transactionId = (string) ($order['transaction_id'] ?? '');

        $membershipId = (string) ($header['transaction_membershipid'] ?? '');
        $bookingId = (string) ($header['transaction_bookingId'] ?? '');
        $cinemaExternalId = (string) ($header['wcl_cinema_id'] ?? '');
        $executionDateTimeUtc = $this->formatExecutionDateTimeUtc($header['transaction_time'] ?? null);
        // Mindbox не принимает «действие в будущем» — ограничиваем текущим UTC, если время из Vista впереди
        $executionDateTimeUtc = $this->capToNowUtc($executionDateTimeUtc);

        $orderLines = $order['lines'] ?? [];
        $firstLine = $orderLines[0] ?? [];
        // sessionTime в ISO 8601 UTC + 2 часа
        $orderSessionTime = $this->formatSessionTimeWithOffset($firstLine['transactionItem_sessionTime'] ?? null, 2);

        // product.ids.website: билеты — movie_ho; продукты — из item_nameAltLang (web_id=98 → 98) или item_code
        $lines = [];
        $lineNumber = 1;
        foreach ($orderLines as $line) {
            $productWebsiteId = $this->resolveProductWebsiteId($line);
            // Стоимость в Vista = spend + tax (нужно учитывать налог)
            $basePricePerItem = $line['transactionItem_spend_with_tax']
                ?? $this->sumIfNumeric($line['transactionItem_spend'] ?? null, $line['transactionItem_tax'] ?? null);
            // Mindbox: базовая цена не может содержать дробные доли копейки — округляем до целых копеек
            if ($basePricePerItem !== null && is_numeric($basePricePerItem)) {
                $basePricePerItem = round((float) $basePricePerItem, 2);
            }
            $typeTicket = (string) ($line['item_name'] ?? '');

            $lines[] = [
                'lineNumber' => $lineNumber++,
                'status' => [
                    'ids' => [
                        'externalId' => 'success',
                    ],
                ],
                'product' => [
                    'ids' => [
                        'website' => $productWebsiteId,
                    ],
                ],
                'quantity' => 1,
                'basePricePerItem' => $basePricePerItem,
                'customFields' => [
                    'typeTicket' => $typeTicket,
                ],
            ];
        }

        // По ТЗ: customFields — bookingId, sessionTime, typeTicket (всегда все три ключа)
        // order.lines — всегда массив (даже пустой), не вычищаем null/пустоты в этих блоках
        $payload = [
            'customer' => $this->filterRecursive([
                'ids' => [
                    'membershipID' => $membershipId,
                ],
            ]),
            'order' => [
                'ids' => [
                    'websiteID' => 'vista_transaction_id_' . $transactionId,
                ],
                'customFields' => [
                    'bookingId' => $bookingId,
                    'sessionTime' => $orderSessionTime,
                ],
                'area' => $this->filterRecursive([
                    'ids' => [
                        'externalId' => $cinemaExternalId,
                    ],
                ]),
                'lines' => $lines,
            ],
            'executionDateTimeUtc' => $executionDateTimeUtc,
        ];

        // Не фильтруем весь payload — иначе пропадут sessionTime и lines
        return $payload;
    }

    private function sumIfNumeric($a, $b): ?float
    {
        $hasA = $a !== null && $a !== '' && is_numeric($a);
        $hasB = $b !== null && $b !== '' && is_numeric($b);
        if (!$hasA && !$hasB) {
            return null;
        }
        return (float) ($hasA ? $a : 0) + (float) ($hasB ? $b : 0);
    }

    /**
     * Идентификатор продукта для Mindbox (product.ids.website):
     * билеты на фильмы — movie_ho; продукты — из item_nameAltLang (web_id=98 → 98) или item_code.
     *
     * @param array<string, mixed> $line
     * @return string
     */
    private function resolveProductWebsiteId(array $line): string
    {
        $movieHo = (string) ($line['movie_ho'] ?? '');
        if ($movieHo !== '') {
            return $movieHo;
        }

        $itemNameAltLang = (string) ($line['item_nameAltLang'] ?? '');
        if ($itemNameAltLang !== '' && preg_match('/web_id=(.+)$/', $itemNameAltLang, $m)) {
            return trim($m[1]);
        }

        $itemCode = (string) ($line['item_code'] ?? '');
        return $itemCode;
    }

    /**
     * Форматирует sessionTime в ISO 8601 UTC и добавляет смещение в часах.
     *
     * @param mixed $value
     * @param int $hoursOffset
     * @return string|null
     */
    private function formatSessionTimeWithOffset($value, int $hoursOffset): ?string
    {
        $formatted = $this->formatExecutionDateTimeUtc($value);
        if ($formatted === null || $formatted === '') {
            return $formatted;
        }
        try {
            return Carbon::parse($formatted)->addHours($hoursOffset)->utc()->format('Y-m-d\TH:i:s.v\Z');
        } catch (\Throwable $e) {
            return $formatted;
        }
    }

    /**
     * Если переданная дата/время (ISO 8601 UTC) в будущем — возвращаем текущее UTC.
     * Mindbox отклоняет запросы с executionDateTimeUtc в будущем.
     *
     * @param string|null $isoUtc
     * @return string|null
     */
    private function capToNowUtc(?string $isoUtc): ?string
    {
        if ($isoUtc === null || $isoUtc === '') {
            return $isoUtc;
        }
        try {
            $dt = Carbon::parse($isoUtc);
            if ($dt->isFuture()) {
                return Carbon::now()->utc()->format('Y-m-d\TH:i:s.v\Z');
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return $isoUtc;
    }

    /**
     * Форматирует executionDateTimeUtc в ISO 8601 UTC для Mindbox.
     *
     * @param mixed $value
     * @return string|null
     */
    private function formatExecutionDateTimeUtc($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            $dt = $value instanceof \DateTimeInterface
                ? Carbon::instance($value)
                : Carbon::parse($value);
            return $dt->utc()->format('Y-m-d\TH:i:s.v\Z');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Рекурсивно вычищает:
     * - null
     * - пустые строки
     * - пустые массивы
     *
     * @param mixed $value
     * @return mixed
     */
    private function filterRecursive($value)
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $filtered = $this->filterRecursive($v);
                $isEmptyString = is_string($filtered) && trim($filtered) === '';
                $isEmptyArray = is_array($filtered) && empty($filtered);
                if ($filtered === null || $isEmptyString || $isEmptyArray) {
                    continue;
                }
                $result[$k] = $filtered;
            }
            return $result;
        }

        return $value;
    }
}


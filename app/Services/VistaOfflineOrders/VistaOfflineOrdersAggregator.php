<?php

namespace App\Services\VistaOfflineOrders;

use Generator;

/**
 * Агрегирует результат JOIN-запроса в структуру:
 * 1 заказ (order) + N строк (lines) по одному transaction_id.
 *
 * ВАЖНО: из-за JOIN один transaction_id присутствует в нескольких строках.
 * Запрос отсортирован по transaction_id ASC, поэтому можно агрегировать стримом без хранения всех строк в памяти.
 */
class VistaOfflineOrdersAggregator
{
    /**
     * @param iterable<array<string, mixed>> $rows
     * @return Generator<int, array{transaction_id:int, header:array<string,mixed>, lines:array<int,array<string,mixed>>}>
     */
    public function aggregate(iterable $rows): Generator
    {
        $currentId = null;
        $header = null;
        $lines = [];

        foreach ($rows as $row) {
            $transactionId = (int) ($row['transaction_id'] ?? 0);

            if ($currentId === null) {
                $currentId = $transactionId;
                $header = $this->extractHeader($row);
                $lines = [];
            }

            // Если transaction_id изменился — отдаём накопленный заказ и начинаем новый
            if ($transactionId !== $currentId) {
                yield [
                    'transaction_id' => $currentId,
                    'header' => $header ?? [],
                    'lines' => $lines,
                ];

                $currentId = $transactionId;
                $header = $this->extractHeader($row);
                $lines = [];
            }

            $line = $this->extractLine($row);
            if ($line !== null) {
                $lines[] = $line;
            }
        }

        // Последний заказ
        if ($currentId !== null) {
            yield [
                'transaction_id' => $currentId,
                'header' => $header ?? [],
                'lines' => $lines,
            ];
        }
    }

    /**
     * Заголовок заказа (общие поля).
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function extractHeader(array $row): array
    {
        return [
            'transaction_id' => $row['transaction_id'] ?? null,
            'transaction_membershipid' => $row['transaction_membershipid'] ?? null,
            'transaction_bookingId' => $row['transaction_bookingId'] ?? null,
            'transaction_time' => $row['transaction_time'] ?? null,
            'transaction_salesChannel' => $row['transaction_salesChannel'] ?? null,
            'complex_name' => $row['complex_name'] ?? null,
            'wcl_cinema_id' => $row['wcl_cinema_id'] ?? null,
        ];
    }

    /**
     * Позиция заказа (line). Добавляем строку по каждой записи из JOIN.
     * transactionItem_id/recognitionid не используем — в Mindbox передаём названия и movie_ho.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    private function extractLine(array $row): ?array
    {
        // Пропускаем только полностью пустые строки (билеты на фильмы и продукты)
        $hasItemData = isset($row['transactionItem_lineItemCount'])
            || isset($row['transactionItem_spend'])
            || !empty($row['item_name'])
            || !empty($row['movie_ho'])
            || !empty($row['item_nameAltLang'])
            || !empty($row['item_code']);
        if (!$hasItemData) {
            return null;
        }

        return [
            'transactionItem_lineItemCount' => $row['transactionItem_lineItemCount'] ?? null,
            'transactionItem_spend' => $row['transactionItem_spend'] ?? null,
            'transactionItem_sessionTime' => $row['transactionItem_sessionTime'] ?? null,
            'item_name' => $row['item_name'] ?? null,
            'item_nameAltLang' => $row['item_nameAltLang'] ?? null,
            'item_code' => $row['item_code'] ?? null,
            'movie_ho' => $row['movie_ho'] ?? null,
            'movie_name' => $row['movie_name'] ?? null,
            'movie_code' => $row['movie_code'] ?? null,
        ];
    }
}


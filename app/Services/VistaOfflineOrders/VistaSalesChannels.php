<?php

namespace App\Services\VistaOfflineOrders;

/**
 * Каналы продаж Vista (transaction_salesChannel), которые умеет синхронизировать Mindbox.
 */
class VistaSalesChannels
{
    public const POS = 1;

    public const KIOSK = 2;

    public const SMARTIX = 8;

    /**
     * @var array<int, string>
     */
    public const CHANNELS = [
        self::POS => 'Point of Sale',
        self::KIOSK => 'Kiosk',
        self::SMARTIX => 'Smartix(КСО)',
    ];

    /**
     * Фрагмент SQL, который подменяется списком включённых каналов.
     */
    public const SQL_IN_CLAUSE = 'IN (1, 2, 8)';

    public static function name(mixed $salesChannelId): string
    {
        if ($salesChannelId === null || $salesChannelId === '') {
            return '—';
        }

        $id = (int) $salesChannelId;

        return self::CHANNELS[$id] ?? (string) $salesChannelId;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::CHANNELS as $id => $name) {
            $options[(string) $id] = $name;
        }

        return $options;
    }

    /**
     * Оставляет только известные id каналов, по возрастанию.
     *
     * @param array<int|string> $channelIds
     * @return list<int>
     */
    public static function sanitize(array $channelIds): array
    {
        $allowed = array_keys(self::CHANNELS);
        $ids = [];

        foreach ($channelIds as $channelId) {
            if (!is_scalar($channelId) || !is_numeric($channelId)) {
                continue;
            }

            $id = (int) $channelId;
            if (in_array($id, $allowed, true)) {
                $ids[$id] = $id;
            }
        }

        sort($ids);

        return array_values($ids);
    }

    /**
     * Подставляет включённые каналы в SQL. null — синхронизировать нечего.
     *
     * @param array<int|string> $channelIds
     */
    public static function applyChannelFilter(string $sql, array $channelIds): ?string
    {
        $ids = self::sanitize($channelIds);
        if ($ids === []) {
            return null;
        }

        if (!str_contains($sql, self::SQL_IN_CLAUSE)) {
            throw new \InvalidArgumentException('SQL is missing the sales channel placeholder.');
        }

        return str_replace(self::SQL_IN_CLAUSE, 'IN (' . implode(', ', $ids) . ')', $sql);
    }
}

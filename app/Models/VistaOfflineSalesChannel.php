<?php

namespace App\Models;

use App\Services\VistaOfflineOrders\VistaSalesChannels;
use Illuminate\Database\Eloquent\Model;

class VistaOfflineSalesChannel extends Model
{
    protected $table = 'vista_offline_sales_channels';

    protected $guarded = false;

    protected $casts = [
        'channel_id' => 'integer',
        'enabled' => 'boolean',
    ];

    public static function ensureDefaults(): void
    {
        foreach (VistaSalesChannels::CHANNELS as $id => $name) {
            static::query()->firstOrCreate(
                ['channel_id' => $id],
                ['name' => $name, 'enabled' => true],
            );
        }
    }

    /**
     * @return list<int>
     */
    public static function enabledIds(): array
    {
        static::ensureDefaults();

        return VistaSalesChannels::sanitize(
            static::query()->where('enabled', true)->pluck('channel_id')->all()
        );
    }

    /**
     * @param array<int|string> $enabledIds
     */
    public static function setEnabled(array $enabledIds): void
    {
        static::ensureDefaults();

        $enabled = array_flip(VistaSalesChannels::sanitize($enabledIds));

        foreach (array_keys(VistaSalesChannels::CHANNELS) as $id) {
            static::query()
                ->where('channel_id', $id)
                ->update(['enabled' => isset($enabled[$id])]);
        }
    }
}

<?php

namespace App\Filament\Resources\VistaOfflineOrderSyncLogResource\Pages;

use App\Filament\Resources\VistaOfflineOrderSyncLogResource;
use App\Filament\Resources\VistaOfflineOrderSyncLogResource\Widgets\SalesChannelSyncSettings;
use Filament\Resources\Pages\ListRecords;

class ListVistaOfflineOrderSyncLogs extends ListRecords
{
    protected static string $resource = VistaOfflineOrderSyncLogResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            SalesChannelSyncSettings::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 1;
    }
}


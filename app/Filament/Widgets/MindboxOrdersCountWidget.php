<?php

namespace App\Filament\Widgets;

use App\Models\VistaOfflineOrderSyncLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MindboxOrdersCountWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $since = now()->subDay();

        $successCount = VistaOfflineOrderSyncLog::query()
            ->where('status', VistaOfflineOrderSyncLog::STATUS_SUCCESS)
            ->where('created_at', '>=', $since)
            ->count();

        $failedCount = VistaOfflineOrderSyncLog::query()
            ->where('status', VistaOfflineOrderSyncLog::STATUS_FAILED)
            ->where('created_at', '>=', $since)
            ->count();

        return [
            Stat::make('Заказов в Mindbox за сутки', $successCount)
                ->description('Vista → Mindbox, статус success')
                ->icon('heroicon-o-arrow-path'),
            Stat::make('Ошибок за сутки', $failedCount)
                ->description('Vista → Mindbox, статус failed')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($failedCount > 0 ? 'danger' : 'success'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Cinema;
use App\Models\Merchant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MerchantCount extends BaseWidget
{
    protected function getStats(): array
    {
        $merchants = Merchant::count();
        $cinemas = Cinema::count();
        return [
            Stat::make(__('Merchants'), $merchants),
            Stat::make(__('Cinemas'), $cinemas),
        ];
    }
}

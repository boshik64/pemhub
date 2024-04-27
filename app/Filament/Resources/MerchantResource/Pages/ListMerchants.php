<?php

namespace App\Filament\Resources\MerchantResource\Pages;

use App\Filament\Resources\MerchantResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMerchants extends ListRecords
{
    protected static string $resource = MerchantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }



//    protected function getTableQuery(): ?Builder
//    {
//        $user = Filament::auth()->user();
//        $workstations = $user->workstations->pluck('id');
//
//        return parent::getTableQuery()->whereIn('workstation_id', $workstations);
//    }
}

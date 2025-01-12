<?php

namespace App\Filament\Resources\ManualSyncResource\Pages;

use App\Filament\Resources\ManualSyncResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManualSync extends EditRecord
{
    protected static string $resource = ManualSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

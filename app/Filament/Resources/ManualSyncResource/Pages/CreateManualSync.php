<?php

namespace App\Filament\Resources\ManualSyncResource\Pages;

use App\Filament\Resources\ManualSyncResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateManualSync extends CreateRecord
{
    protected static string $resource = ManualSyncResource::class;
}

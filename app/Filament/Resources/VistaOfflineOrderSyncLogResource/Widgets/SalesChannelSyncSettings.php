<?php

namespace App\Filament\Resources\VistaOfflineOrderSyncLogResource\Widgets;

use App\Models\VistaOfflineSalesChannel;
use App\Services\VistaOfflineOrders\VistaSalesChannels;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;

class SalesChannelSyncSettings extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.vista-sales-channel-sync';

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'enabled' => array_map('strval', VistaOfflineSalesChannel::enabledIds()),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                CheckboxList::make('enabled')
                    ->label('Синхронизировать')
                    ->options(VistaSalesChannels::options())
                    ->columns(3)
                    ->live()
                    ->afterStateUpdated(function (?array $state): void {
                        VistaOfflineSalesChannel::setEnabled($state ?? []);
                    }),
            ])
            ->statePath('data');
    }
}

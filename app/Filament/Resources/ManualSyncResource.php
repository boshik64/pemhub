<?php

namespace App\Filament\Resources;

use App\Models\ManualSync;
use App\Filament\Resources\ManualSyncResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ManualSyncResource\Pages\ListManualSyncs;

class ManualSyncResource extends Resource
{
    protected static ?string $model = ManualSync::class;

    protected static ?string $navigationLabel = 'Ручная синхронизация';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Инструменты';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('type')
                ->label('Тип синхронизации')
                ->disabled(),
            Forms\Components\TextInput::make('status')
                ->label('Статус')
                ->disabled(),
            Forms\Components\Textarea::make('details')
                ->label('Детали')
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Тип')->sortable(),
                Tables\Columns\IconColumn::make('status')->label('Статус')->sortable()

                    ->icon(function (ManualSync $manualSync): string {
                        if ($manualSync->status == ManualSync::ACCESS) {
                            return 'heroicon-o-check-circle';
                        } else {
                            return 'heroicon-o-exclamation-circle';
                        }
                    })
                    ->color(function (ManualSync $manualSync): string {
                        if ($manualSync->status == ManualSync::ACCESS) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Создано')->dateTime(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Action::make('viewOutput')
                    ->label('Просмотр результата')
                    ->modalHeading('Результат выполнения синхронизации')
                    ->modalContent(fn($record) => view('filament.resources.manual-syncs.modal', ['output' => $record->output])) // Передаём результат в модальное окно
                    ->button()
            ])
            ->filters([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManualSyncs::route('/'),
        ];
    }
}

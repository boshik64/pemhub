<?php

namespace App\Filament\Resources;

use App\Models\ManualSync;
use App\Filament\Resources\ManualSyncResource\Pages;
use App\Filament\Resources\CinemaResource\RelationManagers;
use App\Models\Cinema;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Tables\Columns\TextColumn::make('status')->label('Статус')->sortable(),
                Tables\Columns\TextColumn::make('details')->label('Детали')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->label('Создано')->dateTime(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManualSyncs::route('/'),
        ];
    }
}

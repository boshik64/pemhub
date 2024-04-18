<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerchantResource\Pages;
use App\Filament\Resources\MerchantResource\RelationManagers;
use App\Models\Merchant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MerchantResource extends Resource
{
    protected static ?string $model = Merchant::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('mid')
                    ->required()
                    ->length(10)
                    ->numeric()
                    ->mask('9999999999'),

                Forms\Components\Select::make('merchant_type')
                    ->required()
                    ->options([
                        'pushkin' => 'Пушкинская карта',
                        'visa' => 'Кредитная карта',
                    ])
                    ->default('visa'),

                Forms\Components\Select::make('workstation')
                    ->required()
                    ->options([
                        'WWW' => 'Сайт',
                        'MOB' => 'Мобильное приложение',
                    ])
                    ->default('WWW'),

                Forms\Components\TextInput::make('department_name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Введите наименование мерчанта'),

                Forms\Components\Select::make('cinema_id')
                    ->relationship('cinema', 'cinema_name')
                    ->searchable()
                    ->preload()
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mid')
                    ->searchable(),

                TextColumn::make('merchant_type')
                    ->searchable(),

                TextColumn::make('workstation')
                    ->searchable(),

                TextColumn::make('cinema.cinema_name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMerchants::route('/'),
            'create' => Pages\CreateMerchant::route('/create'),
            'edit' => Pages\EditMerchant::route('/{record}/edit'),
        ];
    }


//    protected static ?string $label = 'Мерчант';
}

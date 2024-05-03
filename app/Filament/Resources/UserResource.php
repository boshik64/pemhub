<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use BezhanSalleh\FilamentShield\Resources\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(25),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->maxLength(50)
                    ->email()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->maxLength(50)
                    ->password()
                    ->revealable(function (User $user) {
                        return !$user->exists;
                    }),
                Forms\Components\Select::make('workstations')
                    ->multiple()
//                    ->required()
                    ->relationship('workstations', 'name')
                    ->preload(),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->hidden(function (User $user) {
                        return $user->hasRole('super_admin');
                    })
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('roles.name')
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action) {
                            $authUser = Filament::auth()->user()->id;
                            $selectedUsers = $action->getRecords();
                            foreach ($selectedUsers as $selectedUser) {
                                if ($selectedUser->id == $authUser) {
                                    Notification::make()
                                        ->warning()
                                        ->title('ОШИБКА')
                                        ->body('Нельзя удалить самого себя, это суицид.')
                                        ->persistent()
                                        ->send();
                                    $action->cancel();
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

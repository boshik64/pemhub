<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerchantResource\Pages;
use App\Filament\Resources\MerchantResource\RelationManagers;
use App\Models\Cinema;
use App\Models\Merchant;
use App\OpenSSL\OpenSSlFactory;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use function Laravel\Prompts\select;

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
                    ->unique('merchants', 'mid', ignoreRecord: true)
                    ->mask('9999999999'),

                Forms\Components\Select::make('merchant_type')
                    ->required()
                    ->options([
                        'pushkin' => 'Пушкинская карта',
                        'visa' => 'Кредитная карта',
                    ])
                    ->default('visa'),

                Forms\Components\Select::make('workstation_id')
                    ->relationship('workstation', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                    ])
                    ->required(),

                Forms\Components\TextInput::make('department_name')
                    ->required()
                    ->maxLength(255)
                    ->unique('merchants', 'department_name', ignoreRecord: true)
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
            ->modifyQueryUsing(function (Builder $query) {
                $user = Filament::auth()->user();
                $workstations = $user->workstations->pluck('id');
                if (!$workstations->empty()) {
                    $query->whereIn('workstation_id', $workstations);
                }

            })
            ->columns([
                TextColumn::make('mid')
                    ->searchable(),

                TextColumn::make('merchant_type')
                    ->searchable(),

                TextColumn::make('workstation.name')
                    ->searchable(),

                TextColumn::make('cinema.cinema_name')
                    ->searchable(),
                IconColumn::make('expiry_status')
                    ->extraAttributes(function (Merchant $merchant) {
                        return [
                            'title' => __('Валиден до: :time', [
                                'time' => $merchant->next_update
                            ])
                        ];
                    })
                    ->getStateUsing(fn() => true)
                    ->icon(function (Merchant $merchant): string {
                        if ($merchant->getExpiryStatus() == Merchant::CERT_VALID) {
                            return 'heroicon-o-check-circle';
                        } elseif ($merchant->getExpiryStatus() == Merchant::CERT_EXPIRES) {
                            return 'heroicon-o-exclamation-circle';
                        } elseif ($merchant->getExpiryStatus() == Merchant::CERT_EXPIRED) {
                            return 'heroicon-o-no-symbol';
                        }
                    })
                    ->color(function (Merchant $merchant): string {
                        if ($merchant->getExpiryStatus() == Merchant::CERT_VALID) {
                            return 'success';
                        } elseif ($merchant->getExpiryStatus() == Merchant::CERT_EXPIRES) {
                            return 'warning';
                        } elseif ($merchant->getExpiryStatus() == Merchant::CERT_EXPIRED) {
                            return 'danger';
                        }
                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('create_and_download_zip')
                    ->label('Create and Download ZIP')
                    ->icon('heroicon-o-pencil')
                    ->action(function (Merchant $merchant) {
                        $zipFileName = storage_path("$merchant->mid.zip");

                        $zip = new ZipArchive();

                        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                            dd('архив не создался');
                        }

                        $factory = new OpenSSlFactory();

                        $pair = $factory->createPair($merchant->getDistinguishedNames());

                        $zip->addFromString("$merchant->mid.req", $pair->getRequest());
                        $zip->addFromString("$merchant->mid.key", $pair->getPrivate());

                        $zip->close();

                        $merchant->update(['next_update' => Carbon::now()->addDays(365)]);

                        return response()->download($zipFileName)->deleteFileAfterSend();
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('create_and_download_zip')
                        ->label('Create and Download ZIP')
                        ->icon('heroicon-o-pencil')
                        ->action(function (Collection $models) {
                            $zipFileName = storage_path('collection.zip');

                            $zip = new ZipArchive();
                            if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                                dd('архив не создался');
                            }

                            //Бошик красавчик
                            $factory = new OpenSSlFactory();

                            $nextUpdate = Carbon::now()->addDays(365);

                            foreach ($models as $model) {
                                $pair = $factory->createPair($model->getDistinguishedNames());

                                $zip->addFromString("$model->mid.req", $pair->getRequest());
                                $zip->addFromString("$model->mid.key", $pair->getPrivate());

                                // Присвоение новой даты столбцу "next_update"
                                $model->update(['next_update' => $nextUpdate]);
                            }

                            $zip->close();
                            return response()->download($zipFileName)->deleteFileAfterSend();
                        })

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

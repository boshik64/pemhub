<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VistaOfflineOrderSyncLogResource\Pages;
use App\Jobs\SendOfflineOrderToMindbox;
use App\Models\VistaOfflineOrderSyncLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VistaOfflineOrderSyncLogResource extends Resource
{
    protected static ?string $model = VistaOfflineOrderSyncLog::class;

    protected static ?string $navigationLabel = 'Vista → Mindbox (оффлайн-заказы)';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Инструменты';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('transaction_id')
                ->label('Transaction ID')
                ->disabled(),
            Forms\Components\Placeholder::make('membership_id')
                ->label('Membership ID')
                ->content(fn (?VistaOfflineOrderSyncLog $record): string => $record ? ($record->source_data['header']['transaction_membershipid'] ?? '—') : '—'),
            Forms\Components\Placeholder::make('booking_id')
                ->label('Booking ID')
                ->content(fn (?VistaOfflineOrderSyncLog $record): string => $record ? ($record->source_data['header']['transaction_bookingId'] ?? '—') : '—'),
            Forms\Components\Placeholder::make('sales_channel')
                ->label('Канал продаж')
                ->content(fn (?VistaOfflineOrderSyncLog $record): string => $record ? self::salesChannelName($record->source_data['header']['transaction_salesChannel'] ?? null) : '—'),
            Forms\Components\TextInput::make('status')
                ->label('Статус')
                ->disabled(),
            Forms\Components\Textarea::make('error_message')
                ->label('Ошибка')
                ->disabled()
                ->rows(3),
            Forms\Components\Textarea::make('request_payload')
                ->label('Request payload (Mindbox)')
                ->disabled()
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->rows(12),
            Forms\Components\Textarea::make('response_payload')
                ->label('Response payload (Mindbox)')
                ->disabled()
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->rows(12),
            Forms\Components\Textarea::make('source_data')
                ->label('Source data (Vista)')
                ->disabled()
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->rows(12),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('membership_id')
                    ->label('Membership ID')
                    ->getStateUsing(fn (VistaOfflineOrderSyncLog $record) => $record->source_data['header']['transaction_membershipid'] ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('source_data->header->transaction_membershipid', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('booking_id')
                    ->label('Booking ID')
                    ->getStateUsing(fn (VistaOfflineOrderSyncLog $record) => $record->source_data['header']['transaction_bookingId'] ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('source_data->header->transaction_bookingId', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('sales_channel')
                    ->label('Канал продаж')
                    ->getStateUsing(fn (VistaOfflineOrderSyncLog $record) => self::salesChannelName($record->source_data['header']['transaction_salesChannel'] ?? null))
                    ->badge()
                    ->color(fn (VistaOfflineOrderSyncLog $record) => match ((int) ($record->source_data['header']['transaction_salesChannel'] ?? 0)) {
                        1 => 'gray',
                        2 => 'info',
                        8 => 'success',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (VistaOfflineOrderSyncLog $record) => match ($record->status) {
                        VistaOfflineOrderSyncLog::STATUS_SUCCESS => 'success',
                        VistaOfflineOrderSyncLog::STATUS_FAILED => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('attempts')
                    ->label('Попытки')
                    ->sortable(),
                Tables\Columns\TextColumn::make('error_message')
                    ->label('Ошибка')
                    ->limit(60)
                    ->tooltip(fn (VistaOfflineOrderSyncLog $record) => $record->error_message),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->timezone(config('app.display_timezone'))->format('d.m.Y H:i') : '')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->timezone(config('app.display_timezone'))->format('d.m.Y H:i') : '')
                    ->sortable(),
            ])
            ->defaultSort('transaction_id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        VistaOfflineOrderSyncLog::STATUS_PENDING => 'pending',
                        VistaOfflineOrderSyncLog::STATUS_SUCCESS => 'success',
                        VistaOfflineOrderSyncLog::STATUS_FAILED => 'failed',
                    ]),
                Tables\Filters\Filter::make('sales_channel')
                    ->form([
                        Forms\Components\Select::make('value')
                            ->label('Канал продаж')
                            ->options([
                                1 => 'Point of Sale',
                                2 => 'Kiosk',
                                8 => 'Smartix(КСО)',
                            ])
                            ->placeholder('Все'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if ($value === null || $value === '') {
                            return $query;
                        }
                        return $query->where('source_data->header->transaction_salesChannel', (int) $value);
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('С'),
                        Forms\Components\DatePicker::make('until')->label('До'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Открыть'),
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->visible(fn (VistaOfflineOrderSyncLog $record) => in_array($record->status, [
                        VistaOfflineOrderSyncLog::STATUS_FAILED,
                        VistaOfflineOrderSyncLog::STATUS_PENDING,
                    ], true))
                    ->action(function (VistaOfflineOrderSyncLog $record): void {
                        // Для принудительной повторной отправки используем сохранённый source_data.
                        // Если membershipID отсутствует, Job снова отметит failed (но это явно покажет причину).
                        if (is_array($record->source_data)) {
                            SendOfflineOrderToMindbox::dispatch($record->source_data);
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    /**
     * Название канала продаж по ID (Vista transaction_salesChannel).
     */
    public static function salesChannelName($salesChannelId): string
    {
        return match ((int) $salesChannelId) {
            1 => 'Point of Sale',
            2 => 'Kiosk',
            8 => 'Smartix(КСО)',
            default => (string) ($salesChannelId ?? '—'),
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVistaOfflineOrderSyncLogs::route('/'),
        ];
    }
}


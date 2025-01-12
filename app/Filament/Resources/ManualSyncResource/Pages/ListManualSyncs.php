<?php

namespace App\Filament\Resources\ManualSyncResource\Pages;

use Filament\Facades\Notification;
use App\Filament\Resources\ManualSyncResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use App\Models\ManualSync;

use Filament\Notifications\Notification as FilamentNotification;






class ListManualSyncs extends ListRecords
{
    protected static string $resource = ManualSyncResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('sync')
                ->label('Выполнить синхронизацию')
                ->color('primary')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    try {
                        // Вызов команды Artisan для выполнения синхронизации
                        $exitCode = Artisan::call('app:sync-karo-films-to-flix ');

                        // Проверка успешности выполнения команды
                        if ($exitCode === 0) {
                            // Логирование успешной синхронизации
                            ManualSync::create([
                                'type' => 'manual',
                                'status' => 'completed',
                                'details' => 'Синхронизация успешно завершена',
                            ]);

                            // Уведомление об успешной синхронизации через Filament
                            FilamentNotification::make()
                                ->title('Синхронизация успешно завершена!')
                                ->success()
                                ->send();
                        } else {
                            // Логирование ошибки синхронизации
                            ManualSync::create([
                                'type' => 'manual',
                                'status' => 'failed',
                                'details' => 'Ошибка при выполнении синхронизации',
                            ]);

                            // Уведомление об ошибке через Filament
                            FilamentNotification::make()
                                ->title('Ошибка синхронизации')
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        // Логирование ошибки синхронизации
                        ManualSync::create([
                            'type' => 'manual',
                            'status' => 'failed',
                            'details' => $e->getMessage(),
                        ]);

                        // Уведомление об ошибке через Filament
                        FilamentNotification::make()
                            ->title('Ошибка синхронизации: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

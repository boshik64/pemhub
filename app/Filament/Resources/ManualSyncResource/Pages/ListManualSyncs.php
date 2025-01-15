<?php
namespace App\Filament\Resources\ManualSyncResource\Pages;

use App\Filament\Resources\ManualSyncResource;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Forms\Components\Modal; // Не нужно, убираем это
use App\Models\ManualSync;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;


class ListManualSyncs extends ListRecords
{
    protected static string $resource = ManualSyncResource::class;
    // Определяем действия на странице
    public function getActions(): array
    {
        return [
            Action::make('sync')
                ->label('Запустить синхронизацию')
                ->action('sync') // Указываем метод sync
                ->icon('heroicon-o-rectangle-stack')
                ->color('primary'),
        ];
    }
    public function sync(): void
    {
        try {
            // Выполнение команды
            Artisan::call('app:sync-karo-films-to-flix');
            $output = Artisan::output(); // Получаем вывод команды

            // Подсвечиваем ошибку в выводе
            $output = preg_replace('/(Ошибка отправки POST-запроса)/', '<span style="color: red; font-weight: bold;">$1</span>', $output);

            // Сохраняем результат в базу данных
            ManualSync::create([
                'type' => 'manual',
                'status' => 'completed', // Устанавливаем статус "completed"
                'details' => 'Синхронизация завершена', // Статус для details
                'output' => $output, // Сохраняем вывод команды с подсветкой
            ]);

            // Отображаем уведомление об успехе
            Notification::make()
                ->title('Синхронизация выполнена')
                ->success()
                ->body('Результат выполнения команды сохранён в базу данных.')
                ->send();
        } catch (\Exception $e) {
            // Отображаем уведомление об ошибке
            Notification::make()
                ->title('Ошибка синхронизации!')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    // Метод для отображения модального окна с результатом
    public function viewOutput(ManualSync $record)
    {
        return $this->modal()
            ->title('Результат выполнения синхронизации')
            ->body(fn () => view('filament.resources.manual-syncs.modal', ['output' => $record->output]))  // Указываем кастомное содержимое
            ->open();
    }
}

<?php
namespace App\Filament\Resources\ManualSyncResource\Pages;

use App\Filament\Resources\ManualSyncResource;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Forms\Components\Modal; // Не нужно, убираем это
use App\Models\ManualSync;

class ListManualSyncs extends ListRecords
{
    protected static string $resource = ManualSyncResource::class;

    public function getActions(): array
    {
        return [
            Action::make('sync')
                ->label('Запустить синхронизацию')
                ->action('sync')
                ->icon('heroicon-o-rectangle-stack')
                ->color('primary')
        ];
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

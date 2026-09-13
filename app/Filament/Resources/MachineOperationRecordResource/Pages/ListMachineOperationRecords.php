<?php

namespace App\Filament\Resources\MachineOperationRecordResource\Pages;

use App\Filament\Resources\MachineOperationRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachineOperationRecords extends ListRecords
{
    protected static string $resource = MachineOperationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary'),
            Actions\Action::make('sync_barcodes')
                ->label('Sync Barcode')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $records = \App\Models\MachineOperationRecord::whereNull('barcode')->whereNotNull('machine_program_id')->get();
                    $count = 0;
                    foreach ($records as $record) {
                        $program = \App\Models\MachineProgram::find($record->machine_program_id);
                        if ($program && !empty($program->barcode)) {
                            $record->barcode = $program->barcode;
                            $record->saveQuietly();
                            $count++;
                        }
                    }
                    \Filament\Notifications\Notification::make()
                        ->title("Berhasil menyinkronkan {$count} barcode!")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => \Filament\Resources\Components\Tab::make('Active Jobs')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', '!=', 'completed')),
            'completed' => \Filament\Resources\Components\Tab::make('Laporan (Completed)')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'completed')),
        ];
    }
}

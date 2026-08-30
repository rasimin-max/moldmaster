<?php

namespace App\Filament\Resources\MachineProgramResource\Pages;

use App\Filament\Resources\MachineProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachinePrograms extends ListRecords
{
    protected static string $resource = MachineProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(\App\Imports\MachineProgramExcelImport::class)
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->label('Impor Excel'),
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\MachineProgramExporter::class)
                ->color('warning')
                ->icon('heroicon-o-arrow-down-tray'),
            Actions\CreateAction::make(),
        ];
    }
}

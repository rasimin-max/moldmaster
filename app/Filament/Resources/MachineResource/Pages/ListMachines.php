<?php

namespace App\Filament\Resources\MachineResource\Pages;

use App\Filament\Resources\MachineResource;
use App\Filament\Traits\HasExcelImport;
use App\Imports\MachinesImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachines extends ListRecords
{
    use HasExcelImport;

    protected static string $resource = MachineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()->fromTable(),
                ]),
            $this->makeImportAction('Import', MachinesImport::class, 'Import Mesin'),
            Actions\CreateAction::make()->label('+ Tambah Mesin'),
        ];
    }
}

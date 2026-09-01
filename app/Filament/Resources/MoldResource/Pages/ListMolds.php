<?php

namespace App\Filament\Resources\MoldResource\Pages;

use App\Filament\Resources\MoldResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMolds extends ListRecords
{
    protected static string $resource = MoldResource::class;
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
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(\App\Imports\MoldsImport::class)
                ->color('warning')
                ->modalHeading('Import Molds (Excel)')
                ->label('Import Excel'),
            Actions\CreateAction::make()->label('+ Tambah Mold')
        ];
    }
}

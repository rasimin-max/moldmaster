<?php

namespace App\Filament\Resources\ToolResource\Pages;

use App\Filament\Resources\ToolResource;
use App\Filament\Traits\HasExcelImport;
use App\Imports\ToolsImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTools extends ListRecords
{
    use HasExcelImport;

    protected static string $resource = ToolResource::class;

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
                ->label('Import Alat')
                ->color('primary')
                ->use(ToolsImport::class),
            Actions\CreateAction::make()->label('+ Tambah Alat'),
        ];
    }
}

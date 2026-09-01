<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use App\Filament\Traits\HasExcelImport;
use App\Imports\VendorsImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendors extends ListRecords
{
    use HasExcelImport;

    protected static string $resource = VendorResource::class;

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
            $this->makeImportAction('Import', VendorsImport::class, 'Import Supplier'),
            Actions\CreateAction::make()->label('+ Tambah Supplier'),
        ];
    }
}

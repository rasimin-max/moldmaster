<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendors extends ListRecords
{
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
            \Filament\Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\VendorImporter::class)
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray'),
            Actions\CreateAction::make()->label('+ Tambah Supplier')
        ];
    }
}

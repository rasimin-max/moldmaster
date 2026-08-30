<?php

namespace App\Filament\Resources\ToolResource\Pages;

use App\Filament\Resources\ToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTools extends ListRecords
{
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
            \Filament\Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\ToolImporter::class)
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray'),
            Actions\CreateAction::make()->label('+ Tambah Alat')
        ];
    }
}

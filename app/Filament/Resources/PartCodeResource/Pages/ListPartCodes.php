<?php

namespace App\Filament\Resources\PartCodeResource\Pages;

use App\Filament\Resources\PartCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartCodes extends ListRecords
{
    protected static string $resource = PartCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->fromTable()
                        ->withNamesAsHeadings()
                        ->ignoreFormatting()
                        ->withFilename('Template-Part-Code-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            \EightyNine\ExcelImport\ExcelImportAction::make()->color('primary'),
            Actions\CreateAction::make(),
        ];
    }
}

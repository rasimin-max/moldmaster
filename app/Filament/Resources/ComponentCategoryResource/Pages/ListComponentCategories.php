<?php

namespace App\Filament\Resources\ComponentCategoryResource\Pages;

use App\Filament\Resources\ComponentCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComponentCategories extends ListRecords
{
    protected static string $resource = ComponentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->fromTable()
                        ->withFilename('Template-Bagian-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            \EightyNine\ExcelImport\ExcelImportAction::make()->color('primary'),
            Actions\CreateAction::make(),
        ];
    }
}

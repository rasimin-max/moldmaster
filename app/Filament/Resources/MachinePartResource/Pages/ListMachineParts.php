<?php

namespace App\Filament\Resources\MachinePartResource\Pages;

use App\Filament\Resources\MachinePartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachineParts extends ListRecords
{
    protected static string $resource = MachinePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->fromTable()
                        ->withNamesAsHeadings()
                        ->ignoreFormatting()
                        ->withFilename('Template-Machine-Part-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            \EightyNine\ExcelImport\ExcelImportAction::make()->color('primary'),
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\SagyoTypeResource\Pages;

use App\Filament\Resources\SagyoTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSagyoTypes extends ManageRecords
{
    protected static string $resource = SagyoTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->fromTable()
                        ->withFilename('Template-Type-Sagyo-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            \EightyNine\ExcelImport\ExcelImportAction::make()->color('primary'),
            Actions\CreateAction::make(),
        ];
    }
}

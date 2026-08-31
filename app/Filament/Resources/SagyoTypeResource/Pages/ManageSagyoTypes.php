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
                        ->withNamesAsHeadings()
                        ->ignoreFormatting()
                        ->withFilename('Template-Type-Sagyo-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary')
                ->processCollectionUsing(function (string $modelClass, \Illuminate\Support\Collection $collection) {
                    foreach ($collection as $row) {
                        $data = $row->toArray();
                        if (isset($data['name'])) {
                            $modelClass::updateOrCreate(['name' => $data['name']], $data);
                        } else {
                            $modelClass::create($data);
                        }
                    }
                    return $collection;
                }),
            Actions\CreateAction::make(),
        ];
    }
}

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
                        ->withNamesAsHeadings()
                        ->ignoreFormatting()
                        ->withFilename('Template-Bagian-' . date('Y-m-d')),
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

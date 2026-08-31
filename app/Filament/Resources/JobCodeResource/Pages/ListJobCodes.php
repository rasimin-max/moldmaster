<?php

namespace App\Filament\Resources\JobCodeResource\Pages;

use App\Filament\Resources\JobCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobCodes extends ListRecords
{
    protected static string $resource = JobCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->fromTable()
                        ->withNamesAsHeadings()
                        ->ignoreFormatting()
                        ->withFilename('Template-Job-Kode-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary')
                ->processCollectionUsing(function (string $modelClass, \Illuminate\Support\Collection $collection) {
                    foreach ($collection as $row) {
                        $data = $row->toArray();
                        if (isset($data['code'])) {
                            $modelClass::updateOrCreate(['code' => $data['code']], $data);
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

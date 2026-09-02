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
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary')
                ->processCollectionUsing(function (string $modelClass, \Illuminate\Support\Collection $collection) {
                    foreach ($collection as $row) {
                        $data = $row->toArray();
                        
                        $item = $data['item'] ?? $data['nama_part'] ?? $data['nama part'] ?? null;
                        $code = $data['code'] ?? $data['kode'] ?? null;
                        
                        // Prevent error on trim(null) and ensure we have both item and code since they are required by DB
                        if (empty($item) || empty($code)) {
                            continue;
                        }
                        
                        $code = trim((string) $code);
                        $item = trim((string) $item);
                        
                        if (!empty($code)) {
                            $modelClass::updateOrCreate(
                                ['code' => $code],
                                ['item' => $item, 'code' => $code]
                            );
                        }
                    }
                    return $collection;
                }),
            Actions\CreateAction::make(),
        ];
    }
}

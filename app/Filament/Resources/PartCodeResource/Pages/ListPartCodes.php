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
                        ->ignoreFormatting()
                        ->withFilename('Template-Part-Code-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary')
                ->processCollectionUsing(function (string $modelClass, \Illuminate\Support\Collection $collection) {
                    foreach ($collection as $index => $row) {
                        $originalData = $row->toArray();
                        
                        // Normalize keys
                        $data = [];
                        $hasData = false;
                        foreach ($originalData as $k => $v) {
                            if ($v !== null && $v !== '') {
                                $hasData = true;
                            }
                            $nk = str_replace([' ', '.'], '_', strtolower(trim((string)$k)));
                            $data[$nk] = $v;
                        }
                        
                        if (!$hasData) {
                            continue; // Skip completely empty rows
                        }
                        
                        $item = $data['item'] ?? $data['nama_part'] ?? null;
                        $code = $data['code'] ?? $data['kode'] ?? null;
                        
                        if (empty($item)) {
                            throw new \Exception("Gagal Import: Kolom 'Nama Part' atau 'Item' tidak ditemukan atau kosong.");
                        }
                        
                        if (empty($code)) {
                            throw new \Exception("Gagal Import: Kolom 'Kode' atau 'Code' tidak ditemukan atau kosong.");
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

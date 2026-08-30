<?php

namespace App\Filament\Resources\InboundComponentResource\Pages;

use App\Filament\Resources\InboundComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInboundComponents extends ListRecords
{
    protected static string $resource = InboundComponentResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\StockMovementExporter::class)
                ->label('Export'),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(\App\Imports\InboundComponentImport::class)
                ->sampleExcel([['kode_komponen' => 'COMP-001', 'jumlah' => 10, 'nama_operator' => 'Budi', 'tujuan' => 'Produksi', 'catatan' => 'Restok']] )
                ->color('success')
                ->label('Import Excel'),
            Actions\CreateAction::make()->label('+ Input Transaksi'),
        ];
    }
    
}

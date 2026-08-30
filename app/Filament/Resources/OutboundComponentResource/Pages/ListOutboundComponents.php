<?php

namespace App\Filament\Resources\OutboundComponentResource\Pages;

use App\Filament\Resources\OutboundComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutboundComponents extends ListRecords
{
    protected static string $resource = OutboundComponentResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\StockMovementExporter::class)
                ->label('Export'),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(\App\Imports\OutboundComponentImport::class)
                ->sampleExcel([['kode_komponen' => 'COMP-001', 'jumlah' => 5, 'nama_operator' => 'Budi', 'tujuan' => 'Maintenance Mesin A', 'catatan' => 'Dipakai']] )
                ->color('success')
                ->label('Import Excel'),
            Actions\CreateAction::make()->label('+ Input Transaksi'),
        ];
    }
    
}

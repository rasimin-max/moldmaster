<?php

namespace App\Filament\Resources\OutboundToolResource\Pages;

use App\Filament\Resources\OutboundToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutboundTools extends ListRecords
{
    protected static string $resource = OutboundToolResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\ToolMovementExporter::class)
                ->label('Export'),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(\App\Imports\OutboundToolImport::class)
                ->sampleExcel([['kode_alat' => 'TOOL-001', 'jumlah' => 2, 'nama_operator' => 'Andi', 'tujuan' => 'Rusak', 'catatan' => 'Dibuang']] )
                ->color('success')
                ->label('Import Excel'),
            Actions\CreateAction::make()->label('+ Input Transaksi'),
        ];
    }
    
}

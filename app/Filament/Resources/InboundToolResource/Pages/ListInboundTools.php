<?php

namespace App\Filament\Resources\InboundToolResource\Pages;

use App\Filament\Resources\InboundToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInboundTools extends ListRecords
{
    protected static string $resource = InboundToolResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\ToolMovementExporter::class)
                ->label('Export'),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(\App\Imports\InboundToolImport::class)
                ->sampleExcel([['kode_alat' => 'TOOL-001', 'jumlah' => 5, 'nama_operator' => 'Andi', 'tujuan' => 'Baru Beli', 'catatan' => 'Baru']] )
                ->color('success')
                ->label('Import Excel'),
            Actions\CreateAction::make()->label('+ Input Transaksi'),
        ];
    }
    
}

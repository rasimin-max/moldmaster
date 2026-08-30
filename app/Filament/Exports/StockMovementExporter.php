<?php

namespace App\Filament\Exports;

use App\Models\StockMovement;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StockMovementExporter extends Exporter
{
    protected static ?string $model = StockMovement::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('reference_number')->label('No. Referensi'),
            ExportColumn::make('type')->label('Jenis Transaksi'),
            ExportColumn::make('component.code')->label('Kode Komponen'),
            ExportColumn::make('component.name')->label('Nama Komponen'),
            ExportColumn::make('quantity')->label('Jumlah'),
            ExportColumn::make('mold.code')->label('Kode Mold'),
            ExportColumn::make('operator_name')->label('Nama Operator'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('created_at')->label('Tanggal Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stock movement export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}

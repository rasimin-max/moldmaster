<?php

namespace App\Filament\Exports;

use App\Models\OrderPriority;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderPriorityExporter extends Exporter
{
    protected static ?string $model = OrderPriority::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('item_code')->label('Kode Item'),
            ExportColumn::make('item_name')->label('Nama Item'),
            ExportColumn::make('type')->label('Jenis')->formatStateUsing(fn ($state) => match($state) {
                'component' => 'Part Common',
                'tool' => 'Tool',
                'project_component' => 'Komponen Project',
                default => $state,
            }),
            ExportColumn::make('category')->label('Kategori'),
            ExportColumn::make('reason')->label('Alasan Prioritas'),
            ExportColumn::make('current_stock')->label('Stok Saat Ini'),
            ExportColumn::make('min_stock')->label('Min Stok/Safety'),
            ExportColumn::make('order_qty')->label('Kekurangan (Qty Order)'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order priority export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}

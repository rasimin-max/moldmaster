<?php

namespace App\Filament\Imports;

use App\Models\StockMovement;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class StockMovementImporter extends Importer
{
    protected static ?string $model = StockMovement::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('type')
                ->requiredMapping()
                ->rules(['required', 'in:in,out,return,adjustment']),
            ImportColumn::make('component')
                ->relationship('component', 'code')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('quantity')
                ->requiredMapping()
                ->rules(['required', 'integer', 'min:1']),
            ImportColumn::make('mold')
                ->relationship('mold', 'code')
                ->rules(['nullable']),
            ImportColumn::make('operator_name')
                ->rules(['nullable', 'string']),
            ImportColumn::make('purpose')
                ->rules(['nullable', 'string']),
            ImportColumn::make('notes')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?StockMovement
    {
        $movement = new StockMovement();
        $movement->requested_by = auth()->id() ?? 1;
        
        return $movement;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your stock movement import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

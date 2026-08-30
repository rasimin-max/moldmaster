<?php

namespace App\Filament\Imports;

use App\Models\ToolMovement;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ToolMovementImporter extends Importer
{
    protected static ?string $model = ToolMovement::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('type')
                ->requiredMapping()
                ->rules(['required', 'in:in,out,adjustment']),
            ImportColumn::make('tool')
                ->relationship('tool', 'code')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('quantity')
                ->requiredMapping()
                ->rules(['required', 'integer', 'min:1']),
            ImportColumn::make('operator_name')
                ->rules(['nullable', 'string']),
            ImportColumn::make('purpose')
                ->rules(['nullable', 'string']),
            ImportColumn::make('notes')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?ToolMovement
    {
        $movement = new ToolMovement();
        $movement->requested_by = auth()->id() ?? 1;
        return $movement;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your tool movement import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

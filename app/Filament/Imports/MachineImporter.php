<?php

namespace App\Filament\Imports;

use App\Models\Machine;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class MachineImporter extends Importer
{
    protected static ?string $model = Machine::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('type')->rules(['nullable', 'max:255']),
            ImportColumn::make('brand')->rules(['nullable', 'max:255']),
            ImportColumn::make('model_number')->rules(['nullable', 'max:255']),
            ImportColumn::make('serial_number')->rules(['nullable', 'max:255']),
            ImportColumn::make('area')->rules(['nullable', 'max:255']),
            ImportColumn::make('year_purchased')->numeric()->rules(['nullable', 'integer']),
            ImportColumn::make('status')->rules(['nullable', 'max:255']),
            ImportColumn::make('hourly_rate')->numeric()->rules(['nullable', 'numeric']),
            ImportColumn::make('notes')->rules(['nullable']),
        ];
    }

    public function resolveRecord(): ?Machine
    {
        return Machine::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your machine import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

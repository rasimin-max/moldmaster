<?php

namespace App\Filament\Imports;

use App\Models\Mold;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class MoldImporter extends Importer
{
    protected static ?string $model = Mold::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('mold_number')->rules(['nullable', 'max:255']),
            ImportColumn::make('code')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('project_name')->rules(['nullable', 'max:255']),
            ImportColumn::make('customer')->rules(['nullable', 'max:255']),
            ImportColumn::make('product_type')->rules(['nullable', 'max:255']),
            ImportColumn::make('cavity')->numeric()->rules(['nullable', 'integer']),
            ImportColumn::make('shot_life')->numeric()->rules(['nullable', 'integer']),
            ImportColumn::make('current_shot')->numeric()->rules(['nullable', 'integer']),
            ImportColumn::make('status')->rules(['nullable', 'max:255']),
            ImportColumn::make('description')->rules(['nullable']),
        ];
    }

    public function resolveRecord(): ?Mold
    {
        return Mold::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your mold import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

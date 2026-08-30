<?php

namespace App\Filament\Imports;

use App\Models\Tool;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ToolImporter extends Importer
{
    protected static ?string $model = Tool::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('category')->rules(['nullable', 'max:255']),
            ImportColumn::make('total_quantity')->numeric()->rules(['integer', 'min:0']),
            ImportColumn::make('available_quantity')->numeric()->rules(['integer', 'min:0']),
            ImportColumn::make('condition')->rules(['nullable', 'in:good,fair,poor,damaged']),
            ImportColumn::make('location')->rules(['nullable', 'max:255']),
            ImportColumn::make('description')->rules(['nullable']),
        ];
    }

    public function resolveRecord(): ?Tool
    {
        return Tool::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your tool import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

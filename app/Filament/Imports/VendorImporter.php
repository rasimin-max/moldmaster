<?php

namespace App\Filament\Imports;

use App\Models\Vendor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class VendorImporter extends Importer
{
    protected static ?string $model = Vendor::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('address')->rules(['nullable']),
            ImportColumn::make('pic_name')->rules(['nullable', 'max:255']),
            ImportColumn::make('phone')->rules(['nullable', 'max:255']),
            ImportColumn::make('email')->rules(['nullable', 'email', 'max:255']),
            ImportColumn::make('lead_time_days')->numeric()->rules(['nullable', 'integer']),
            ImportColumn::make('bank_name')->rules(['nullable', 'max:255']),
            ImportColumn::make('bank_account')->rules(['nullable', 'max:255']),
            ImportColumn::make('status')->rules(['nullable', 'max:255']),
            ImportColumn::make('notes')->rules(['nullable']),
        ];
    }

    public function resolveRecord(): ?Vendor
    {
        return Vendor::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your vendor import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

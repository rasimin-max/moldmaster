<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('employee_id'),
            ExportColumn::make('email'),
            ExportColumn::make('role')
                ->label('Role')
                ->state(function (User $record): string {
                    return $record->roles->pluck('name')
                        ->map(fn($role) => strtoupper(str_replace('_', ' ', $role)))
                        ->join(', ');
                }),
            ExportColumn::make('email_verified_at'),
            ExportColumn::make('phone'),
            ExportColumn::make('area'),
            ExportColumn::make('avatar'),
            ExportColumn::make('is_active'),
            ExportColumn::make('hourly_rate'),
            ExportColumn::make('last_login_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}

<?php

namespace App\Filament\Exports;

use App\Models\MachineProgram;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class MachineProgramExporter extends Exporter
{
    protected static ?string $model = MachineProgram::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('project.name')->label('Project'),
            ExportColumn::make('mold.name')->label('Mold Name'),
            ExportColumn::make('component.name')->label('Component Name'),
            ExportColumn::make('machine.name')->label('Machine'),
            ExportColumn::make('programmer')->label('Programmer'),
            ExportColumn::make('name')->label('Program Name'),
            ExportColumn::make('r_f')->label('R/F'),
            ExportColumn::make('b')->label('B'),
            ExportColumn::make('tool_no')->label('Tool No'),
            ExportColumn::make('tool_name')->label('Tool Name'),
            ExportColumn::make('tool_dia')->label('Tool Dia'),
            ExportColumn::make('tool_r')->label('Tool R'),
            ExportColumn::make('tool_length_total')->label('Length Total'),
            ExportColumn::make('tool_length_eff')->label('Length Eff'),
            ExportColumn::make('tool_num')->label('Tool Num'),
            ExportColumn::make('holder')->label('Holder'),
            ExportColumn::make('ps_thick')->label('PS Thick'),
            ExportColumn::make('rpm')->label('RPM'),
            ExportColumn::make('feed')->label('Feed'),
            ExportColumn::make('doc')->label('DoC'),
            ExportColumn::make('setting')->label('Setting'),
            ExportColumn::make('estimated_time')->label('Process Time Plan'),
            ExportColumn::make('actual_time')->label('Process Time Actual'),
            ExportColumn::make('barcode')->label('Barcode'),
            ExportColumn::make('description')->label('Description'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your machine program export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}

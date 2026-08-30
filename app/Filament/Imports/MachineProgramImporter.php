<?php

namespace App\Filament\Imports;

use App\Models\MachineProgram;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class MachineProgramImporter extends Importer
{
    protected static ?string $model = MachineProgram::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('project')
                ->relationship(resolveUsing: function (string $state) {
                    return \App\Models\Project::where('name', 'LIKE', trim($state))->first();
                }),
            ImportColumn::make('mold')
                ->relationship(resolveUsing: function (string $state) {
                    return \App\Models\Mold::where('name', 'LIKE', trim($state))->first();
                }),
            ImportColumn::make('component')
                ->relationship(resolveUsing: function (string $state) {
                    return \App\Models\Component::where('name', 'LIKE', trim($state))->first();
                }),
            ImportColumn::make('machine')
                ->relationship(resolveUsing: function (string $state) {
                    return \App\Models\Machine::where('name', 'LIKE', trim($state))->first();
                }),
            ImportColumn::make('programmer'),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->guess(['program name', 'name']),
            ImportColumn::make('r_f')
                ->guess(['r/f', 'r_f']),
            ImportColumn::make('b'),
            ImportColumn::make('tool_no')
                ->guess(['tool no', 'tool_no']),
            ImportColumn::make('tool_name')
                ->guess(['tool name', 'tool_name', 'name tool']),
            ImportColumn::make('tool_dia')
                ->guess(['tool dia', 'dia.', 'dia']),
            ImportColumn::make('tool_r')
                ->guess(['tool r', 'r.', 'r']),
            ImportColumn::make('tool_length_total')
                ->guess(['length total', 'total length']),
            ImportColumn::make('tool_length_eff')
                ->guess(['length eff', 'eff length', 'eff.']),
            ImportColumn::make('tool_num')
                ->guess(['tool num', 'num']),
            ImportColumn::make('holder'),
            ImportColumn::make('ps_thick')
                ->guess(['ps thick']),
            ImportColumn::make('rpm'),
            ImportColumn::make('feed'),
            ImportColumn::make('doc'),
            ImportColumn::make('setting'),
            ImportColumn::make('estimated_time')
                ->guess(['process time plan', 'plan', 'estimated time']),
            ImportColumn::make('actual_time')
                ->guess(['process time actual', 'actual', 'actual time']),
            ImportColumn::make('barcode'),
            ImportColumn::make('description'),
        ];
    }

    public function resolveRecord(): ?MachineProgram
    {
        if (isset($this->data['barcode']) && !empty($this->data['barcode'])) {
            return MachineProgram::firstOrNew([
                'barcode' => $this->data['barcode'],
            ]);
        }

        return new MachineProgram();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your machine program import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

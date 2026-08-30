<?php

namespace App\Filament\Widgets;

use App\Models\Machine;
use Filament\Widgets\ChartWidget;

class MachineOperationCostChart extends ChartWidget
{
    public static function canView(): bool
    {
        try {
            $activeWidgets = app(\App\Settings\GeneralSettings::class)->active_widgets ?? [];
            return empty($activeWidgets) || in_array(class_basename(static::class), $activeWidgets);
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected static ?string $heading = 'Cost Operasi Mesin';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $machines = Machine::with(['operationRecords' => function ($query) {
            $query->where('status', 'completed');
        }])->get();

        $labels = [];
        $costData = [];

        foreach ($machines as $machine) {
            $totalMinutes = $machine->operationRecords->sum('duration_minutes');
            
            if ($totalMinutes > 0) {
                $totalHours = $totalMinutes / 60;
                $hourlyRate = (float)($machine->hourly_rate ?? 0);
                $totalCost = $totalHours * $hourlyRate;

                $labels[] = $machine->name . ' (' . $machine->code . ')';
                $costData[] = round($totalCost, 2);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Cost (Rp)',
                    'data' => $costData,
                    'backgroundColor' => '#10b981', // green
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 2500,
                'easing' => 'easeOutCubic',
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Cost (Rp)',
                    ],
                ],
            ],
        ];
    }
}

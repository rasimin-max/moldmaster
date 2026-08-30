<?php

namespace App\Filament\Widgets;

use App\Models\Machine;
use Filament\Widgets\ChartWidget;

class MachineOperationTimeChart extends ChartWidget
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

    protected static ?string $heading = 'Waktu Operasi Mesin';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $machines = Machine::with(['operationRecords' => function ($query) {
            $query->where('status', 'completed');
        }])->get();

        $labels = [];
        $timeData = [];

        foreach ($machines as $machine) {
            $totalMinutes = $machine->operationRecords->sum('duration_minutes');
            
            if ($totalMinutes > 0) {
                $totalHours = $totalMinutes / 60;
                $labels[] = $machine->name . ' (' . $machine->code . ')';
                $timeData[] = round($totalHours, 2);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Waktu (Jam)',
                    'data' => $timeData,
                    'backgroundColor' => '#3b82f6', // blue
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
                        'text' => 'Waktu (Jam)',
                    ],
                ],
            ],
        ];
    }
}

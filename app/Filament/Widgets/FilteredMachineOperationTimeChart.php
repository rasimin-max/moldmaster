<?php

namespace App\Filament\Widgets;

use App\Models\Machine;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Pages\ResumeMachineOperationRecordPage;

class FilteredMachineOperationTimeChart extends ChartWidget
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

    use InteractsWithPageTable;

    protected static ?string $heading = 'Waktu Operasi Mesin';
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return ResumeMachineOperationRecordPage::class;
    }

    protected function getData(): array
    {
        $records = $this->getPageTableQuery()->where('status', 'completed')->get();
        
        $machineData = [];
        
        foreach ($records as $record) {
            $machineName = $record->machine->name ?? 'Unknown';
            if (!isset($machineData[$machineName])) {
                $machineData[$machineName] = 0;
            }
            $machineData[$machineName] += $record->duration_minutes;
        }

        $labels = [];
        $timeData = [];

        foreach ($machineData as $machineName => $totalMinutes) {
            if ($totalMinutes > 0) {
                $totalHours = $totalMinutes / 60;
                $labels[] = $machineName;
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

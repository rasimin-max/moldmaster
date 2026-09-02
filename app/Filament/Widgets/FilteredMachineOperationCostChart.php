<?php

namespace App\Filament\Widgets;

use App\Models\Machine;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Pages\ResumeMachineOperationRecordPage;

class FilteredMachineOperationCostChart extends ChartWidget
{
    public static function canView(): bool
    {
        try {
            $activeWidgets = app(\App\Settings\GeneralSettings::class)->getActiveWidgetsForUser();
            return empty($activeWidgets) || in_array(class_basename(static::class), $activeWidgets);
        } catch (\Throwable $e) {
            return true;
        }
    }

    use InteractsWithPageTable;

    protected static ?string $heading = 'Cost Operasi Mesin';
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
            $hourlyRate = (float)($record->machine->hourly_rate ?? 0);
            $totalCost = ($record->duration_minutes / 60) * $hourlyRate;
            $machineData[$machineName] += $totalCost;
        }

        $labels = [];
        $costData = [];

        foreach ($machineData as $machineName => $totalCost) {
            if ($totalCost > 0) {
                $labels[] = $machineName;
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

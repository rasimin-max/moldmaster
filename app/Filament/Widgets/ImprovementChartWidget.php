<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Pages\ImprovementReportPage;

class ImprovementChartWidget extends ChartWidget
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

    protected static ?string $heading = 'Grafik Cost Effect Berdasarkan Improvement';
    protected static bool $isDiscovered = false;
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';
    
    protected function getTablePage(): string
    {
        return ImprovementReportPage::class;
    }

    protected function getData(): array
    {
        // Get data filtered by the table
        $records = $this->getPageTableQuery()->get();
        
        $labels = [];
        $data = [];
        
        // Take top 10 highest cost effect to avoid clutter, or just show all filtered
        $sortedRecords = $records->sortByDesc('cost_effect')->take(15);
        
        foreach ($sortedRecords as $item) {
            $labels[] = str($item->title)->limit(20) . ' (' . $item->reporter_name . ')';
            $data[] = $item->cost_effect ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Estimasi Cost Effect (Rp)',
                    'data' => $data,
                    'backgroundColor' => '#10b981', // emerald
                    'borderColor' => '#059669',
                    'borderWidth' => 1,
                ],
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
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Rupiah (Rp)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Pages\ResumeSagyoNippoPage;

class SagyoNippoChartWidget extends ChartWidget
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

    protected static ?string $heading = 'Grafik Jam Kerja (Sagyo Nippo)';
    protected static bool $isDiscovered = false;
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';
    
    protected function getTablePage(): string
    {
        return ResumeSagyoNippoPage::class;
    }

    protected function getData(): array
    {
        // Ambil data yang sudah difilter oleh tabel
        $records = $this->getPageTableQuery()->with('sagyoNippo.user')->get();
        
        // Kelompokkan berdasarkan nama member
        $grouped = $records->groupBy(function($item) {
            return $item->sagyoNippo?->user?->name ?? 'Unknown';
        });
        
        $labels = [];
        $data = [];
        
        foreach ($grouped as $userName => $items) {
            $labels[] = $userName;
            $data[] = round($items->sum('hours'), 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Jam',
                    'data' => $data,
                    'backgroundColor' => '#f59e0b', // amber
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
                        'text' => 'Total Jam',
                    ],
                ],
            ],
        ];
    }
}

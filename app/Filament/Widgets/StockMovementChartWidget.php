<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Filament\Widgets\ChartWidget;

class StockMovementChartWidget extends ChartWidget
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

    protected static ?string $heading = 'Grafik Barang Keluar (7 Hari Terakhir)';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn($i) => now()->subDays($i));

        $outData = $days->map(fn($day) =>
            StockMovement::whereDate('created_at', $day->toDateString())
                ->where('type', 'out')
                ->where('status', 'approved')
                ->sum('quantity')
        )->toArray();

        $inData = $days->map(fn($day) =>
            StockMovement::whereDate('created_at', $day->toDateString())
                ->where('type', 'in')
                ->where('status', 'approved')
                ->sum('quantity')
        )->toArray();

        $labels = $days->map(fn($day) => $day->format('d M'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Barang Keluar',
                    'data' => $outData,
                    'borderColor' => '#f43f5e',
                    'backgroundColor' => 'rgba(244,63,94,0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Barang Masuk',
                    'data' => $inData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 2500,
                'easing' => 'easeOutCubic',
            ],
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Component;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class CommonPartSafetyStockChart extends ChartWidget
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

    protected static ?string $heading = 'Grafik Safety Stock - Common Parts';
    protected static ?int $sort = 5;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $components = Component::whereHas('category', function (Builder $query) {
            $query->where('name', 'COMMON PART');
        })
        ->orderBy('stock', 'asc')
        ->limit(15)
        ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Stok Saat Ini',
                    'data' => $components->pluck('stock')->toArray(),
                    'backgroundColor' => $components->map(function ($component) {
                        return $component->stock <= $component->stock_minimum ? '#ef4444' : '#10b981';
                    })->toArray(),
                ],
                [
                    'label' => 'Batas Minimum',
                    'data' => $components->pluck('stock_minimum')->toArray(),
                    'backgroundColor' => '#f59e0b',
                    'type' => 'line',
                    'borderColor' => '#f59e0b',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
            ],
            'labels' => $components->map(fn ($c) => $c->name . ' (' . $c->code . ')')->toArray(),
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
        ];
    }
}

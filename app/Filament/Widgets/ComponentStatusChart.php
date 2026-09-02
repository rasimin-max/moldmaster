<?php

namespace App\Filament\Widgets;

use App\Models\Component;
use Filament\Widgets\ChartWidget;

class ComponentStatusChart extends ChartWidget
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

    protected static ?string $heading = 'Status Komponen';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $records = Component::all();
        
        $total = 0;
        $complete = 0;
        $prosesDipakai = 0;
        $ready = 0;
        $onProgress = 0;
        $waiting = 0;
        
        foreach ($records as $record) {
            $total++;
            
            if ($record->mold_id && $record->required_qty) {
                $req = $record->required_qty;
                $used = $record->taken_qty;
                $currentStock = $record->stock;
                $totalReceived = $currentStock + $used;

                if ($used >= $req) {
                    $complete++;
                } elseif ($used > 0) {
                    $prosesDipakai++;
                } elseif ($totalReceived >= $req) {
                    $ready++;
                } elseif ($totalReceived > 0) {
                    $onProgress++;
                } else {
                    $waiting++;
                }
            } else {
                // Fallback for non-project components
                if ($record->status === 'ready') $ready++;
                elseif ($record->status === 'in_use') $prosesDipakai++;
                elseif ($record->status === 'pending_arrival') $waiting++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Komponen',
                    'data' => [$total, $waiting, $onProgress, $ready, $prosesDipakai, $complete],
                    'backgroundColor' => [
                        '#64748b', // Total: Slate
                        '#ef4444', // Waiting: Red
                        '#eab308', // On Progress: Yellow/Orange
                        '#22c55e', // Ready: Green
                        '#0ea5e9', // Proses Dipakai: Blue
                        '#8b5cf6', // Complete: Purple
                    ],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => ['Total Item', 'Waiting', 'On Progress', 'Ready', 'Proses Dipakai', 'Complete'],
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
                        'text' => 'Jumlah Komponen',
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}

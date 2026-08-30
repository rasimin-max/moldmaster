<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SagyoNippo;
use App\Models\User;
use Illuminate\Support\Carbon;

class SagyoNippoComplianceChart extends ChartWidget
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

    protected static ?string $heading = 'Kepatuhan Pengisian (Hari Ini)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $today = Carbon::today();
        
        $totalUsers = User::count();
        $usersFilled = SagyoNippo::whereDate('date', $today)->distinct('user_id')->count('user_id');
        $usersNotFilled = max(0, $totalUsers - $usersFilled);

        return [
            'datasets' => [
                [
                    'label' => 'Status',
                    'data' => [$usersFilled, $usersNotFilled],
                    'backgroundColor' => ['#10b981', '#ef4444'], // emerald-500 for success, red-500 for pending
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Sudah Mengisi (' . $usersFilled . ')', 'Belum (' . $usersNotFilled . ')'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 2500,
                'easing' => 'easeOutCubic',
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '70%',
        ];
    }
}

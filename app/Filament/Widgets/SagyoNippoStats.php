<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SagyoNippoItem;
use Illuminate\Support\Carbon;

class SagyoNippoStats extends BaseWidget
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

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        
        $totalHoursMonth = SagyoNippoItem::whereHas('sagyoNippo', function ($query) use ($startOfMonth) {
            $query->whereDate('date', '>=', $startOfMonth->toDateString());
        })->sum('hours');

        $totalItemsMonth = SagyoNippoItem::whereHas('sagyoNippo', function ($query) use ($startOfMonth) {
            $query->whereDate('date', '>=', $startOfMonth->toDateString());
        })->count();

        // Calculate Cost Month
        $items = SagyoNippoItem::with('jobCode')->whereHas('sagyoNippo', function ($query) use ($startOfMonth) {
            $query->whereDate('date', '>=', $startOfMonth->toDateString());
        })->get();
        
        $totalCostMonth = $items->sum(function ($item) {
            return $item->hours * ($item->jobCode?->rate ?? 0);
        });

        return [
            Stat::make('Total Jam (Bulan Ini)', number_format($totalHoursMonth, 1) . ' Jam')
                ->description('Total jam kerja tercatat bulan ini')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),
            Stat::make('Total Aktivitas', $totalItemsMonth . ' Data')
                ->description('Jumlah item sagyo nippo')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info'),
            Stat::make('Estimasi Cost (Bulan Ini)', 'Rp ' . number_format($totalCostMonth, 0, ',', '.'))
                ->description('Biaya berdasarkan jam x rate')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }
}

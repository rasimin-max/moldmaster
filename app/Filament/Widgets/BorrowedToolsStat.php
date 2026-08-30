<?php

namespace App\Filament\Widgets;

use App\Models\ToolLoan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BorrowedToolsStat extends BaseWidget
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

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $borrowed = ToolLoan::where('status', 'borrowed')->count();
        $overdue = ToolLoan::where('status', 'overdue')->count();

        return [
            Stat::make('Alat Sedang Dipinjam', $borrowed)
                ->description('Jumlah alat yang sedang berada di lapangan')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('primary'),
                
            Stat::make('Alat Terlambat (Overdue)', $overdue)
                ->description('Peminjaman melewati batas waktu')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Component;
use App\Models\Machine;
use App\Models\Maintenance;
use App\Models\StockMovement;
use App\Models\ToolLoan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
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
        $user = auth()->user();
        $stats = [];

        if ($user->hasRole(['super_admin', 'admin', 'viewer'])) {
            $totalComponents = Component::count();
            $readyComponents = Component::where('status', 'ready')->count();
            $lowStockComponents = Component::whereColumn('stock', '<=', 'stock_minimum')->count();
            $pendingArrivals = Component::where('status', 'pending_arrival')->count();

            $stats[] = Stat::make('Total Komponen', $totalComponents)
                ->description('Seluruh komponen terdaftar')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary');

            $stats[] = Stat::make('Stok Ready', $readyComponents)
                ->description('Komponen siap dipakai')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success');

            $stats[] = Stat::make('Stok Menipis', $lowStockComponents)
                ->description('Di bawah batas minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockComponents > 0 ? 'danger' : 'success');

            $stats[] = Stat::make('Belum Datang', $pendingArrivals)
                ->description('Sedang dalam pengiriman')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning');
        }

        if ($user->hasRole(['super_admin', 'admin', 'leader'])) {
            $pendingMovements = StockMovement::where('status', 'pending')->count();
            $pendingMaintenance = Maintenance::where('status', 'pending')->count();
            $pendingLoans = ToolLoan::where('status', 'pending')->count();
            $breakdownMachines = Machine::where('status', 'breakdown')->count();

            $stats[] = Stat::make('Pending Approval', $pendingMovements + $pendingMaintenance + $pendingLoans)
                ->description('Total perlu disetujui')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning');

            $stats[] = Stat::make('Mesin Breakdown', $breakdownMachines)
                ->description('Mesin tidak bisa beroperasi')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($breakdownMachines > 0 ? 'danger' : 'success');
        }

        if ($user->hasRole(['operator'])) {
            $myPending = StockMovement::where('requested_by', $user->id)->where('status', 'pending')->count();
            $myApproved = StockMovement::where('requested_by', $user->id)->where('status', 'approved')->count();
            $myLoans = ToolLoan::where('borrower_id', $user->id)->where('status', 'borrowed')->count();
            $availableComponents = Component::where('status', 'ready')->where('stock', '>', 0)->count();

            $stats[] = Stat::make('Komponen Tersedia', $availableComponents)
                ->description('Siap dipakai')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success');

            $stats[] = Stat::make('Request Saya (Pending)', $myPending)
                ->description('Menunggu persetujuan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning');

            $stats[] = Stat::make('Disetujui', $myApproved)
                ->description('Transaksi disetujui')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success');

            $stats[] = Stat::make('Alat Dipinjam', $myLoans)
                ->description('Sedang saya pinjam')
                ->descriptionIcon('heroicon-m-wrench')
                ->color('info');
        }

        return $stats;
    }
}

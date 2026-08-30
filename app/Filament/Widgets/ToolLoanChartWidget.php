<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Pages\ToolLoanReportPage;
use App\Models\ToolLoan;
use Illuminate\Support\Facades\DB;

class ToolLoanChartWidget extends ChartWidget
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

    protected static ?string $heading = 'Grafik Jumlah Peminjaman per Member';
    protected static bool $isDiscovered = false;
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getTablePage(): string
    {
        return ToolLoanReportPage::class;
    }

    protected function getData(): array
    {
        // Ambil data yang sudah difilter oleh tabel
        $records = $this->getPageTableQuery()->with('borrower')->get();
        
        $grouped = $records->groupBy(function($item) {
            return $item->borrower?->name ?? 'Unknown';
        });
            
        $labels = [];
        $data = [];
        
        // Buat array untuk di-sort
        $mapped = [];
        foreach ($grouped as $userName => $items) {
            $mapped[$userName] = $items->sum('quantity');
        }
        
        // Urutkan dari yang terbanyak dan ambil 10 teratas
        arsort($mapped);
        $mapped = array_slice($mapped, 0, 10);
        
        foreach ($mapped as $userName => $total) {
            $labels[] = $userName;
            $data[] = (int) $total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Alat Dipinjam',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6', // blue
                    'borderColor' => '#2563eb',
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
                        'text' => 'Jumlah Alat',
                    ],
                    'ticks' => [
                        'stepSize' => 1,
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

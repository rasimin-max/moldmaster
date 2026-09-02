<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ProjectPhase;
use Illuminate\Support\Carbon;

class FukaSimulationPage extends Page
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Detail Project';
    protected static ?string $navigationLabel = 'FUKA Simulation';
    protected static ?string $title = 'FUKA・負荷 Simulation';

    protected static string $view = 'filament.pages.fuka-simulation-page';

    protected function getViewData(): array
    {
        // Kapasitas hardcode sementara
        $capacities = [
            'design' => 2112, // TEIJI
            'machining' => 3020, // OT + Sat
            'assembly' => 7096, // Max
        ];

        // Ambil data untuk 6 bulan ke depan
        $startMonth = Carbon::now()->startOfMonth();
        
        $months = [];
        $labels = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $startMonth->copy()->addMonths($i);
            $months[] = $month;
            $labels[] = strtoupper($month->translatedFormat('M Y'));
        }

        // Ambil fase proyek
        $phases = ProjectPhase::with('project')->where('status', '!=', 'completed')->get();

        // Hitung beban per departemen per bulan
        $loads = [
            'design' => array_fill(0, 6, 0),
            'machining' => array_fill(0, 6, 0),
            'assembly' => array_fill(0, 6, 0),
            'trial' => array_fill(0, 6, 0),
            'qc' => array_fill(0, 6, 0),
        ];

        foreach ($phases as $phase) {
            $name = strtolower($phase->name);
            if (!isset($loads[$name])) continue;

            if (!$phase->start_date || !$phase->end_date || $phase->estimated_hours == 0) continue;

            $duration = max(1, $phase->start_date->diffInDays($phase->end_date));
            $hoursPerDay = $phase->estimated_hours / $duration;

            foreach ($months as $index => $month) {
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();

                // Cek overlap
                $overlapStart = max($monthStart, $phase->start_date);
                $overlapEnd = min($monthEnd, $phase->end_date);

                if ($overlapStart <= $overlapEnd) {
                    $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                    $loads[$name][$index] += ($hoursPerDay * $overlapDays);
                }
            }
        }
        
        // Round numbers
        foreach ($loads as $key => $values) {
            $loads[$key] = array_map(fn($v) => round($v), $values);
        }

        return [
            'labels' => $labels,
            'loads' => $loads,
            'capacities' => $capacities,
        ];
    }
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\SagyoNippo;
use Carbon\Carbon;

class SagyoNippoCalendarPage extends Page
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Kalender Sagyo Nippo';
    protected static ?string $title = 'Kalender Resume Sagyo Nippo';
    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.pages.sagyo-nippo-calendar-page';

    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year  = now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year  = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year  = $date->year;
    }

    public function getCalendarDataProperty(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $daysInMonth = $start->daysInMonth;

        // Semua user aktif dengan area
        $users = User::where('is_active', true)
            ->orderBy('area')
            ->orderBy('name')
            ->get();

        // Semua submission sagyo nippo bulan ini
        $nippos = SagyoNippo::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(function ($n) {
                return $n->user_id . '_' . $n->date;
            });

        // Bangun data: area => [ user => [ day => filled ] ]
        $areas = [];
        foreach ($users as $user) {
            $area = $user->area ?: 'Lainnya';
            if (!isset($areas[$area])) {
                $areas[$area] = [];
            }

            $days = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = Carbon::create($this->year, $this->month, $d);
                $key  = $user->id . '_' . $date->toDateString();
                $days[$d] = [
                    'date'     => $date,
                    'filled'   => isset($nippos[$key]),
                    'hours'    => isset($nippos[$key]) ? $nippos[$key]->sum('total_hours') : 0,
                    'is_weekend' => $date->isWeekend(),
                ];
            }

            $areas[$area][] = [
                'user' => $user,
                'days' => $days,
                'total_filled' => collect($days)->filter(fn($d) => $d['filled'] && !$d['is_weekend'])->count(),
                'total_working_days' => collect($days)->filter(fn($d) => !$d['is_weekend'])->count(),
            ];
        }

        return [
            'areas'        => $areas,
            'days_in_month' => $daysInMonth,
            'start'        => $start,
        ];
    }

    public function getMonthNameProperty(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $months[$this->month];
    }

    public function getDayNamesProperty(): array
    {
        $names = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $start = Carbon::create($this->year, $this->month, 1);
        $result = [];
        $daysInMonth = $start->daysInMonth;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($this->year, $this->month, $d);
            $result[$d] = $names[$date->dayOfWeek];
        }
        return $result;
    }
}

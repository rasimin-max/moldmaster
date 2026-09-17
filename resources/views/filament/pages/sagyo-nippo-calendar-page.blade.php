<x-filament-panels::page>
    {{-- Header navigasi bulan --}}
    <div class="flex items-center justify-between mb-4">
        <button wire:click="previousMonth"
            class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 shadow-sm transition">
            ← Bulan Sebelumnya
        </button>
        <div class="text-2xl font-bold text-gray-800 dark:text-white">
            {{ $this->monthName }} {{ $this->year }}
        </div>
        <button wire:click="nextMonth"
            class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 shadow-sm transition">
            Bulan Berikutnya →
        </button>
    </div>

    @php
        $cal       = $this->calendarData;
        $areas     = $cal['areas'];
        $daysCount = $cal['days_in_month'];
        $dayNames  = $this->dayNames;
        $no        = 1;
    @endphp

    <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-300 dark:border-gray-700">
        <table style="border-collapse: collapse; table-layout: fixed; min-width: 100%;">

            {{-- ===== HEADER BARIS TANGGAL ===== --}}
            <thead>
                <tr>
                    {{-- No --}}
                    <th style="
                        position: sticky; left: 0; z-index: 30;
                        background: #1e3a5f; color: #fff;
                        border: 1px solid #4a6a8a;
                        padding: 6px 4px; text-align: center;
                        min-width: 36px; width: 36px; font-size: 11px;">
                        No
                    </th>
                    {{-- Nama --}}
                    <th style="
                        position: sticky; left: 36px; z-index: 30;
                        background: #1e3a5f; color: #fff;
                        border: 1px solid #4a6a8a;
                        padding: 6px 8px; text-align: left;
                        min-width: 150px; width: 150px; font-size: 11px;">
                        Nama
                    </th>

                    {{-- Kolom tanggal --}}
                    @for($d = 1; $d <= $daysCount; $d++)
                        @php $isWeekend = in_array($dayNames[$d], ['Sab', 'Min']); @endphp
                        <th style="
                            border: 1px solid {{ $isWeekend ? '#888' : '#4a6a8a' }};
                            background: {{ $isWeekend ? '#6b7280' : '#1e3a5f' }};
                            color: #ffffff;
                            padding: 2px 1px; text-align: center;
                            min-width: 32px; width: 32px;">
                            <div style="font-size: 12px; font-weight: 700; line-height: 1.3;">{{ $d }}</div>
                            <div style="font-size: 9px; color: #d1d5db; line-height: 1;">{{ $dayNames[$d] }}</div>
                        </th>
                    @endfor

                    {{-- Total & % --}}
                    <th style="background: #1e3a5f; color: #fff; border: 1px solid #4a6a8a; padding: 6px 4px; text-align: center; min-width: 55px; font-size: 11px;">Total</th>
                    <th style="background: #1e3a5f; color: #fff; border: 1px solid #4a6a8a; padding: 6px 4px; text-align: center; min-width: 45px; font-size: 11px;">%</th>
                </tr>
            </thead>

            <tbody>
                @forelse($areas as $areaName => $members)

                    {{-- ===== HEADER AREA ===== --}}
                    <tr>
                        <td colspan="{{ $daysCount + 4 }}" style="
                            background: #f59e0b; color: #1a1a1a;
                            font-weight: 800; font-size: 11px;
                            padding: 5px 10px;
                            border: 1px solid #d97706;
                            position: sticky; left: 0;
                            letter-spacing: 0.5px;">
                            {{ strtoupper($areaName) }}
                        </td>
                    </tr>

                    @foreach($members as $member)
                        @php
                            $user    = $member['user'];
                            $days    = $member['days'];
                            $filled  = $member['total_filled'];
                            $working = $member['total_working_days'];
                            $pct     = $working > 0 ? round(($filled / $working) * 100) : 0;

                            $totalColor = $pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626');
                        @endphp

                        <tr style="background: #ffffff;" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='#ffffff'">

                            {{-- No --}}
                            <td style="
                                position: sticky; left: 0; z-index: 10;
                                background: #f8fafc;
                                border: 1px solid #e2e8f0;
                                text-align: center; font-size: 11px;
                                color: #64748b; font-weight: 600;
                                width: 36px; padding: 4px 2px;">
                                {{ $no++ }}
                            </td>

                            {{-- Nama --}}
                            <td style="
                                position: sticky; left: 36px; z-index: 10;
                                background: #f8fafc;
                                border: 1px solid #e2e8f0;
                                padding: 4px 8px; font-size: 11px;
                                font-weight: 600; color: #1e293b;
                                width: 150px; white-space: nowrap;
                                overflow: hidden; text-overflow: ellipsis;">
                                {{ $user->name }}
                            </td>

                            {{-- ===== SEL TANGGAL ===== --}}
                            @foreach($days as $day => $info)
                                @php
                                    $isWeekend = $info['is_weekend'];
                                    $isFilled  = $info['filled'];

                                    if ($isFilled) {
                                        $bgColor    = '#dcfce7'; // hijau muda
                                        $borderColor = '#86efac';
                                    } elseif ($isWeekend) {
                                        $bgColor    = '#e5e7eb'; // abu-abu
                                        $borderColor = '#d1d5db';
                                    } else {
                                        $bgColor    = '#ffffff';
                                        $borderColor = '#e2e8f0';
                                    }
                                @endphp

                                <td style="
                                    border: 1px solid {{ $borderColor }};
                                    background: {{ $bgColor }};
                                    text-align: center; padding: 2px 1px;
                                    width: 32px; height: 34px; vertical-align: middle;">

                                    @if($isFilled)
                                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%;">
                                            {{-- Centang hijau --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width:14px; height:14px; color:#16a34a; stroke:#16a34a;" fill="none" viewBox="0 0 24 24" stroke-width="3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            @if($info['hours'] > 0)
                                                <span style="font-size:8px; color:#15803d; font-weight:700; line-height:1;">
                                                    {{ number_format($info['hours'], 0) }}j
                                                </span>
                                            @endif
                                        </div>
                                    @elseif($isWeekend)
                                        <span style="color:#9ca3af; font-size:11px;">—</span>
                                    @else
                                        <span style="color:#d1d5db; font-size:10px;">·</span>
                                    @endif
                                </td>
                            @endforeach

                            {{-- Total --}}
                            <td style="
                                border: 1px solid #e2e8f0;
                                background: {{ $pct >= 80 ? '#dcfce7' : ($pct >= 50 ? '#fef9c3' : '#fee2e2') }};
                                text-align: center; font-size: 11px;
                                font-weight: 700; color: {{ $totalColor }};
                                padding: 4px 2px;">
                                {{ $filled }}/{{ $working }}
                            </td>

                            {{-- Persen --}}
                            <td style="
                                border: 1px solid #e2e8f0;
                                background: {{ $pct >= 80 ? '#dcfce7' : ($pct >= 50 ? '#fef9c3' : '#fee2e2') }};
                                text-align: center; font-size: 11px;
                                font-weight: 700; color: {{ $totalColor }};
                                padding: 4px 2px;">
                                {{ $pct }}%
                            </td>
                        </tr>
                    @endforeach

                @empty
                    <tr>
                        <td colspan="{{ $daysCount + 4 }}" style="text-align:center; padding:30px; color:#9ca3af; font-size:13px;">
                            Tidak ada data karyawan aktif.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    <div style="display:flex; align-items:center; gap:24px; margin-top:12px; font-size:12px; color:#64748b; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:6px;">
            <div style="width:20px; height:20px; background:#dcfce7; border:1px solid #86efac; border-radius:4px; display:flex; align-items:center; justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span>Sudah isi Sagyo Nippo</span>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <div style="width:20px; height:20px; background:#e5e7eb; border:1px solid #d1d5db; border-radius:4px;"></div>
            <span>Sabtu / Minggu</span>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <span style="color:#16a34a; font-weight:700;">≥80%</span><span>Baik</span>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <span style="color:#d97706; font-weight:700;">50–79%</span><span>Cukup</span>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <span style="color:#dc2626; font-weight:700;">&lt;50%</span><span>Kurang</span>
        </div>
    </div>
</x-filament-panels::page>

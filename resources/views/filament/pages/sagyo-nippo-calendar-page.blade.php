<x-filament-panels::page>
    {{-- Header navigasi bulan --}}
    <div class="flex items-center justify-between mb-4">
        <button wire:click="previousMonth"
            class="fi-btn inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
            ← Bulan Sebelumnya
        </button>

        <div class="text-xl font-bold text-gray-800 dark:text-white">
            {{ $this->monthName }} {{ $this->year }}
        </div>

        <button wire:click="nextMonth"
            class="fi-btn inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
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

    <div class="overflow-x-auto rounded-xl shadow border border-gray-200 dark:border-gray-700">
        <table class="min-w-full text-xs border-collapse" style="table-layout: fixed;">
            {{-- Header baris hari --}}
            <thead>
                <tr>
                    <th class="sticky left-0 z-20 bg-gray-800 text-white border border-gray-600 px-2 py-2 text-center" style="min-width:40px; width:40px;">No</th>
                    <th class="sticky left-10 z-20 bg-gray-800 text-white border border-gray-600 px-3 py-2 text-left" style="min-width:160px; width:160px;">Nama</th>
                    @for($d = 1; $d <= $daysCount; $d++)
                        @php
                            $isWeekend = in_array($dayNames[$d], ['Sab', 'Min']);
                        @endphp
                        <th class="border border-gray-600 py-1 text-center font-bold {{ $isWeekend ? 'bg-yellow-600 text-white' : 'bg-gray-800 text-white' }}"
                            style="min-width:34px; width:34px;">
                            <div>{{ $d }}</div>
                            <div class="text-gray-300 font-normal text-[10px]">{{ $dayNames[$d] }}</div>
                        </th>
                    @endfor
                    <th class="bg-gray-800 text-white border border-gray-600 px-2 py-2 text-center" style="min-width:60px;">Total</th>
                    <th class="bg-gray-800 text-white border border-gray-600 px-2 py-2 text-center" style="min-width:50px;">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($areas as $areaName => $members)
                    {{-- Header area/section --}}
                    <tr>
                        <td colspan="{{ $daysCount + 4 }}"
                            class="bg-yellow-400 text-gray-900 font-bold text-xs px-3 py-1.5 border border-yellow-500 sticky left-0">
                            {{ strtoupper($areaName) }}
                        </td>
                    </tr>

                    @foreach($members as $member)
                        @php
                            $user  = $member['user'];
                            $days  = $member['days'];
                            $filled = $member['total_filled'];
                            $working = $member['total_working_days'];
                            $pct = $working > 0 ? round(($filled / $working) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            {{-- No --}}
                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-center font-medium text-gray-500"
                                style="width:40px;">
                                {{ $no++ }}
                            </td>

                            {{-- Nama --}}
                            <td class="sticky left-10 z-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-2 py-1 font-semibold text-gray-800 dark:text-gray-200"
                                style="width:160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $user->name }}
                            </td>

                            {{-- Hari --}}
                            @foreach($days as $day => $info)
                                @php
                                    $isWeekend = $info['is_weekend'];
                                    $isFilled  = $info['filled'];
                                @endphp
                                <td class="border border-gray-200 dark:border-gray-700 text-center p-0.5
                                    {{ $isWeekend ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}"
                                    style="width:34px; height:32px;">
                                    @if($isFilled)
                                        <div class="flex flex-col items-center justify-center h-full">
                                            {{-- Centang hijau --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            @if($info['hours'] > 0)
                                                <span class="text-[9px] text-green-600 font-medium leading-none">{{ number_format($info['hours'], 0) }}j</span>
                                            @endif
                                        </div>
                                    @elseif($isWeekend)
                                        <span class="text-yellow-400 text-xs">—</span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600 text-xs">·</span>
                                    @endif
                                </td>
                            @endforeach

                            {{-- Total hari isi --}}
                            <td class="border border-gray-200 dark:border-gray-700 text-center font-bold
                                {{ $pct >= 80 ? 'text-green-600 bg-green-50 dark:bg-green-900/20' : ($pct >= 50 ? 'text-yellow-600 bg-yellow-50' : 'text-red-500 bg-red-50 dark:bg-red-900/20') }}">
                                {{ $filled }}/{{ $working }}
                            </td>

                            {{-- Persentase --}}
                            <td class="border border-gray-200 dark:border-gray-700 text-center font-bold text-xs
                                {{ $pct >= 80 ? 'text-green-600' : ($pct >= 50 ? 'text-yellow-600' : 'text-red-500') }}">
                                {{ $pct }}%
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ $daysCount + 4 }}" class="text-center py-8 text-gray-400">
                            Tidak ada data karyawan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-6 mt-4 text-xs text-gray-600 dark:text-gray-400">
        <div class="flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>Sudah mengisi Sagyo Nippo</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-4 bg-yellow-400 rounded-sm"></div>
            <span>Sabtu / Minggu</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="text-green-600 font-bold">≥80%</span>
            <span>Baik</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="text-yellow-600 font-bold">50-79%</span>
            <span>Cukup</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="text-red-500 font-bold">&lt;50%</span>
            <span>Kurang</span>
        </div>
    </div>
</x-filament-panels::page>

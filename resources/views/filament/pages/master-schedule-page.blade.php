<x-filament-panels::page>
    <div x-data="{
            open: false,
            x: 0,
            y: 0,
            data: {},
            showTooltip(event, phaseData) {
                this.data = phaseData;
                this.x = event.clientX;
                this.y = event.clientY;
                this.open = true;
            },
            activeTab: 'global'
        }" class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-white/10 p-6 relative">
        
        <!-- Tabs Navigation -->
        <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
            <button @click="activeTab = 'global'" 
                    :class="activeTab === 'global' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'" 
                    class="px-6 py-3 border-b-2 font-bold text-sm transition-colors">
                Global Project Schedule
            </button>
            <button @click="activeTab = 'stasion'" 
                    :class="activeTab === 'stasion' ? 'border-green-600 text-green-600 dark:text-green-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'" 
                    class="px-6 py-3 border-b-2 font-bold text-sm transition-colors">
                Stasion Schedule
            </button>
        </div>

        <!-- Tooltip Overlay -->
        <div x-show="open" 
             x-transition
             class="fixed z-50 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 w-72 pointer-events-none transform -translate-x-1/2 -translate-y-[calc(100%+15px)]"
             :style="`top: ${y}px; left: ${x}px;`"
             style="display: none;">
             
             <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                 <div class="bg-primary-600 text-white text-xs font-bold px-2 py-1 rounded" x-text="data.projectCode"></div>
                 <div class="flex-1 min-w-0">
                     <div class="font-bold text-sm text-gray-900 dark:text-white truncate" x-text="data.projectName"></div>
                 </div>
             </div>
             
             <div class="space-y-1.5 text-xs">
                 <div class="flex justify-between">
                     <span class="text-gray-500 font-medium">PHASE HOVERED</span>
                     <span class="font-bold text-blue-600" x-text="data.hoveredPhase"></span>
                 </div>
                 <div class="flex justify-between">
                     <span class="text-gray-500 font-medium">MULAI</span>
                     <span class="font-medium text-gray-900 dark:text-gray-100" x-text="data.startDate"></span>
                 </div>
                 <div class="flex justify-between">
                     <span class="text-gray-500 font-medium">SELESAI</span>
                     <span class="font-medium text-gray-900 dark:text-gray-100" x-text="data.endDate"></span>
                 </div>
                 <div class="flex justify-between">
                     <span class="text-gray-500 font-medium">DURASI</span>
                     <span class="font-medium text-gray-900 dark:text-gray-100" x-text="data.duration"></span>
                 </div>
                 <div class="flex justify-between items-center mt-2">
                     <span class="text-gray-500 font-medium">PROGRESS FASE</span>
                     <span class="font-bold text-gray-900 dark:text-gray-100" x-text="data.progress"></span>
                 </div>
                 <!-- Progress Bar -->
                 <div class="w-full bg-gray-100 rounded-full h-1.5 dark:bg-gray-700 mt-1 mb-2">
                     <div class="bg-green-500 h-1.5 rounded-full" :style="`width: ${data.progress}`"></div>
                 </div>
                 <div class="flex justify-between">
                     <span class="text-gray-500 font-medium">STATUS</span>
                     <span class="font-bold px-2 py-0.5 rounded" 
                           :class="data.status === 'completed' ? 'text-green-700 bg-green-100' : (data.status === 'active' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 bg-gray-100')"
                           x-text="data.status.toUpperCase()"></span>
                 </div>
             </div>

             <!-- All Phases List -->
             <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                 <div class="text-xs font-semibold text-gray-400 mb-2 tracking-wider">ALL PHASES</div>
                 <div class="space-y-1">
                     <template x-for="p in data.allPhases">
                         <div class="flex justify-between text-[11px]">
                             <span class="font-medium" :class="p.name === data.hoveredPhase ? 'text-primary-600 font-bold' : 'text-gray-600 dark:text-gray-400'" x-text="p.name"></span>
                             <span class="text-gray-500" x-text="p.dates"></span>
                         </div>
                     </template>
                 </div>
             </div>
        </div>

        <!-- TAB CONTENT: GLOBAL PROJECT SCHEDULE -->
        <div x-show="activeTab === 'global'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">Global Project Schedule</h2>
                    <div class="flex bg-gray-100 p-1 rounded-md">
                        <button wire:click="$set('projectViewMode', 'daily')"
                                class="px-3 py-1 text-xs font-semibold rounded {{ $projectViewMode === 'daily' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                            DAILY
                        </button>
                        <button wire:click="$set('projectViewMode', 'weekly')"
                                class="px-3 py-1 text-xs font-semibold rounded {{ $projectViewMode === 'weekly' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                            WEEKLY
                        </button>
                        <button wire:click="$set('projectViewMode', 'monthly')"
                                class="px-3 py-1 text-xs font-semibold rounded {{ $projectViewMode === 'monthly' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                            MONTHLY
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-funnel class="w-5 h-5 text-gray-500" />
                        <span class="font-bold text-sm text-gray-700 tracking-wider">FILTER FASE:</span>
                        <select wire:model.live="filterPhase" class="border border-gray-200 rounded-md px-3 py-1.5 text-sm font-medium focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 cursor-pointer min-w-[150px]">
                            <option value="All">Semua Fase</option>
                            <option value="design">Design</option>
                            <option value="machining">Machining</option>
                            <option value="assembly">Assembly</option>
                            <option value="trial">Trial</option>
                            <option value="qc">QC</option>
                        </select>
                    </div>

                    <!-- MENU EDIT DROPDOWN (Global) -->
                    <div class="relative" x-data="{ menuOpenGlobal: false }">
                        <button @click="menuOpenGlobal = !menuOpenGlobal" @click.away="menuOpenGlobal = false" class="px-4 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold text-xs rounded border border-gray-300 dark:border-gray-600 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 transition-colors">
                            <x-heroicon-o-bars-3 class="w-4 h-4" />
                            MENU EDIT
                            <x-heroicon-o-chevron-down class="w-3 h-3" />
                        </button>
                        
                        <div x-show="menuOpenGlobal" 
                             x-transition.opacity.duration.200ms
                             style="display: none;"
                             class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                            
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi Jadwal</span>
                            </div>
                            
                            <button wire:click="mountAction('createSchedule')"
                                    @click="menuOpenGlobal = false"
                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-700 dark:text-gray-200 hover:text-primary-600 transition-colors flex items-center gap-2">
                                <x-heroicon-o-plus-circle class="w-5 h-5 text-primary-500" />
                                Tambah Jadwal (Wizard)
                            </button>
                            
                            <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between bg-green-50/50 dark:bg-green-900/10 cursor-help" title="Gunakan icon pensil di nama proyek untuk edit phase">
                                <span class="flex items-center gap-2 font-medium">
                                    <x-heroicon-o-pencil-square class="w-5 h-5 text-green-500" />
                                    Edit Phase Aktif
                                </span>
                                <div class="w-7 h-4 bg-green-500 rounded-full relative">
                                    <div class="absolute right-0.5 top-0.5 w-3 h-3 bg-white rounded-full shadow"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm pb-6">
                <div class="min-w-[1000px] relative">
                    <!-- Header -->
                    <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <div class="font-bold text-gray-700 dark:text-gray-300 text-xs p-3 flex items-center border-r border-gray-200 dark:border-gray-700" style="width: 25%;">
                            PROYEK
                        </div>
                        <div class="flex relative" style="width: 75%;">
                            @foreach($projectPeriods as $index => $period)
                                <div class="flex-1 text-center border-r border-gray-200 dark:border-gray-700 py-2 flex flex-col justify-center
                                            {{ $projectViewMode === 'daily' && $index === 0 ? 'text-red-500 font-bold' : 'text-gray-600' }}">
                                    @if($projectViewMode === 'daily' && $index === 0)
                                        <div class="text-[9px] uppercase tracking-wider mb-0.5">{{ $period['sub_label'] }}</div>
                                    @endif
                                    <div class="text-xs font-bold">{{ $period['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Projects -->
                    <div class="space-y-0">
                        @forelse($projects as $project)
                            @php
                                $allPhasesJson = $project->phases->map(function($p) {
                                    return [
                                        'name' => strtoupper(substr($p->name, 0, 4)),
                                        'dates' => ($p->start_date ? $p->start_date->format('j M Y') : '-') . ' - ' . ($p->end_date ? $p->end_date->format('j M Y') : '-')
                                    ];
                                })->values()->toJson();
                            @endphp
                            <div wire:key="project-row-{{ $project->id }}" class="flex relative group border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                <!-- Label Proyek -->
                                <div class="p-3 border-r border-gray-200 dark:border-gray-700 flex-shrink-0 bg-white group-hover:bg-gray-50 z-10 flex flex-col justify-center" style="width: 25%;">
                                    <div class="flex justify-between items-start">
                                        <div class="flex flex-col overflow-hidden">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-1.5 h-1.5 rounded-full {{ $project->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                                <p class="font-bold text-xs text-gray-900 truncate" title="{{ $project->code }}">{{ $project->code }}</p>
                                            </div>
                                            <p class="text-[10px] text-gray-500 truncate mt-0.5 ml-3" title="{{ $project->name }}">{{ $project->name }}</p>
                                        </div>
                                        <button wire:click="mountAction('editSchedule', { project: {{ $project->id }} })" 
                                                class="text-primary-600 hover:text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 transition px-2 py-0.5 rounded flex items-center gap-1 shadow-sm mt-0.5"
                                                title="Edit Jadwal / Setting Waktu">
                                            <x-heroicon-o-pencil-square class="w-3 h-3" />
                                            <span class="text-[9px] font-bold tracking-wider">EDIT</span>
                                        </button>
                                    </div>
                                    @php
                                        $latestProgress = $project->phases->last()?->progress ?? 0;
                                    @endphp
                                    <div class="ml-3 mt-1 flex items-center gap-2">
                                        <div class="text-[10px] text-gray-500 font-medium">Progress:</div>
                                        <div class="w-16 bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $latestProgress }}%"></div>
                                        </div>
                                        <div class="text-[10px] text-gray-700 font-bold">
                                            {{ $latestProgress }}%
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline Area -->
                                <div class="relative h-10 bg-gray-50 dark:bg-gray-800 rounded-md overflow-hidden border border-gray-100 dark:border-gray-700" style="width: 75%;">
                                    <!-- Grid background -->
                                    <div class="absolute inset-0 flex pointer-events-none">
                                        @foreach($projectPeriods as $period)
                                            <div class="flex-1 border-l border-white/50 dark:border-gray-700/50"></div>
                                        @endforeach
                                    </div>

                                    <!-- Phases -->
                                    @foreach($project->phases as $phase)
                                        @php
                                            if ($filterPhase !== 'All' && strtolower($phase->name) !== $filterPhase) continue;

                                            if (!$phase->start_date || !$phase->end_date) continue;
                                            
                                            $pStart = \Carbon\Carbon::parse($phase->start_date)->startOfDay();
                                            $pEnd = \Carbon\Carbon::parse($phase->end_date)->endOfDay();
                                            
                                            // Jika fase sama sekali di luar rentang, skip
                                            if ($pEnd->lessThan($projectStartDate)) continue;
                                            
                                            $diffFromStart = $projectStartDate->diffInMinutes($pStart, false);
                                            $duration = $pStart->diffInMinutes($pEnd);
                                            
                                            $leftPercentage = ($diffFromStart / $projectTotalDuration) * 100;
                                            $widthPercentage = ($duration / $projectTotalDuration) * 100;
                                            
                                            $leftPercentage = max(0, $leftPercentage);
                                            $widthPercentage = min(100 - $leftPercentage, $widthPercentage);
                                            
                                            if ($widthPercentage <= 0) continue;

                                            $bgColor = match(strtolower($phase->name)) {
                                                'design' => '#60a5fa', // blue-400
                                                'machining' => '#fb923c', // orange-400
                                                'assembly' => '#4ade80', // green-400
                                                'trial' => '#9ca3af', // gray-400
                                                'qc' => '#facc15', // yellow-400
                                                default => '#8b5cf6', // purple-500
                                            };
                                            
                                            $hoverData = json_encode([
                                                'projectCode' => $project->code,
                                                'projectName' => $project->name,
                                                'hoveredPhase' => strtoupper(substr($phase->name, 0, 4)),
                                                'startDate' => $pStart->format('j M Y'),
                                                'endDate' => $pEnd->format('j M Y'),
                                                'duration' => round($pStart->floatDiffInDays($pEnd)) . ' hari',
                                                'progress' => $phase->progress . '%',
                                                'status' => $phase->status,
                                                'allPhases' => json_decode($allPhasesJson)
                                            ]);
                                        @endphp
                                        
                                        <div wire:key="phase-bar-{{ $phase->id }}"
                                             class="absolute top-0 bottom-0 flex flex-col items-center justify-center text-[9px] text-white font-bold shadow-sm cursor-move hover:opacity-90 overflow-hidden border border-black/10 select-none"
                                             x-data="{ 
                                                startDrag(e) {
                                                    let startX = e.clientX;
                                                    let el = this.$el;
                                                    let currentTranslateX = 0;
                                                    
                                                    // Sembunyikan tooltip dari parent Alpine komponen
                                                    this.open = false; 
                                                    window.isDraggingPhase = true;
                                                    
                                                    let containerWidth = el.parentElement.offsetWidth;
                                                    let projectTotalDuration = {{ $projectTotalDuration }};
                                                    
                                                    document.body.classList.add('cursor-move', 'select-none');
                                                    el.style.transition = 'none';
                                                    el.style.zIndex = '50';
                                                    el.style.opacity = '0.8';
                                                    
                                                    let doDrag = (ev) => {
                                                        // Mengikuti kursor mouse secara presisi 1:1 tanpa snapping
                                                        currentTranslateX = ev.clientX - startX;
                                                        el.style.transform = `translateX(${currentTranslateX}px)`;
                                                    };
                                                    
                                                    let stopDrag = (ev) => {
                                                        window.removeEventListener('mousemove', doDrag);
                                                        window.removeEventListener('mouseup', stopDrag);
                                                        
                                                        window.isDraggingPhase = false;
                                                        document.body.classList.remove('cursor-move', 'select-none');
                                                        el.style.zIndex = '';
                                                        el.style.opacity = '';
                                                        
                                                        let deltaPercentage = (currentTranslateX / containerWidth) * 100;
                                                        let minutesShifted = (deltaPercentage / 100) * projectTotalDuration;
                                                        
                                                        // Hitung berapa HARI penuh pergeserannya (pembulatan terdekat)
                                                        let daysShifted = Math.round(minutesShifted / 1440);
                                                        
                                                        // Jika user menggeser lumayan jauh (> 5px) tapi secara matematika masih membulat ke 0 hari,
                                                        // paksa agar bergeser minimal 1 hari sesuai arah tarikan, agar tidak melenting balik.
                                                        if (daysShifted === 0 && Math.abs(currentTranslateX) > 5) {
                                                            daysShifted = currentTranslateX > 0 ? 1 : -1;
                                                        }
                                                        
                                                        if (Math.abs(daysShifted) > 0) { 
                                                            // Snap posisi visualnya ke hitungan hari yang bulat sambil nunggu Livewire refresh
                                                            let dayInPixels = (1440 / projectTotalDuration) * containerWidth;
                                                            el.style.transform = `translateX(${daysShifted * dayInPixels}px)`;
                                                            
                                                            this.$wire.updatePhaseDates({{ $phase->id }}, daysShifted * 1440);
                                                        } else {
                                                            // Kalau gesernya terlalu sedikit (< 5px), kembalikan ke awal
                                                            el.style.transition = '';
                                                            el.style.transform = '';
                                                        }
                                                    };
                                                    
                                                    window.addEventListener('mousemove', doDrag);
                                                    window.addEventListener('mouseup', stopDrag);
                                                }
                                             }"
                                             style="background-color: {{ $bgColor }}; left: {{ $leftPercentage }}%; width: {{ $widthPercentage }}%;"
                                             @mousedown.stop="startDrag"
                                             @mouseenter="if (!window.isDraggingPhase) showTooltip($event, {{ $hoverData }})"
                                             @mouseleave="if (!window.isDraggingPhase) open = false">
                                            <span class="truncate z-10">{{ strtoupper(substr($phase->name, 0, 4)) }}</span>
                                            
                                            @if($phase->progress > 0)
                                                <div class="absolute bottom-0 left-0 h-1 bg-black/20" style="width: {{ $phase->progress }}%"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-sm text-gray-500 py-4">Belum ada proyek aktif.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB CONTENT: STASION SCHEDULE -->
        <div x-show="activeTab === 'stasion'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
            @php
                $stasionDays = collect(range(0, $stasionDaysCount - 1))->map(fn($i) => $stasionStartDate->copy()->addDays($i));
            @endphp

            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-funnel class="w-5 h-5 text-gray-500" />
                        <span class="font-bold text-sm text-gray-700 tracking-wider">FILTER:</span>
                        <select wire:model.live="filterMachineType" class="border border-gray-200 rounded-md px-3 py-1.5 text-sm font-medium focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 cursor-pointer min-w-[150px]">
                            <option value="All">Semua Mesin</option>
                            @foreach($machineTypes as $type)
                                <option value="{{ $type }}">{{ strtoupper($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-md border border-gray-200 dark:border-gray-700">
                        <button wire:click="$set('stasionViewMode', 'day')" class="px-4 py-1.5 text-xs font-bold rounded transition-colors {{ $stasionViewMode === 'day' ? 'bg-white dark:bg-gray-700 shadow-sm text-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">Day</button>
                        <button wire:click="$set('stasionViewMode', 'week')" class="px-4 py-1.5 text-xs font-bold rounded transition-colors {{ $stasionViewMode === 'week' ? 'bg-white dark:bg-gray-700 shadow-sm text-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">Week</button>
                        <button wire:click="$set('stasionViewMode', 'month')" class="px-4 py-1.5 text-xs font-bold rounded transition-colors {{ $stasionViewMode === 'month' ? 'bg-white dark:bg-gray-700 shadow-sm text-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">Month</button>
                    </div>
                    
                    <div class="flex items-center gap-1 ml-2">
                        <button wire:click="previousStasionDate" class="p-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded border border-gray-200 dark:border-gray-700 transition" title="Mundur">
                            <x-heroicon-o-chevron-left class="w-4 h-4" />
                        </button>
                        <button wire:click="todayStasionDate" class="px-3 py-1.5 text-xs font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded border border-gray-200 dark:border-gray-700 transition">
                            Hari Ini
                        </button>
                        <button wire:click="nextStasionDate" class="p-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded border border-gray-200 dark:border-gray-700 transition" title="Maju">
                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                
                <div class="relative" x-data="{ menuOpen: false }">
                    <button @click="menuOpen = !menuOpen" @click.away="menuOpen = false" class="px-4 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold text-xs rounded border border-gray-300 dark:border-gray-600 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 transition-colors">
                        <x-heroicon-o-bars-3 class="w-4 h-4" />
                        MENU EDIT
                        <x-heroicon-o-chevron-down class="w-3 h-3" />
                    </button>
                    
                    <div x-show="menuOpen" 
                         x-transition.opacity.duration.200ms
                         style="display: none;"
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                        
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi Jadwal</span>
                        </div>
                        
                        <button wire:click="mountAction('createSchedule')"
                                @click="menuOpen = false"
                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-700 dark:text-gray-200 hover:text-primary-600 transition-colors flex items-center gap-2">
                            <x-heroicon-o-plus-circle class="w-5 h-5 text-primary-500" />
                            Tambah Jadwal (Wizard)
                        </button>
                        
                        <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between bg-green-50/50 dark:bg-green-900/10 cursor-help" title="Mode Drag & Drop jadwal sudah aktif secara default">
                            <span class="flex items-center gap-2 font-medium">
                                <x-heroicon-o-arrows-pointing-out class="w-5 h-5 text-green-500" />
                                Drag & Drop Aktif
                            </span>
                            <div class="w-7 h-4 bg-green-500 rounded-full relative">
                                <div class="absolute right-0.5 top-0.5 w-3 h-3 bg-white rounded-full shadow"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 relative">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left" style="table-layout: fixed; min-width: 1000px; border: 1px solid #9ca3af;">
                        <thead>
                            <tr>
                                <th class="bg-white" style="width: 200px; border: 1px solid #9ca3af; padding: 8px;">
                                    <div class="font-bold text-gray-800 uppercase" style="font-size: 11px;">STATION</div>
                                </th>
                                @foreach($stasionDays as $day)
                                    <th colspan="2" class="text-center bg-white" style="border: 1px solid #9ca3af; padding: 4px;">
                                        <div class="font-bold text-gray-800 uppercase" style="font-size: 11px;">{{ $day->format('d-M-y') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                <th class="bg-white text-center" style="border: 1px solid #9ca3af; padding: 8px;">
                                    <div class="font-bold text-gray-800 uppercase" style="font-size: 10px;">SHIFT</div>
                                </th>
                                @foreach($stasionDays as $day)
                                    <th class="bg-white text-center" style="border: 1px solid #9ca3af; padding: 2px;">
                                        <div class="text-gray-700 font-bold uppercase" style="font-size: 9px;">PAGI</div>
                                    </th>
                                    <th class="text-center" style="background-color: #d1d5db; border: 1px solid #9ca3af; padding: 2px;">
                                        <div class="text-gray-700 font-bold uppercase" style="font-size: 9px;">MALAM</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($machines as $machine)
                                <tr>
                                    <td class="bg-white group" style="border: 1px solid #9ca3af; padding: 8px; vertical-align: top;">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="font-bold text-gray-900 uppercase" style="font-size: 11px;">{{ $machine->name }}</div>
                                                <div class="text-gray-800 font-bold uppercase" style="font-size: 9px;">{{ $machine->type }}</div>
                                            </div>
                                            <button wire:click="mountAction('editMachineSchedule', { machine: {{ $machine->id }} })" 
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-primary-600 rounded -mt-1 -mr-1">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                    
                                    @foreach($stasionDays as $day)
                                        <!-- Pagi -->
                                        <td class="bg-white relative" style="border: 1px solid #9ca3af; padding: 2px; vertical-align: top; height: 40px;"
                                            x-on:dragover.prevent="event.dataTransfer.dropEffect = 'move'"
                                            x-on:drop.prevent="$wire.updateRecordShift(event.dataTransfer.getData('recordId'), {{ $machine->id }}, '{{ $day->copy()->setTime(6, 0, 0)->toDateTimeString() }}', event.dataTransfer.getData('segmentStart'), event.dataTransfer.getData('segmentEnd'))">
                                            
                                            <div class="flex flex-col gap-0.5 min-h-[36px]">
                                                @foreach($machine->operationRecords as $record)
                                                    @php
                                                        $shiftStart = $day->copy()->setTime(6, 0, 0);
                                                        $shiftEnd = $day->copy()->setTime(18, 0, 0);
                                                        $recordStart = $record->start_time;
                                                        $recordEnd = $record->end_time;
                                                        $overlap = ($recordStart < $shiftEnd && $recordEnd > $shiftStart);
                                                        $projectCode = $record->project?->code ?? 'OTH';
                                                        $colors = ['#5cb85c', '#5bc0de', '#f0ad4e', '#d9534f', '#9966cc', '#0275d8', '#f0ad4e', '#5cb85c'];
                                                        $colorIndex = crc32($projectCode) % count($colors);
                                                        $colorStyle = "background-color: " . $colors[$colorIndex] . ";";
                                                    @endphp
                                                    @if($overlap)
                                                        <div draggable="true" x-on:dragstart="event.dataTransfer.setData('recordId', '{{ $record->id }}'); event.dataTransfer.setData('segmentStart', '{{ $shiftStart->toDateTimeString() }}'); event.dataTransfer.setData('segmentEnd', '{{ $shiftEnd->toDateTimeString() }}')" 
                                                             wire:click.stop="mountAction('editSingleSchedule', { record: {{ $record->id }} })"
                                                             class="text-white font-semibold px-1 py-0.5 shadow-sm truncate cursor-pointer hover:opacity-90 transition-opacity flex items-center justify-start w-full h-[22px]" style="{{ $colorStyle }} font-size: 9px; line-height: 12px; border-radius: 2px;"
                                                             title="{{ $projectCode }} / {{ $record->component?->name }} {{ $record->operation_type }} (Klik untuk edit)">
                                                            {{ $projectCode }} / {{ substr($record->component?->name ?? 'OP', 0, 3) }} {{ substr($record->operation_type ?? '', 0, 5) }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                        
                                        <!-- Malam -->
                                        <td class="relative" style="background-color: #d1d5db; border: 1px solid #9ca3af; padding: 2px; vertical-align: top; height: 40px;"
                                            x-on:dragover.prevent="event.dataTransfer.dropEffect = 'move'"
                                            x-on:drop.prevent="$wire.updateRecordShift(event.dataTransfer.getData('recordId'), {{ $machine->id }}, '{{ $day->copy()->setTime(18, 0, 0)->toDateTimeString() }}', event.dataTransfer.getData('segmentStart'), event.dataTransfer.getData('segmentEnd'))">
                                            
                                            <div class="flex flex-col gap-0.5 min-h-[36px]">
                                                @foreach($machine->operationRecords as $record)
                                                    @php
                                                        $shiftStart = $day->copy()->setTime(18, 0, 0);
                                                        $shiftEnd = $day->copy()->addDay()->setTime(6, 0, 0);
                                                        $recordStart = $record->start_time;
                                                        $recordEnd = $record->end_time;
                                                        $overlap = ($recordStart < $shiftEnd && $recordEnd > $shiftStart);
                                                        $projectCode = $record->project?->code ?? 'OTH';
                                                        $colors = ['#5cb85c', '#5bc0de', '#f0ad4e', '#d9534f', '#9966cc', '#0275d8', '#f0ad4e', '#5cb85c'];
                                                        $colorIndex = crc32($projectCode) % count($colors);
                                                        $colorStyle = "background-color: " . $colors[$colorIndex] . ";";
                                                    @endphp
                                                    @if($overlap)
                                                        <div draggable="true" x-on:dragstart="event.dataTransfer.setData('recordId', '{{ $record->id }}'); event.dataTransfer.setData('segmentStart', '{{ $shiftStart->toDateTimeString() }}'); event.dataTransfer.setData('segmentEnd', '{{ $shiftEnd->toDateTimeString() }}')" 
                                                             wire:click.stop="mountAction('editSingleSchedule', { record: {{ $record->id }} })"
                                                             class="text-white font-semibold px-1 py-0.5 shadow-sm truncate cursor-pointer hover:opacity-90 transition-opacity flex items-center justify-start w-full h-[22px]" style="{{ $colorStyle }} font-size: 9px; line-height: 12px; border-radius: 2px;"
                                                             title="{{ $projectCode }} / {{ $record->component?->name }} {{ $record->operation_type }} (Klik untuk edit)">
                                                            {{ $projectCode }} / {{ substr($record->component?->name ?? 'OP', 0, 3) }} {{ substr($record->operation_type ?? '', 0, 5) }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="mt-6 flex gap-4 text-xs font-semibold text-gray-600 dark:text-gray-400">
                <div class="flex items-center gap-1"><div class="w-3 h-3 bg-green-500 rounded-sm"></div> D90</div>
                <div class="flex items-center gap-1"><div class="w-3 h-3 bg-blue-500 rounded-sm"></div> A82</div>
                <div class="flex items-center gap-1"><div class="w-3 h-3 bg-orange-500 rounded-sm"></div> C33</div>
                <div class="flex items-center gap-1"><div class="w-3 h-3 bg-purple-500 rounded-sm"></div> Lainnya</div>
            </div>
        </div>

    </div>
</x-filament-panels::page>

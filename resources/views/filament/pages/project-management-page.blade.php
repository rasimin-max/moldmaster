<x-filament-panels::page>
    <div class="mb-4">
        @if(!$selectedProjectId)
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <x-heroicon-o-folder class="w-4 h-4"/>
                <span class="font-medium text-gray-900 dark:text-gray-100">Manajemen Project</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Kelola project, mold, dan komponen secara hierarkis</p>
        @elseif($selectedProjectId && !$selectedMoldId)
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <x-heroicon-o-folder class="w-4 h-4 cursor-pointer hover:text-primary-600 transition" wire:click="goBack"/>
                <span>Project</span>
                <x-heroicon-m-chevron-right class="w-4 h-4"/>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $this->selectedProject->code }}</span>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <x-heroicon-o-square-3-stack-3d class="w-7 h-7 text-green-600"/>
                        {{ $this->selectedProject->code }} — Daftar Mold
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $this->selectedProject->name }} &bull; {{ $this->selectedProject->customer }}</p>
                </div>
                <button wire:click="mountAction('createMold')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition flex items-center gap-2 shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/> Tambah Mold
                </button>
            </div>
        @elseif($selectedMoldId && !$selectedCategoryId)
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <x-heroicon-o-folder class="w-4 h-4 cursor-pointer hover:text-primary-600 transition" wire:click="$set('selectedProjectId', null); $set('selectedMoldId', null);"/>
                <span class="cursor-pointer hover:text-primary-600 transition" wire:click="goBack">Project</span>
                <x-heroicon-m-chevron-right class="w-4 h-4"/>
                <span class="cursor-pointer hover:text-primary-600 transition" wire:click="goBack">{{ $this->selectedProject->code }}</span>
                <x-heroicon-m-chevron-right class="w-4 h-4"/>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $this->selectedMold->code }}</span>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <x-heroicon-o-square-3-stack-3d class="w-7 h-7 text-green-600"/>
                        {{ $this->selectedMold->code }} — {{ $this->selectedMold->name }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Project: {{ $this->selectedProject->code }} &bull; {{ $this->selectedProject->name }}</p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="goBack" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
                        <x-heroicon-o-arrow-left class="w-4 h-4"/> Kembali
                    </button>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition flex items-center gap-2 shadow-sm">
                        <x-heroicon-o-plus class="w-4 h-4"/> Tambah Kategori
                    </button>
                </div>
            </div>
        @elseif($selectedCategoryId)
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <x-heroicon-o-folder class="w-4 h-4 cursor-pointer hover:text-primary-600 transition" wire:click="$set('selectedProjectId', null); $set('selectedMoldId', null); $set('selectedCategoryId', null);"/>
                <span class="cursor-pointer hover:text-primary-600 transition" wire:click="$set('selectedMoldId', null); $set('selectedCategoryId', null);">Project</span>
                <x-heroicon-m-chevron-right class="w-4 h-4"/>
                <span class="cursor-pointer hover:text-primary-600 transition" wire:click="$set('selectedMoldId', null); $set('selectedCategoryId', null);">{{ $this->selectedProject->code }}</span>
                <x-heroicon-m-chevron-right class="w-4 h-4"/>
                <span class="cursor-pointer hover:text-primary-600 transition" wire:click="goBack">{{ $this->selectedMold->code }}</span>
                <x-heroicon-m-chevron-right class="w-4 h-4"/>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $this->selectedCategory->name }}</span>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center relative">
                        <x-heroicon-o-photo class="w-6 h-6 text-gray-400"/>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-600 rounded-full flex items-center justify-center text-white cursor-pointer shadow">
                            <x-heroicon-m-camera class="w-3 h-3"/>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold flex items-center gap-2">
                            {{ $this->selectedCategory->name }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $this->selectedMold->code }} — {{ $this->selectedMold->name }}</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="goBack" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
                        <x-heroicon-o-arrow-left class="w-4 h-4"/> Kembali
                    </button>
                    <button class="px-4 py-2 border border-blue-200 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-50 transition flex items-center gap-2">
                        <x-heroicon-o-printer class="w-4 h-4"/> Cetak Barcode
                    </button>
                    <button class="px-4 py-2 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm font-medium hover:bg-green-100 transition flex items-center gap-2">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4"/> Export Excel
                    </button>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition flex items-center gap-2 shadow-sm">
                        <x-heroicon-o-plus class="w-4 h-4"/> Tambah Komponen
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Search Bar -->
    @if(!$selectedCategoryId)
        <div class="relative w-full mb-6 flex gap-4">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400"/>
                </div>
                <input type="text" wire:model.live.debounce.300ms="searchQuery" class="block w-full p-3 pl-10 text-sm text-gray-900 border border-gray-200 rounded-xl bg-white focus:ring-primary-500 focus:border-primary-500 shadow-sm" placeholder="Cari {{ $selectedMoldId ? 'No. Mold atau nama...' : 'No. Project, nama, atau customer...' }}">
            </div>
            @if(!$selectedProjectId)
                <button wire:click="mountAction('createProject')" class="whitespace-nowrap px-4 py-3 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition flex items-center gap-2 shadow-sm">
                    <x-heroicon-o-plus class="w-5 h-5"/> Tambah Project
                </button>
            @endif
        </div>
    @endif

    <!-- Content Area -->
    <div>
        @if(!$selectedProjectId)
            <!-- LEVEL 1: Projects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold text-green-600">{{ $this->totalProjects }}</span>
                    <span class="text-sm text-gray-500 mt-2">Total Project</span>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold text-blue-600">{{ $this->totalMolds }}</span>
                    <span class="text-sm text-gray-500 mt-2">Total Mold</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($this->projects as $project)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 bg-green-100 text-green-700 rounded-xl flex items-center justify-center">
                                    <x-heroicon-o-folder class="w-6 h-6"/>
                                </div>
                                <span class="px-2 py-1 bg-{{ $project->status == 'active' ? 'green' : 'gray' }}-100 text-{{ $project->status == 'active' ? 'green' : 'gray' }}-600 text-xs font-bold rounded-full uppercase">
                                    {{ $project->status }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 cursor-pointer hover:text-primary-600" wire:click="selectProject({{ $project->id }})">
                                {{ $project->code }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1 mb-2">{{ $project->name }}</p>
                            
                            <!-- Schedule -->
                            <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-400"/>
                                <span>
                                    {{ $project->start_date ? $project->start_date->format('d M Y') : 'TBD' }} - 
                                    {{ $project->end_date ? $project->end_date->format('d M Y') : 'TBD' }}
                                </span>
                            </div>

                            <!-- Cost Control -->
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-500">Cost Control (Realisasi vs Budget)</span>
                                    <span class="font-bold text-gray-700">Rp {{ number_format($project->total_cost, 0, ',', '.') }} / Rp {{ number_format($project->budget, 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    @php
                                        $percent = $project->budget > 0 ? min(100, ($project->total_cost / $project->budget) * 100) : 0;
                                        $color = $percent > 90 ? 'bg-red-500' : ($percent > 75 ? 'bg-yellow-400' : 'bg-green-500');
                                    @endphp
                                    <div class="{{ $color }} h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 p-4 bg-gray-50 rounded-b-xl flex justify-between items-center text-sm text-gray-600">
                            <div class="flex gap-4">
                                <span class="flex items-center gap-1"><x-heroicon-o-square-3-stack-3d class="w-4 h-4 text-gray-400"/> {{ $project->molds_count }} Mold</span>
                                <span class="flex items-center gap-1">&bull; {{ $project->components_count }} Komponen</span>
                            </div>
                            <button class="text-blue-600 font-medium hover:underline flex items-center gap-1 text-xs transition" wire:click="selectProject({{ $project->id }})">
                                Lihat Detail <x-heroicon-m-chevron-right class="w-3 h-3"/>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($selectedProjectId && !$selectedMoldId)
            @php 
                $fin = $this->projectFinancials; 
                $groupedComponents = $this->projectComponentsGroupedByCategory;
                $costByCategory = $this->projectCostByCategory;
            @endphp
            
            <!-- LEVEL 2: Professional Project Dashboard -->

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-sm text-gray-500 mb-1">Total Molds / Parts</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $fin['total_molds'] }} <span class="text-lg text-gray-400 font-normal">/ {{ $fin['total_components'] }}</span></div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-sm text-gray-500 mb-1">Plan Budget</div>
                    <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($fin['budget'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-sm text-gray-500 mb-1">Actual Cost</div>
                    <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($fin['actual_cost'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-sm text-gray-500 mb-1">Variance</div>
                    <div class="text-2xl font-bold {{ $fin['variance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($fin['variance'], 0, ',', '.') }}
                    </div>
                </div>
            </div>



            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Budget vs Actual Chart -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm"
                     x-data="{
                        initChart() {
                            const init = () => {
                                const ctx = document.getElementById('budgetChartCanvas');
                                if (!ctx) return;
                                if (window.budgetChartInstance) window.budgetChartInstance.destroy();
                                window.budgetChartInstance = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: ['Plan Budget', 'Actual Cost'],
                                        datasets: [{
                                            label: 'Rupiah (Rp)',
                                            data: [{{ json_encode((float)($fin['budget'] ?? 0)) }}, {{ json_encode((float)($fin['actual_cost'] ?? 0)) }}],
                                            backgroundColor: [
                                                'rgba(209, 213, 219, 1)',
                                                'rgba(37, 99, 235, 1)'
                                            ],
                                            borderColor: [
                                                'rgba(107, 114, 128, 1)',
                                                'rgba(29, 78, 216, 1)'
                                            ],
                                            borderWidth: 1,
                                            maxBarThickness: 150
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return 'Rp ' + (value/1000000).toLocaleString('id-ID') + ' Jt';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            };

                            if (!window.chartJsPromise) {
                                window.chartJsPromise = new Promise((resolve) => {
                                    if (typeof Chart !== 'undefined') {
                                        resolve();
                                    } else {
                                        const script = document.createElement('script');
                                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                                        script.onload = resolve;
                                        document.head.appendChild(script);
                                    }
                                });
                            }
                            window.chartJsPromise.then(() => {
                                setTimeout(init, 100);
                            });
                        }
                     }" x-init="initChart()">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Plan Budget vs Actual Budget</h3>
                    <div class="relative h-96 w-full" wire:ignore>
                        <canvas id="budgetChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Cost per Category Chart -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm"
                     x-data="{
                        initChart() {
                            if (!window.chartJsPromise) {
                                window.chartJsPromise = new Promise((resolve) => {
                                    if (typeof Chart !== 'undefined') {
                                        resolve();
                                    } else {
                                        const script = document.createElement('script');
                                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                                        script.onload = resolve;
                                        document.head.appendChild(script);
                                    }
                                });
                            }
                            window.chartJsPromise.then(() => {
                                this.$nextTick(() => {
                                    if (this.chartInstance) this.chartInstance.destroy();
                                    this.chartInstance = new Chart(this.$refs.canvas, {
                                        type: 'doughnut',
                                        data: {
                                            labels: {{ json_encode($costByCategory->pluck('category')) }},
                                            datasets: [{
                                                data: {{ json_encode($costByCategory->pluck('cost')) }},
                                                backgroundColor: [
                                                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                                                    '#8b5cf6', '#ec4899', '#14b8a6', '#64748b'
                                                ],
                                                borderWidth: 2,
                                                borderColor: '#ffffff'
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: { legend: { position: 'bottom' } }
                                        }
                                    });
                                });
                            });
                        }
                     }" x-init="initChart()">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Cost per Category</h3>
                    <div class="relative h-96 w-full" wire:ignore>
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Molds List Section -->
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Daftar Mold</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($this->molds as $mold)
                        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition cursor-pointer" wire:click="selectMold({{ $mold->id }})">
                            <div class="flex justify-between items-start mb-2">
                                <div class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-cube-transparent class="w-5 h-5"/>
                                </div>
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">{{ $mold->components_count }} Part</span>
                            </div>
                            <h4 class="font-bold text-gray-900">{{ $mold->code }} - {{ $mold->name }}</h4>
                            <p class="text-sm text-gray-500 mt-1 mb-3">Cost Terpakai: <span class="font-bold text-blue-600">Rp {{ number_format($mold->cost, 0, ',', '.') }}</span></p>
                        </div>
                    @empty
                        <div class="col-span-full p-6 text-center text-gray-500 bg-white rounded-xl border border-gray-200">
                            Belum ada mold di project ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Detailed Components List Grouped By Category -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Detail Komponen (Berdasarkan Kategori)</h3>
                        <p class="text-sm text-gray-500 mt-1">Daftar semua komponen di project ini yang dikelompokkan berdasarkan kategorinya.</p>
                    </div>
                    <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> Export Report
                    </button>
                </div>
                
                <div class="p-0">
                    @forelse($groupedComponents as $categoryName => $components)
                        <div x-data="{ expanded: false }" class="border-b border-gray-200 last:border-0">
                            <!-- Accordion Header -->
                            <div @click="expanded = !expanded" class="flex justify-between items-center p-4 bg-white hover:bg-gray-50 cursor-pointer transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                                        <x-heroicon-o-cube class="w-4 h-4"/>
                                    </div>
                                    <h4 class="font-bold text-gray-800">{{ $categoryName }}</h4>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">{{ count($components) }} items</span>
                                </div>
                                <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform duration-200" x-bind:class="expanded ? 'rotate-180' : ''" />
                            </div>
                            
                            <!-- Accordion Body -->
                            <div x-show="expanded" x-collapse class="bg-gray-50 p-4 border-t border-gray-100">
                                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th class="px-4 py-3">Mold</th>
                                                <th class="px-4 py-3">Part Name</th>
                                                <th class="px-4 py-3">Dimensi</th>
                                                <th class="px-4 py-3 text-center">Qty (T/B)</th>
                                                <th class="px-4 py-3 text-right">Harga</th>
                                                <th class="px-4 py-3 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $catTotal = 0; @endphp
                                            @foreach($components as $comp)
                                                @php 
                                                    $total = $comp->stock * $comp->unit_price; 
                                                    $catTotal += $total;
                                                @endphp
                                                <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $comp->mold->code ?? '-' }}</td>
                                                    <td class="px-4 py-3 text-gray-900">{{ $comp->name }}</td>
                                                    <td class="px-4 py-3">{{ $comp->size_spec ?? '-' }}</td>
                                                    <td class="px-4 py-3 text-center">{{ $comp->stock }} / {{ $comp->required_qty }}</td>
                                                    <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($comp->unit_price, 0, ',', '.') }}</td>
                                                    <td class="px-4 py-3 text-right font-medium text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50 border-t border-gray-200">
                                            <tr>
                                                <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-900">Total Kategori:</td>
                                                <td class="px-4 py-3 text-right font-bold text-gray-900">Rp {{ number_format($catTotal, 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            Belum ada komponen di dalam project ini.
                        </div>
                    @endforelse
                </div>
            </div>



        @elseif($selectedMoldId && !$selectedCategoryId)
            <!-- LEVEL 3: Categories Grid -->
            <p class="text-sm text-gray-500 mb-4">{{ $this->categories->count() }} Kategori Part</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($this->categories as $category)
                    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center">
                                <x-heroicon-o-cube class="w-6 h-6"/>
                            </div>
                            <button class="px-3 py-1.5 border border-blue-200 text-blue-600 rounded-md text-xs font-medium hover:bg-blue-50 transition flex items-center gap-1">
                                Edit <x-heroicon-m-chevron-right class="w-3 h-3"/>
                            </button>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 cursor-pointer hover:text-primary-600" wire:click="selectCategory({{ $category->id }})">
                            {{ $category->name }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1 mb-3">{{ $category->components_count }} komponen</p>
                        
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($category->sample_components as $sc)
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">{{ $sc }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($selectedCategoryId)
            <!-- LEVEL 4: Components Table -->
            <div class="relative w-full mb-4 mt-2">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400"/>
                </div>
                <input type="text" wire:model.live.debounce.300ms="searchQuery" class="block w-full p-3 pl-10 text-sm text-gray-900 border border-gray-200 rounded-xl bg-white focus:ring-primary-500 focus:border-primary-500 shadow-sm" placeholder="Cari nama komponen...">
            </div>

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4">#</th>
                                <th scope="col" class="px-6 py-4">Nama Komponen</th>
                                <th scope="col" class="px-6 py-4">Material</th>
                                <th scope="col" class="px-6 py-4">Dimension</th>
                                <th scope="col" class="px-6 py-4">Heat Treatmen</th>
                                <th scope="col" class="px-6 py-4 text-center">Tiba / Butuh</th>
                                <th scope="col" class="px-6 py-4 text-right">Harga Satuan</th>
                                <th scope="col" class="px-6 py-4 text-right">Total Harga</th>
                                <th scope="col" class="px-6 py-4">Remarks</th>
                                <th scope="col" class="px-6 py-4">Lokasi</th>
                                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->components as $idx => $component)
                                <tr class="bg-white border-b hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium">{{ $idx + 1 }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ $component->name }}</td>
                                    <td class="px-6 py-4">{{ $component->material ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $component->size_spec ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $component->heat_treatment ?? '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-bold text-gray-900">{{ $component->stock }}</span> / {{ $component->required_qty }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-600">Rp {{ number_format($component->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-green-700">Rp {{ number_format($component->stock * $component->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">{{ $component->remarks ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $component->rack_location ?? 'Tidak diatur' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="px-4 py-1.5 border border-blue-200 text-blue-600 rounded text-xs font-medium hover:bg-blue-50 transition">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-10 text-center text-gray-500">Belum ada komponen di kategori ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Action Modals -->
    <x-filament-actions::modals />
</x-filament-panels::page>

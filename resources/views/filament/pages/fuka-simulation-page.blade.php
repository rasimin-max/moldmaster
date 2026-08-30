<x-filament-panels::page>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-white/10 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold tracking-tight">FUKA・負荷 Simulation</h2>
                <p class="text-sm text-gray-500">Simulasi Beban Kapasitas (6 Bulan)</p>
            </div>
            
            <div class="flex flex-col text-xs text-right text-gray-500">
                <div class="flex items-center justify-end gap-2">
                    <span class="w-4 h-0.5 bg-red-400 border border-red-400 border-dashed"></span>
                    <span>Kapasitas Design: {{ number_format($capacities['design']) }} Jam</span>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <span class="w-4 h-0.5 bg-green-400 border border-green-400 border-dashed"></span>
                    <span>Kapasitas Assembly: {{ number_format($capacities['assembly']) }} Jam</span>
                </div>
            </div>
        </div>

        <div class="relative h-[400px] w-full">
            <canvas id="fukaChart"></canvas>
        </div>
    </div>

    <!-- Gunakan Chart.js dari CDN untuk kemudahan (Filament v3 biasanya bawa chart.js di widgetnya, tapi untuk halaman custom ini lebih aman) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('fukaChart').getContext('2d');
            
            const labels = @json($labels);
            const loads = @json($loads);
            const capacities = @json($capacities);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Design',
                            data: loads['design'],
                            backgroundColor: 'rgba(59, 130, 246, 0.5)', // blue
                            borderColor: 'rgb(59, 130, 246)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Machining',
                            data: loads['machining'],
                            backgroundColor: 'rgba(249, 115, 22, 0.5)', // orange
                            borderColor: 'rgb(249, 115, 22)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Assembly',
                            data: loads['assembly'],
                            backgroundColor: 'rgba(34, 197, 94, 0.5)', // green
                            borderColor: 'rgb(34, 197, 94)',
                            fill: true,
                            tension: 0.4
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2500,
                        easing: 'easeOutCubic'
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Bulan'
                            }
                        },
                        y: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Jam (H)'
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'capacityLines',
                    beforeDraw(chart, args, options) {
                        const {ctx, chartArea: {top, right, bottom, left, width, height}, scales: {x, y}} = chart;
                        
                        ctx.save();
                        
                        // Gambar garis kapasitas Design
                        const yDesign = y.getPixelForValue(capacities['design']);
                        ctx.beginPath();
                        ctx.strokeStyle = 'rgba(248, 113, 113, 0.8)'; // red-400
                        ctx.setLineDash([5, 5]);
                        ctx.lineWidth = 2;
                        ctx.moveTo(left, yDesign);
                        ctx.lineTo(right, yDesign);
                        ctx.stroke();
                        
                        // Gambar garis kapasitas Assembly
                        const yAssembly = y.getPixelForValue(capacities['assembly']);
                        ctx.beginPath();
                        ctx.strokeStyle = 'rgba(74, 222, 128, 0.8)'; // green-400
                        ctx.moveTo(left, yAssembly);
                        ctx.lineTo(right, yAssembly);
                        ctx.stroke();
                        
                        ctx.restore();
                    }
                }]
            });
        });
    </script>
</x-filament-panels::page>

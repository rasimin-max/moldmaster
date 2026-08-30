<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="fi-section rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Cari Alat (Tools)</h2>
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-scanner')"
                    class="fi-btn inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-lg bg-primary-600 text-white hover:bg-primary-500"
                >
                    📷 Scan
                </button>
            </div>

            <div
                x-data="{ show: false }"
                x-on:open-scanner.window="show = true"
                x-on:close-scanner.window="show = false"
                x-show="show"
                x-cloak
                class="mb-4"
            >
                <div id="reader" style="width: 100%;"></div>
                <button
                    type="button"
                    x-on:click="$dispatch('close-scanner')"
                    class="mt-2 px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    Tutup Kamera
                </button>
            </div>

            <form wire:submit.prevent>
                {{ $this->filterForm }}
            </form>

            <div class="mt-4 border rounded-lg divide-y max-h-[28rem] overflow-y-auto">
                @forelse($this->filteredTools as $tool)
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-800 {{ $selectedTool?->id === $tool->id ? 'bg-primary-50 dark:bg-primary-950' : '' }}">
                        <div wire:click="selectTool({{ $tool->id }})" class="flex items-center gap-3 flex-1 cursor-pointer min-w-0">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.492-3.053c.24-.294.338-.677.26-.104-.636 4.757-4.636 8.35-9.62 8.35a9.75 9.75 0 01-6.732-2.73m11.42 15.17a9.75 9.75 0 01-1.341-3.14M6.75 9.75h.008v.008H6.75V9.75z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ $tool->code }} — {{ $tool->name }}</div>
                                <div class="text-sm text-gray-500">{{ $tool->category }} · Tersedia: {{ $tool->available_quantity }}</div>
                            </div>
                        </div>
                        <button type="button" wire:click="selectTool({{ $tool->id }})" class="text-sm text-primary-600 hover:underline">
                            Pilih
                        </button>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-400">Tidak ada alat tersedia</div>
                @endforelse
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-lg font-semibold mb-4">Detail Peminjaman</h2>

            @if($selectedTool)
                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-gray-500">Kode Alat</div>
                        <div class="font-bold text-lg">{{ $selectedTool->code }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Nama Alat</div>
                        <div class="font-medium">{{ $selectedTool->name }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Lokasi</div>
                            <div>{{ $selectedTool->location ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Stok Tersedia</div>
                            <div class="font-bold text-green-600">
                                {{ $selectedTool->available_quantity }}
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-200 dark:border-gray-700">

                    <div x-data="{ qty: 1, purpose: '', max: {{ $selectedTool->available_quantity }} }" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Jumlah Pinjam *</label>
                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="qty = Math.max(1, qty - 1)" class="w-8 h-8 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-lg flex items-center justify-center">−</button>
                                <input type="number" x-model.number="qty" min="1" :max="max" class="w-20 text-center border rounded-lg py-1 dark:bg-gray-800" />
                                <button type="button" x-on:click="qty = Math.min(max, qty + 1)" class="w-8 h-8 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-lg flex items-center justify-center">+</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Keperluan *</label>
                            <textarea x-model="purpose" rows="3" class="w-full border rounded-lg p-2 dark:bg-gray-800" placeholder="Untuk apa alat ini digunakan..."></textarea>
                        </div>

                        <x-filament::button color="primary" x-on:click="$wire.borrowTool(qty, purpose)" class="w-full mt-4">
                            Konfirmasi Pinjam Alat
                        </x-filament::button>
                    </div>
                </div>
            @else
                <div class="text-center text-gray-400 py-12 flex flex-col items-center justify-center h-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    Scan barcode atau pilih alat di sebelah kiri untuk meminjam
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            let html5QrCode = null;

            window.addEventListener('open-scanner', () => {
                setTimeout(() => {
                    html5QrCode = new Html5Qrcode("reader");
                    html5QrCode.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: 250 },
                        (decodedText) => {
                            @this.call('findByCode', decodedText);
                            html5QrCode.stop();
                            window.dispatchEvent(new CustomEvent('close-scanner'));
                        },
                        (errorMessage) => {}
                    ).catch((err) => {
                        alert('Tidak bisa mengakses kamera: ' + err);
                    });
                }, 200);
            });

            window.addEventListener('close-scanner', () => {
                if (html5QrCode) {
                    html5QrCode.stop().catch(() => {});
                }
            });
        });
    </script>
    @endpush

    <div class="mt-8">
        {{ $this->table }}
    </div>
</x-filament-panels::page>

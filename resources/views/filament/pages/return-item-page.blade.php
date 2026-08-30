<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="fi-section rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Cari Komponen</h2>
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
                @forelse($this->filteredComponents as $comp)
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-800 {{ $selectedComponent?->id === $comp->id ? 'bg-primary-50 dark:bg-primary-950' : '' }}">
                        <div wire:click="selectComponent({{ $comp->id }})" class="flex items-center gap-3 flex-1 cursor-pointer min-w-0">
                            @if($comp->photo)
                                <img src="{{ Storage::url($comp->photo) }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0" />
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-400 flex-shrink-0">-</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ $comp->code }} — {{ $comp->name }}</div>
                                <div class="text-sm text-gray-500">{{ $comp->category?->name }}</div>
                            </div>
                        </div>
                        <button type="button" wire:click="selectComponent({{ $comp->id }})" class="text-sm text-primary-600 hover:underline">
                            Pilih
                        </button>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-400">Tidak ada komponen</div>
                @endforelse
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-lg font-semibold mb-4">Detail Pengembalian</h2>

            @if($selectedComponent)
                <div class="space-y-4">
                    @if($selectedComponent->photo)
                        <img src="{{ Storage::url($selectedComponent->photo) }}"
                             class="w-full h-48 object-cover rounded-lg" />
                    @endif

                    <div>
                        <div class="text-sm text-gray-500">Kode</div>
                        <div class="font-bold text-lg">{{ $selectedComponent->code }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Nama</div>
                        <div class="font-medium">{{ $selectedComponent->name }}</div>
                    </div>

                    <hr class="my-4 border-gray-200 dark:border-gray-700">

                    <div x-data="{ qty: 1, condition: 'good', notes: '' }" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Jumlah Kembali *</label>
                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="qty = Math.max(1, qty - 1)" class="w-8 h-8 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-lg flex items-center justify-center">−</button>
                                <input type="number" x-model.number="qty" min="1" class="w-20 text-center border rounded-lg py-1 dark:bg-gray-800" />
                                <button type="button" x-on:click="qty = qty + 1" class="w-8 h-8 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-lg flex items-center justify-center">+</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Kondisi Barang *</label>
                            <select x-model="condition" class="w-full border rounded-lg p-2 dark:bg-gray-800">
                                <option value="good">Baik</option>
                                <option value="damaged">Rusak</option>
                                <option value="lost">Hilang</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Catatan / Keperluan Tambahan</label>
                            <textarea x-model="notes" rows="3" class="w-full border rounded-lg p-2 dark:bg-gray-800" placeholder="Opsional..."></textarea>
                        </div>

                        <x-filament::button color="success" x-on:click="$wire.returnItem(qty, condition, notes)" class="w-full mt-4">
                            Konfirmasi Kembalikan Barang
                        </x-filament::button>
                    </div>
                </div>
            @else
                <div class="text-center text-gray-400 py-12 flex flex-col items-center justify-center h-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Scan barcode atau pilih komponen di sebelah kiri untuk dikembalikan
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

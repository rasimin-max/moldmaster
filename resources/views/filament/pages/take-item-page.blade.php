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
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            @if($comp->photo)
                                <img src="{{ Storage::url($comp->photo) }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0" />
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-400 flex-shrink-0">-</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ $comp->code }} — {{ $comp->name }}</div>
                                <div class="text-sm text-gray-500">{{ $comp->category?->name }} · Stok: {{ $comp->available_stock }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 flex-shrink-0">
                            <input
                                type="number"
                                wire:model="inputQty.{{ $comp->id }}"
                                min="0"
                                max="{{ $comp->available_stock }}"
                                wire:keydown.enter="addFromInput({{ $comp->id }})"
                                class="w-20 text-center border-gray-300 dark:bg-gray-800 rounded-lg py-1 text-sm focus:border-primary-500 focus:ring-primary-500"
                                placeholder="0"
                            />
                            <button
                                type="button"
                                wire:click="addFromInput({{ $comp->id }})"
                                class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-600 hover:text-primary-600 transition"
                                title="Tambah ke keranjang"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.98-4.804 2.545-7.454A1.125 1.125 0 0019.905 4.5H5.106M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-400">Tidak ada komponen</div>
                @endforelse
            </div>
        </div>

        {{-- Keranjang --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.98-4.804 2.545-7.454A1.125 1.125 0 0019.905 4.5H5.106M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
                Keranjang ({{ count($cart) }})
            </h2>

            @if($this->cartItems->isNotEmpty())
                <div class="border rounded-lg divide-y mb-4">
                    @foreach($this->cartItems as $item)
                        <div class="flex items-center gap-3 p-3">
                            <div class="flex-1">
                                <div class="font-medium">{{ $item->code }} — {{ $item->name }}</div>
                                <div class="text-sm text-gray-500">Stok tersedia: {{ $item->available_stock }}</div>
                            </div>
                            <button type="button" wire:click="decrementCartItem({{ $item->id }})" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200">−</button>
                            <span class="w-8 text-center font-medium">{{ $item->cart_qty }}</span>
                            <button type="button" wire:click="incrementCartItem({{ $item->id }})" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200">+</button>
                            <button type="button" wire:click="removeFromCart({{ $item->id }})" class="ml-2 text-red-500 hover:text-red-700 text-sm">Hapus</button>
                        </div>
                    @endforeach
                </div>

                <x-filament::button color="primary" wire:click="confirmCart" class="w-full">
                    Konfirmasi Ambil Semua Barang
                </x-filament::button>
            @else
                <div class="text-center text-gray-400 py-6">
                    Keranjang kosong — atur jumlah lalu klik ikon keranjang pada komponen di daftar
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

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
                                <div class="text-sm text-gray-500">{{ $comp->category?->name }} · Stok: {{ $comp->available_stock }}</div>
                            </div>
                        </div>

                        <div x-data="{ qty: 0, max: {{ $comp->available_stock }} }" class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" x-on:click="qty = Math.max(0, (qty || 0) - 1)" class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-xs">−</button>
                            <input
                                type="number"
                                x-model.number="qty"
                                min="0"
                                :class="qty > max ? 'border-red-400 bg-red-50 text-red-600' : 'border-gray-300 dark:bg-gray-800'"
                                class="w-12 text-center border rounded-lg py-1 text-sm"
                            />
                            <button type="button" x-on:click="qty = Math.min(max, (qty || 0) + 1)" class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-xs">+</button>
                            <button
                                type="button"
                                x-on:click="if (qty > 0 && qty <= max) { $wire.addToCart({{ $comp->id }}, qty); qty = 0; }"
                                class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800"
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

        <div class="fi-section rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-lg font-semibold mb-4">Detail Komponen</h2>

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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Kategori</div>
                            <div>{{ $selectedComponent->category?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Mold</div>
                            <div>{{ $selectedComponent->mold?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Lokasi Rak</div>
                            <div>{{ $selectedComponent->rack_location ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Stok Tersedia</div>
                            <div class="font-bold {{ $selectedComponent->is_low_stock ? 'text-red-600' : 'text-green-600' }}">
                                {{ $selectedComponent->available_stock }} {{ $selectedComponent->unit }}
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div x-data="{ qty: 0 }" class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">Jumlah Kembali:</span>
                        <button type="button" x-on:click="qty = Math.max(0, (qty || 0) - 1)" class="w-7 h-7 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-sm">−</button>
                        <input type="number" x-model.number="qty" min="0" class="w-14 text-center border rounded-lg py-1 text-sm dark:bg-gray-800" />
                        <button type="button" x-on:click="qty = (qty || 0) + 1" class="w-7 h-7 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-sm">+</button>
                        <x-filament::button color="success" x-on:click="if (qty > 0) { $wire.returnItem(qty); qty = 0; }" class="flex-1">
                            Kembalikan
                        </x-filament::button>
                    </div>
                </div>
            @else
                <div class="text-center text-gray-400 py-12">
                    Scan barcode atau pilih komponen di sebelah kiri
                </div>
            @endif
        </div>
    </div>

    {{-- Keranjang --}}
    <div class="fi-section rounded-xl bg-white p-6 shadow dark:bg-gray-900 mt-6">
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
</x-filament-panels::page>

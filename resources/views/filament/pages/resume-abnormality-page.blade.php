<x-filament-panels::page>
    <div class="space-y-6">
        <p class="text-gray-600 dark:text-gray-400">
            Halaman ini menampilkan seluruh riwayat laporan abnormality mesin dan part. Anda dapat menggunakan fitur filter, pencarian, dan export Excel yang tersedia pada tabel di bawah.
        </p>
        
        {{ $this->table }}
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <x-filament-panels::form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit" color="danger">
                Kirim Laporan
            </x-filament::button>
        </div>
    </x-filament-panels::form>
    <div class="mt-8">
        {{ $this->table }}
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>

    <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold mb-4 dark:text-white">Riwayat Aktivitas Harian</h2>
        {{ $this->table }}
    </div>
</x-filament-panels::page>

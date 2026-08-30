<x-filament-panels::page>
    <div class="mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Konfigurasi parameter, keamanan, dan backup sistem</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Parameter Aplikasi -->
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:bg-gray-50 transition" wire:click="mountAction('parameterAplikasi')">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-lg mr-4">
                <x-heroicon-o-cog class="w-6 h-6"/>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-200">Parameter Aplikasi</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Konfigurasi parameter dasar sistem mold tracking</p>
            </div>
        </div>

        <!-- Maintenance Parameter -->
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:bg-gray-50 transition" wire:click="mountAction('maintenanceParameter')">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-lg mr-4">
                <x-heroicon-o-wrench-screwdriver class="w-6 h-6"/>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-200">Maintenance Parameter</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Interval notifikasi dan batas stok minimum</p>
            </div>
        </div>

        <!-- Dashboard Settings -->
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:bg-gray-50 transition" wire:click="mountAction('dashboardSettings')">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-lg mr-4">
                <x-heroicon-o-chart-bar-square class="w-6 h-6"/>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-200">Dashboard Settings</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pengaturan widget dan layout dashboard</p>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:bg-gray-50 transition" wire:click="mountAction('securitySettings')">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-lg mr-4">
                <x-heroicon-o-shield-check class="w-6 h-6"/>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-200">Security Settings</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Password policy, session timeout, dan 2FA</p>
            </div>
        </div>

        <!-- Backup & Restore -->
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:bg-gray-50 transition" wire:click="mountAction('backupRestore')">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-lg mr-4">
                <x-heroicon-o-arrow-path class="w-6 h-6"/>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-200">Backup & Restore</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Backup otomatis dan restore data sistem</p>
            </div>
        </div>

        <!-- API Settings -->
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:bg-gray-50 transition" wire:click="mountAction('apiSettings')">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-lg mr-4">
                <x-heroicon-o-server-stack class="w-6 h-6"/>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-200">API Settings</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">API key dan webhook konfigurasi integrasi</p>
            </div>
        </div>

        <!-- Mode Maintenance -->
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:bg-red-50 transition" wire:click="mountAction('modeMaintenance')">
            <div class="p-3 bg-red-100 dark:bg-red-900/30 text-red-600 rounded-lg mr-4">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6"/>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-200">Mode Maintenance</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Aktifkan/nonaktifkan mode maintenance system-wide</p>
            </div>
        </div>

    </div>

    <!-- Container for the injected modals -->
    <x-filament-actions::modals />
</x-filament-panels::page>

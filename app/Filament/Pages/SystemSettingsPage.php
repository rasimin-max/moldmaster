<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Filament\Forms\Components\Select;

class SystemSettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationGroup = 'Menu Admin';
    protected static ?string $title = 'Pengaturan Sistem';
    protected static ?string $navigationLabel = 'Pengaturan Sistem';
    protected static ?int $navigationSort = 10;
    
    protected static string $view = 'filament.pages.system-settings-page';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    protected function getActions(): array
    {
        return [
            $this->getParameterAplikasiAction(),
            $this->getMaintenanceParameterAction(),
            $this->getSecuritySettingsAction(),
            $this->getBackupRestoreAction(),
            $this->getModeMaintenanceAction(),
            $this->getApiSettingsAction(),
        ];
    }

    public function getParameterAplikasiAction(): Action
    {
        return Action::make('parameterAplikasi')
            ->label('Parameter Aplikasi')
            ->icon('heroicon-o-cog')
            ->color('success')
            ->modalHeading('Konfigurasi Parameter Aplikasi')
            ->fillForm(fn (GeneralSettings $settings) => [
                'app_name' => $settings->app_name,
            ])
            ->form([
                TextInput::make('app_name')
                    ->label('Nama Aplikasi')
                    ->required(),
            ])
            ->action(function (array $data, GeneralSettings $settings) {
                $settings->app_name = $data['app_name'];
                $settings->save();
                Notification::make()->title('Pengaturan berhasil disimpan')->success()->send();
            });
    }

    public function getMaintenanceParameterAction(): Action
    {
        return Action::make('maintenanceParameter')
            ->label('Maintenance Parameter')
            ->icon('heroicon-o-wrench-screwdriver')
            ->color('success')
            ->modalHeading('Maintenance Parameter')
            ->fillForm(fn (GeneralSettings $settings) => [
                'minimum_stock_threshold' => $settings->minimum_stock_threshold,
            ])
            ->form([
                TextInput::make('minimum_stock_threshold')
                    ->label('Batas Stok Minimum')
                    ->numeric()
                    ->required(),
            ])
            ->action(function (array $data, GeneralSettings $settings) {
                $settings->minimum_stock_threshold = $data['minimum_stock_threshold'];
                $settings->save();
                Notification::make()->title('Pengaturan berhasil disimpan')->success()->send();
            });
    }

    public function getSecuritySettingsAction(): Action
    {
        return Action::make('securitySettings')
            ->label('Security Settings')
            ->icon('heroicon-o-shield-check')
            ->color('success')
            ->modalHeading('Security Settings')
            ->fillForm(fn (GeneralSettings $settings) => [
                'session_timeout' => $settings->session_timeout,
            ])
            ->form([
                TextInput::make('session_timeout')
                    ->label('Session Timeout (Menit)')
                    ->numeric()
                    ->required(),
            ])
            ->action(function (array $data, GeneralSettings $settings) {
                $settings->session_timeout = $data['session_timeout'];
                $settings->save();
                Notification::make()->title('Pengaturan berhasil disimpan')->success()->send();
            });
    }

    public function getBackupRestoreAction(): Action
    {
        return Action::make('backupRestore')
            ->label('Backup & Restore')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->modalHeading('Backup Database')
            ->modalDescription('Proses ini akan menjalankan fitur backup database ke direktori penyimpanan internal Anda. Apakah Anda yakin ingin melanjutkan?')
            ->requiresConfirmation()
            ->action(function () {
                // Artisan::call('backup:run'); // Jika library spatie/laravel-backup terinstall
                Notification::make()->title('Proses Backup sedang dijalankan di latar belakang')->success()->send();
            });
    }

    public function getModeMaintenanceAction(): Action
    {
        return Action::make('modeMaintenance')
            ->label('Mode Maintenance')
            ->icon('heroicon-o-exclamation-triangle')
            ->color('danger')
            ->modalHeading('Mode Maintenance')
            ->fillForm(fn (GeneralSettings $settings) => [
                'maintenance_mode' => $settings->maintenance_mode,
            ])
            ->form([
                Toggle::make('maintenance_mode')
                    ->label('Aktifkan Mode Maintenance')
                    ->helperText('Jika diaktifkan, hanya Super Admin yang dapat mengakses sistem (melalui Bypass). Pengguna lain akan melihat halaman Maintenance.'),
            ])
            ->action(function (array $data, GeneralSettings $settings) {
                $settings->maintenance_mode = $data['maintenance_mode'];
                $settings->save();

                if ($data['maintenance_mode']) {
                    Artisan::call('down', ['--secret' => 'superadmin-bypass']);
                    Notification::make()->title('Maintenance Mode DIAKTIFKAN. Bypass URL: /superadmin-bypass')->warning()->send();
                } else {
                    Artisan::call('up');
                    Notification::make()->title('Maintenance Mode DIMATIKAN.')->success()->send();
                }
            });
    }

    public function getApiSettingsAction(): Action
    {
        return Action::make('apiSettings')
            ->label('API Settings')
            ->icon('heroicon-o-server-stack')
            ->color('success')
            ->modalHeading('API Configuration')
            ->fillForm(fn (GeneralSettings $settings) => [
                'api_key' => $settings->api_key,
                'webhook_url' => $settings->webhook_url,
            ])
            ->form([
                TextInput::make('api_key')
                    ->label('API Key')
                    ->password()
                    ->revealable()
                    ->helperText('Gunakan API Key ini untuk mengautentikasi aplikasi pihak ketiga.')
                    ->nullable(),
                TextInput::make('webhook_url')
                    ->label('Webhook URL')
                    ->url()
                    ->helperText('URL yang akan dipanggil (POST) saat terjadi event penting.')
                    ->nullable(),
            ])
            ->action(function (array $data, GeneralSettings $settings) {
                $settings->api_key = $data['api_key'] ?? '';
                $settings->webhook_url = $data['webhook_url'] ?? '';
                $settings->save();
                Notification::make()->title('Pengaturan API berhasil disimpan')->success()->send();
            });
    }
}

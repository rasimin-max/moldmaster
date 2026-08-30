<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class DashboardSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'Menu Admin';
    protected static ?string $title = 'Pengaturan Dashboard';
    protected static ?string $navigationLabel = 'Pengaturan Dashboard';
    protected static ?int $navigationSort = 11;
    protected static string $view = 'filament.pages.dashboard-settings-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public function mount(GeneralSettings $settings): void
    {
        $this->form->fill([
            'active_widgets' => $settings->active_widgets ?? [],
        ]);
    }

    public function form(Form $form): Form
    {
        // Mendapatkan daftar widget otomatis dari folder
        $widgetFiles = glob(app_path('Filament/Widgets/*.php'));
        $widgetOptions = [];
        
        foreach ($widgetFiles as $file) {
            $className = basename($file, '.php');
            $widgetOptions[$className] = preg_replace('/(?<!^)([A-Z])/', ' $1', $className);
        }

        return $form
            ->schema([
                Section::make('Widget Dashboard')
                    ->description('Pilih widget apa saja yang ingin ditampilkan di Dashboard. Konfigurasi ini seperti Role (tinggal centang widget yang ingin dimunculkan).')
                    ->schema([
                        CheckboxList::make('active_widgets')
                            ->label('Daftar Widget')
                            ->options($widgetOptions)
                            ->columns(2)
                            ->bulkToggleable()
                            ->searchable(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(GeneralSettings $settings): void
    {
        $settings->active_widgets = $this->form->getState()['active_widgets'] ?? [];
        $settings->save();

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}

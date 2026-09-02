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
            'role_active_widgets' => $settings->role_active_widgets ?? [],
        ]);
    }

    public function form(Form $form): Form
    {
        $widgetFiles = glob(app_path('Filament/Widgets/*.php'));
        $widgetOptions = [];
        
        foreach ($widgetFiles as $file) {
            $className = basename($file, '.php');
            $widgetOptions[$className] = preg_replace('/(?<!^)([A-Z])/', ' $1', $className);
        }

        $roles = \Spatie\Permission\Models\Role::pluck('name', 'name')->toArray();
        $tabs = [];
        
        foreach ($roles as $role) {
            $tabs[] = Forms\Components\Tabs\Tab::make(strtoupper(str_replace('_', ' ', $role)))
                ->schema([
                    CheckboxList::make("role_active_widgets.{$role}")
                        ->label("Widget yang aktif untuk " . strtoupper(str_replace('_', ' ', $role)))
                        ->options($widgetOptions)
                        ->columns(2)
                        ->bulkToggleable()
                        ->searchable(),
                ]);
        }

        return $form
            ->schema([
                Section::make('Widget Dashboard (Per Role)')
                    ->description('Pilih widget apa saja yang ingin ditampilkan di Dashboard untuk masing-masing role.')
                    ->schema([
                        Forms\Components\Tabs::make('Role Tabs')
                            ->tabs($tabs),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(GeneralSettings $settings): void
    {
        $settings->role_active_widgets = $this->form->getState()['role_active_widgets'] ?? [];
        $settings->save();

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}

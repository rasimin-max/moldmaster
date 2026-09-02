<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $app_name;
    public bool $maintenance_mode;
    public int $session_timeout;
    public int $minimum_stock_threshold;
    public string $dashboard_layout;
    public array $active_widgets;
    public array $role_active_widgets;
    public string $api_key;
    public string $webhook_url;

    public static function group(): string
    {
        return 'general';
    }

    public function getActiveWidgetsForUser(): array
    {
        $roleWidgets = $this->role_active_widgets ?? [];
        if (empty($roleWidgets)) {
            return $this->active_widgets ?? [];
        }

        $user = auth()->user();
        if (!$user) return [];

        $userRoles = $user->roles->pluck('name')->toArray();
        $activeWidgets = [];
        
        foreach ($userRoles as $role) {
            if (isset($roleWidgets[$role]) && is_array($roleWidgets[$role])) {
                $activeWidgets = array_merge($activeWidgets, $roleWidgets[$role]);
            }
        }
        
        return array_unique($activeWidgets);
    }
}

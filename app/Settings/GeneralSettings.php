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
    public string $api_key;
    public string $webhook_url;

    public static function group(): string
    {
        return 'general';
    }
}

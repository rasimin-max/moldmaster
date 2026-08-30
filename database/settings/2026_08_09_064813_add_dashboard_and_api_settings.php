<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.dashboard_layout', 'default');
        $this->migrator->add('general.active_widgets', ['stock_summary', 'recent_activities', 'mold_status']);
        $this->migrator->add('general.api_key', '');
        $this->migrator->add('general.webhook_url', '');
    }
};

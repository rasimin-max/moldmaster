<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.app_name', 'MOLDMASTER');
        $this->migrator->add('general.maintenance_mode', false);
        $this->migrator->add('general.session_timeout', 120);
        $this->migrator->add('general.minimum_stock_threshold', 10);
    }
};

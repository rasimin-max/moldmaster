<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$perm = Permission::firstOrCreate(['name' => 'page_SagyoNippoCalendarPage', 'guard_name' => 'web']);
echo "Permission: {$perm->name}\n";

$roles = Role::whereIn('name', ['super_admin', 'admin', 'manager'])->get();
foreach ($roles as $role) {
    $role->givePermissionTo($perm);
    echo "Assigned to: {$role->name}\n";
}

echo "Done!\n";

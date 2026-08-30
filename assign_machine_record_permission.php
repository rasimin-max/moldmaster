<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::where('name', 'operator')->first();
if (!$role) {
    echo "Role 'operator' not found.\n";
    exit;
}

$permissions = Permission::where('name', 'like', '%machine::operation::record%')->get();

$assigned = 0;
foreach ($permissions as $permission) {
    $role->givePermissionTo($permission);
    echo "Assigned {$permission->name} to operator.\n";
    $assigned++;
}

echo "Assigned $assigned permissions successfully.\n";

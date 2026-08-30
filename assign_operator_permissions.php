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

$operatorPermissions = [
    'page_SagyoNippoEntryPage',
    'page_TakeItemPage',
    'page_ReturnItemPage',
    'page_BorrowToolPage',
    'page_ReturnToolPage',
    'page_RequestPartToolPage',
    'page_ReportAbnormalityPage',
    'page_ReportImprovementPage',
    'page_OperatorStock',
    'page_Dashboard' // Assuming they need access to dashboard
];

$assigned = 0;
foreach ($operatorPermissions as $permName) {
    $permission = Permission::where('name', $permName)->first();
    if ($permission) {
        $role->givePermissionTo($permission);
        echo "Assigned $permName to operator.\n";
        $assigned++;
    } else {
        echo "Permission $permName not found in DB.\n";
    }
}
echo "Assigned $assigned permissions successfully.\n";

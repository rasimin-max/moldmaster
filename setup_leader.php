<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$operator = Role::where('name', 'operator')->first();
$leader = Role::where('name', 'leader')->first();

if ($operator && $leader) {
    // Get all operator permissions
    $operatorPermissions = $operator->permissions->pluck('name')->toArray();
    
    // Additional leader permissions
    $additional = [
        'page_ApprovalRequestPage',
        'page_MasterSchedulePage',
        'approve maintenances',
        'approve stock movements',
        'approve tool loans',
        'reject maintenances',
        'reject stock movements',
        'reject tool loans'
    ];
    
    // Filter out any permissions that don't actually exist in the DB just to be safe
    $validAdditional = Permission::whereIn('name', $additional)->pluck('name')->toArray();
    
    // Combine and sync
    $allPermissions = array_unique(array_merge($operatorPermissions, $validAdditional));
    $leader->syncPermissions($allPermissions);
    
    echo "Successfully assigned " . count($allPermissions) . " permissions to leader.\n";
} else {
    echo "Error: Operator or Leader role not found.\n";
}

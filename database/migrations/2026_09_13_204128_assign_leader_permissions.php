<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
            
            // Filter out any permissions that don't actually exist in the DB
            $validAdditional = Permission::whereIn('name', $additional)->pluck('name')->toArray();
            
            // Combine and sync
            $allPermissions = array_unique(array_merge($operatorPermissions, $validAdditional));
            $leader->syncPermissions($allPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

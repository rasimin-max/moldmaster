<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::firstOrCreate([
            'name'       => 'page_SagyoNippoCalendarPage',
            'guard_name' => 'web',
        ]);

        // Assign ke semua role yang punya akses laporan
        $roles = Role::whereIn('name', ['super_admin', 'admin', 'manager', 'operator'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($perm);
        }
    }

    public function down(): void
    {
        Permission::where('name', 'page_SagyoNippoCalendarPage')->delete();
    }
};

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'view dashboard',
            // Components
            'view components', 'create components', 'edit components', 'delete components',
            'generate qr components',
            // Stock Movements
            'view stock movements', 'create stock movements',
            'approve stock movements', 'reject stock movements', 'edit stock movements',
            // Purchase Orders
            'view purchase orders', 'create purchase orders', 'edit purchase orders',
            'delete purchase orders', 'approve purchase orders',
            // Maintenance
            'view maintenances', 'create maintenances', 'edit maintenances',
            'approve maintenances', 'reject maintenances', 'set maintenance priority',
            // Tool Loans
            'view tool loans', 'create tool loans', 'approve tool loans',
            'reject tool loans', 'return tool loans',
            // Stock Opname
            'view stock opnames', 'create stock opnames', 'approve stock opnames',
            // Master Data
            'view vendors', 'create vendors', 'edit vendors', 'delete vendors',
            'view molds', 'create molds', 'edit molds', 'delete molds',
            'view machines', 'create machines', 'edit machines', 'delete machines',
            'view tools', 'create tools', 'edit tools', 'delete tools',
            // Reports
            'view reports', 'export reports',
            // Users
            'view users', 'create users', 'edit users', 'delete users', 'manage roles',
            // Audit
            'view audit logs',
            // Settings
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // VIEWER
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'view dashboard', 'view components', 'view stock movements',
            'view purchase orders', 'view maintenances', 'view tool loans',
            'view stock opnames', 'view vendors', 'view molds', 'view machines',
            'view tools', 'view reports',
        ]);

        // OPERATOR
        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->syncPermissions([
            'view dashboard', 'view components', 'generate qr components',
            'view stock movements', 'create stock movements',
            'view maintenances', 'create maintenances',
            'view tool loans', 'create tool loans', 'return tool loans',
            'view purchase orders', 'view vendors', 'view molds', 'view machines',
            'view tools', 'view reports',
        ]);

        // LEADER
        $leader = Role::firstOrCreate(['name' => 'leader']);
        $leader->syncPermissions([
            'view dashboard', 'view components',
            'view stock movements', 'create stock movements',
            'approve stock movements', 'reject stock movements',
            'view maintenances', 'create maintenances',
            'approve maintenances', 'reject maintenances', 'set maintenance priority',
            'view tool loans', 'create tool loans', 'approve tool loans', 'reject tool loans', 'return tool loans',
            'view purchase orders', 'approve purchase orders',
            'view stock opnames',
            'view vendors', 'view molds', 'view machines', 'view tools',
            'view reports', 'export reports',
            'view audit logs',
        ]);

        // ADMIN
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view dashboard', 'view components', 'create components', 'edit components', 'generate qr components',
            'view stock movements', 'create stock movements', 'approve stock movements', 'reject stock movements', 'edit stock movements',
            'view purchase orders', 'create purchase orders', 'edit purchase orders', 'delete purchase orders', 'approve purchase orders',
            'view maintenances', 'create maintenances', 'edit maintenances', 'approve maintenances', 'reject maintenances', 'set maintenance priority',
            'view tool loans', 'create tool loans', 'approve tool loans', 'reject tool loans', 'return tool loans',
            'view stock opnames', 'create stock opnames', 'approve stock opnames',
            'view vendors', 'create vendors', 'edit vendors',
            'view molds', 'create molds', 'edit molds',
            'view machines', 'create machines', 'edit machines',
            'view tools', 'create tools', 'edit tools',
            'view reports', 'export reports',
            'view users', 'create users', 'edit users',
            'view audit logs',
        ]);

        // SUPER ADMIN - all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        $this->command->info('Roles and permissions seeded successfully!');
    }
}

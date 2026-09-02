<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        // Temukan role operator
        $role = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);

        // Daftar izin sesuai screenshot
        $permissions = [
            'page_ResumeSagyoNippoPage',
            'page_SagyoNippoEntryPage',
            'page_TakeItemPage',
            'page_ReturnItemPage',
            'page_BorrowToolPage',
            'page_ReturnToolPage',
            'page_RequestPartToolPage',
            'page_ReportAbnormalityPage',
            'page_ImprovementReportPage',
            'page_ReportImprovementPage',
            'page_ResumeMachineOperationRecordPage',
        ];

        // Pastikan permissionnya ada di database dulu
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Setel HANYA izin-izin di atas untuk operator
        $role->syncPermissions($permissions);
    }

    public function down(): void
    {
        // Kosongkan untuk rollback
    }
};

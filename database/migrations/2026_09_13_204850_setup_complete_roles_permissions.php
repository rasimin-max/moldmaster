<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    public function up(): void
    {
        // Generate all permissions first
        Artisan::call('shield:generate', ['--all' => true]);

        $operator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $leader = Role::firstOrCreate(['name' => 'leader', 'guard_name' => 'web']);

        $operatorPermissions = [
            'view_any_machine::operation::record', 'create_machine::operation::record', 'update_machine::operation::record', 'view_machine::operation::record',
            'view_any_sagyo::nippo', 'create_sagyo::nippo', 'update_sagyo::nippo', 'view_sagyo::nippo',
            'view_any_maintenance', 'create_maintenance', 'view_maintenance',
            'view_any_tool::loan', 'create_tool::loan', 'update_tool::loan', 'view_tool::loan',
            'view_any_stock::movement', 'create_stock::movement', 'view_stock::movement',
            'view_any_machine', 'view_machine',
            'view_any_machine::part', 'view_machine::part',
            'view_any_tool', 'view_tool',
            'view_any_component', 'view_component',
            'view_any_mold', 'view_mold',
            'page_SagyoNippoEntryPage', 'page_ResumeSagyoNippoPage',
            'page_ResumeMachineOperationRecordPage',
            'page_BorrowToolPage', 'page_ReturnToolPage',
            'page_TakeItemPage', 'page_ReturnItemPage',
            'page_ReportAbnormalityPage', 'page_ReportImprovementPage',
            'page_OperatorStock'
        ];

        $leaderPermissions = array_merge($operatorPermissions, [
            'page_ApprovalRequestPage',
            'page_MasterSchedulePage',
            'page_ProjectManagementPage',
            'approve maintenances', 'reject maintenances',
            'approve stock movements', 'reject stock movements',
            'approve tool loans', 'reject tool loans',
            'view_any_project', 'view_project',
            'update_maintenance',
            'update_stock::movement',
            'view_any_user',
            'view_user'
        ]);

        // Only assign permissions that exist in the database to prevent errors
        $validOperatorPerms = Permission::whereIn('name', $operatorPermissions)->pluck('name')->toArray();
        $validLeaderPerms = Permission::whereIn('name', $leaderPermissions)->pluck('name')->toArray();

        $operator->syncPermissions($validOperatorPerms);
        $leader->syncPermissions($validLeaderPerms);
    }

    public function down(): void
    {
        //
    }
};

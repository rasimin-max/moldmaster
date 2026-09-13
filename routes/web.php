<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComponentQrController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/run-migrations-system', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "Migrations ran successfully: " . nl2br(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Exception $e) {
        return "Error running migrations: " . $e->getMessage();
    }
});

Route::get('/force-drop-constraint', function () {
    try {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE machines DROP CONSTRAINT IF EXISTS machines_type_check');
        return "Constraint dropped successfully!";
    } catch (\Exception $e) {
        return "Error dropping constraint: " . $e->getMessage();
    }
});

Route::get('/force-migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return nl2br(e(\Illuminate\Support\Facades\Artisan::output()));
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Fallback route to serve files if symlinks are broken on Railway's php artisan serve
Route::get('/storage/{path}', function (string $path) {
    $pathsToCheck = [
        storage_path('app/public/' . $path),
        storage_path('app/private/' . $path),
        storage_path('app/' . $path),
    ];
    
    foreach ($pathsToCheck as $filePath) {
        if (file_exists($filePath)) {
            return response()->file($filePath);
        }
    }
    
    abort(404);
})->where('path', '.*');

Route::get('/components/{component}/qr', [ComponentQrController::class, 'show'])
    ->name('components.qr');

Route::get('/components-bulk-qr', [ComponentQrController::class, 'bulkShow'])
    ->name('components.qr.bulk');

Route::get('/tools/{tool}/qr', [\App\Http\Controllers\ToolQrController::class, 'show'])
    ->name('tools.qr');

Route::get('/fix-roles', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin']);

        $operator = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $leader = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'leader', 'guard_name' => 'web']);

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

        $validOperatorPerms = \Spatie\Permission\Models\Permission::whereIn('name', $operatorPermissions)->pluck('name')->toArray();
        $validLeaderPerms = \Spatie\Permission\Models\Permission::whereIn('name', $leaderPermissions)->pluck('name')->toArray();

        $operator->syncPermissions($validOperatorPerms);
        $leader->syncPermissions($validLeaderPerms);

        return "SUCCESS! Roles and Permissions configured properly.<br><br>Operator: " . count($validOperatorPerms) . " permissions.<br>Leader: " . count($validLeaderPerms) . " permissions.";
    } catch (\Exception $e) {
        return "ERROR: " . $e->getMessage() . "<br>" . $e->getTraceAsString();
    }
});

Route::get('/purchase-orders/{record}/pdf', function ($record) {
    $po = \App\Models\PurchaseOrder::with(['vendor', 'items.component', 'creator'])->findOrFail($record);
    return view('purchase-orders.pdf', compact('po'));
})->name('purchase-orders.pdf')->middleware(['web', 'auth']);

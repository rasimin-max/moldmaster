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
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*');

Route::get('/components/{component}/qr', [ComponentQrController::class, 'show'])
    ->name('components.qr');

Route::get('/components-bulk-qr', [ComponentQrController::class, 'bulkShow'])
    ->name('components.qr.bulk');

Route::get('/tools/{tool}/qr', [\App\Http\Controllers\ToolQrController::class, 'show'])
    ->name('tools.qr');

Route::get('/purchase-orders/{record}/pdf', function ($record) {
    $po = \App\Models\PurchaseOrder::with(['vendor', 'items.component', 'creator'])->findOrFail($record);
    return view('purchase-orders.pdf', compact('po'));
})->name('purchase-orders.pdf')->middleware(['web', 'auth']);

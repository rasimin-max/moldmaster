<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComponentQrController;

Route::get('/', function () {
    return view('welcome');
});

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

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComponentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\ToolLoanController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard stats (per role)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Components
    Route::get('/components', [ComponentController::class, 'index']);
    Route::get('/components/search', [ComponentController::class, 'search']);
    Route::get('/components/qr/{qrCode}', [ComponentController::class, 'findByQr']);
    Route::get('/components/{id}', [ComponentController::class, 'show']);

    // Stock Movements
    Route::get('/stock-movements', [StockMovementController::class, 'index']);
    Route::post('/stock-movements', [StockMovementController::class, 'store']);
    Route::get('/stock-movements/{id}', [StockMovementController::class, 'show']);
    Route::patch('/stock-movements/{id}/approve', [StockMovementController::class, 'approve']);
    Route::patch('/stock-movements/{id}/reject', [StockMovementController::class, 'reject']);

    // Maintenances
    Route::get('/maintenances', [MaintenanceController::class, 'index']);
    Route::post('/maintenances', [MaintenanceController::class, 'store']);
    Route::get('/maintenances/{id}', [MaintenanceController::class, 'show']);
    Route::patch('/maintenances/{id}/approve', [MaintenanceController::class, 'approve']);
    Route::patch('/maintenances/{id}/complete', [MaintenanceController::class, 'complete']);

    // Tool Loans
    Route::get('/tool-loans', [ToolLoanController::class, 'index']);
    Route::get('/tools', [ToolLoanController::class, 'listTools']);
    Route::post('/tool-loans', [ToolLoanController::class, 'store']);
    Route::patch('/tool-loans/{id}/return', [ToolLoanController::class, 'returnTool']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
});

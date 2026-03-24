<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Users (admin only)
    Route::apiResource('users', UserController::class)->middleware('role:admin');

    // Settings (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index']);
        Route::put('/settings', [SettingController::class, 'update']);
        Route::post('/settings/logo', [SettingController::class, 'uploadLogo']);
        Route::delete('/settings/logo', [SettingController::class, 'deleteLogo']);
    });

    // Categories
    Route::apiResource('categories', CategoryController::class);
    Route::post('/categories/{category}/image', [CategoryController::class, 'uploadImage']);
    Route::delete('/categories/{category}/image', [CategoryController::class, 'deleteImage']);

    // Items
    Route::apiResource('items', ItemController::class);
    Route::patch('/items/{item}/stock', [ItemController::class, 'updateStock']);
    Route::post('/items/{item}/image', [ItemController::class, 'uploadImage']);
    Route::delete('/items/{item}/image', [ItemController::class, 'deleteImage']);

    // Orders
    Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'store']);
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt']);

    // Stock movements
    Route::get('/stock-movements', [StockMovementController::class, 'index']);
    Route::post('/stock-movements', [StockMovementController::class, 'store']);

    // Reports
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly']);
    Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
});

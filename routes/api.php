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

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Items
    Route::apiResource('items', ItemController::class);
    Route::patch('/items/{item}/stock', [ItemController::class, 'updateStock']);

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

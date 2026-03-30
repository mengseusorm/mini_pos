<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');
    Route::get('/pos', fn() => view('pos.index'))->name('pos.index');
    Route::get('/orders', fn() => view('orders.index'))->name('orders.index');
    Route::get('/items', fn() => view('items.index'))->name('items.index');
    Route::get('/categories', fn() => view('categories.index'))->name('categories.index');
    Route::get('/stock', fn() => view('stock.index'))->name('stock.index');
    Route::get('/reports', fn() => view('reports.index'))->name('reports.index');
    Route::get('/users', fn() => view('users.index'))->name('users.index');
    Route::get('/settings', fn() => view('settings.index'))->name('settings.index');
});


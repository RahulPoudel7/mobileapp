<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GiftController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrderController; // add this

// Home (optional)
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Web login/logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'webLogin']);
Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');

// Admin area (protected)
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('gifts', GiftController::class);
        Route::resource('users', UserController::class);
        Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update']);
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

    });

<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Bookstore\Controllers\AuthController;
use App\Modules\Bookstore\Controllers\DashboardController;
use App\Modules\Bookstore\Controllers\POSController;

Route::prefix('bookstore')->name('bookstore.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:bookstore')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/pos', [POSController::class, 'index'])->name('pos');
        Route::post('/sales', [POSController::class, 'store'])->name('sales.store');
    });
});

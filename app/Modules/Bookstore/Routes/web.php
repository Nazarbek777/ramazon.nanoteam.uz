<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Bookstore\Controllers\AuthController;
use App\Modules\Bookstore\Controllers\DashboardController;
use App\Modules\Bookstore\Controllers\POSController;
use App\Modules\Bookstore\Controllers\BookController;
use App\Modules\Bookstore\Controllers\ReportsController;
use App\Modules\Bookstore\Controllers\AnalyticsController;
use App\Modules\Bookstore\Controllers\ArrivalsController;

Route::prefix('bookstore')->name('bookstore.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:bookstore')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/pos', [POSController::class, 'index'])->name('pos');
        Route::post('/sales', [POSController::class, 'store'])->name('sales.store');

        Route::get('/book-find/{barcode}', [POSController::class, 'findBook'])->name('book.find');
        Route::get('/book-search', [POSController::class, 'searchBooks'])->name('book.search');
        Route::get('/books-cache', [POSController::class, 'booksCache'])->name('books.cache');
        Route::post('/sales/offline-sync', [POSController::class, 'offlineSync'])->name('sales.offline-sync');

        // Books CRUD
        Route::get('/books', [BookController::class, 'index'])->name('books.index');
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

        // CRM — Reports
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');

        // CRM — Analytics
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

        // CRM — Arrivals / Inventory ledger
        Route::get('/arrivals', [ArrivalsController::class, 'index'])->name('arrivals');
        Route::post('/arrivals', [ArrivalsController::class, 'store'])->name('arrivals.store');
        Route::delete('/arrivals/{arrival}', [ArrivalsController::class, 'destroy'])->name('arrivals.destroy');
        Route::get('/arrivals/export', [ArrivalsController::class, 'export'])->name('arrivals.export');
    });
});

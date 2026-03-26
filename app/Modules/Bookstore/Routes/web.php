<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Bookstore\Controllers\AuthController;
use App\Modules\Bookstore\Controllers\DashboardController;
use App\Modules\Bookstore\Controllers\POSController;
use App\Modules\Bookstore\Controllers\BookController;

Route::prefix('bookstore')->name('bookstore.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:bookstore')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/pos', [POSController::class, 'index'])->name('pos');
        Route::post('/sales', [POSController::class, 'store'])->name('sales.store');

        // Barcode lookup — web route (session auth guaranteed)
        Route::get('/book-find/{barcode}', [POSController::class, 'findBook'])->name('book.find');

        // Books CRUD
        Route::get('/books', [BookController::class, 'index'])->name('books.index');
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Bookstore\Controllers\POSController;

Route::middleware('auth:bookstore')->group(function () {
    Route::get('/books/{barcode}', [POSController::class, 'findBook'])->name('books.find');
});

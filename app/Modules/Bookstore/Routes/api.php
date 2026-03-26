<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Bookstore\Controllers\POSController;

// No auth middleware here - the page is already guarded by web session.
// This is a simple JSON read-only endpoint.
Route::get('/books/{barcode}', [POSController::class, 'findBook']);

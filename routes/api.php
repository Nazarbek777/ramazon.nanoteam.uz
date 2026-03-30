<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Log::info("[API] Accessing routes/api.php", ['url' => request()->fullUrl(), 'method' => request()->method()]);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/book-bot/webhook', [\App\Modules\Book\Controllers\WebhookController::class, 'handle']);

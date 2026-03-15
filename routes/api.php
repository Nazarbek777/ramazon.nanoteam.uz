<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/book-bot/webhook', [\App\Modules\Book\Controllers\WebhookController::class, 'handle']);

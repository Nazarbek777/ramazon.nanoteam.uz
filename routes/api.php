<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Log::info("[API] Accessing routes/api.php", ['url' => request()->fullUrl(), 'method' => request()->method()]);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/book-bot/webhook', [\App\Modules\Book\Controllers\WebhookController::class, 'handle']);

Route::get('/test-bot', function() {
    try {
        $controller = new \App\Modules\Book\Controllers\WebhookController();
        return response()->json([
            'ok' => true,
            'message' => 'Controller logic is operational',
            'scout_installed' => class_exists('Laravel\Scout\Searchable')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

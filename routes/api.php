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
        $meiliHost = env('MEILISEARCH_HOST', 'http://127.0.0.1:7700');
        $meiliStatus = false;
        $meiliSearchTest = null;
        try {
            $health = \Illuminate\Support\Facades\Http::timeout(2)
                ->withHeaders(['Authorization' => 'Bearer ' . env('MEILISEARCH_KEY', 'masterKey')])
                ->get($meiliHost . '/health');
            $meiliStatus = $health->json();
            
            $search = \Illuminate\Support\Facades\Http::timeout(2)
                ->withHeaders(['Authorization' => 'Bearer ' . env('MEILISEARCH_KEY', 'masterKey')])
                ->post($meiliHost . '/indexes/bookstore_books/search', ['q' => 'Binafsha', 'limit' => 1]);
            $meiliSearchTest = $search->json();
        } catch (\Exception $e) {
            $meiliStatus = "Error: " . $e->getMessage();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Controller logic is operational',
            'scout_installed' => class_exists('Laravel\Scout\Searchable'),
            'meilisearch_connection' => $meiliStatus,
            'meilisearch_search_test' => $meiliSearchTest
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

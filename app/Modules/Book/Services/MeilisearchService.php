<?php

namespace App\Modules\Book\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MeilisearchService
{
    protected string $host;
    protected string $key;
    protected string $index = 'bookstore_books';

    public function __construct()
    {
        $this->host = rtrim(env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'), '/');
        $this->key = env('MEILISEARCH_KEY', 'masterKey');
    }

    public function search(string $query, int $limit = 10): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->key}"
            ])->post("{$this->host}/indexes/{$this->index}/search", [
                'q' => $query,
                'limit' => $limit
            ]);

            if ($response->successful()) {
                return $response->json()['hits'] ?? [];
            }

            Log::error("[Meili] Search Failed: " . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error("[Meili] Search Exception: " . $e->getMessage());
            return [];
        }
    }

    public function syncDocuments(array $documents): bool
    {
        try {
            // Index yaratish (agar yo'q bo'lsa)
            Http::withHeaders(['Authorization' => "Bearer {$this->key}"])
                ->post("{$this->host}/indexes", ['uid' => $this->index, 'primaryKey' => 'id']);

            // Ma'lumotlarni yuborish
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->key}"
            ])->post("{$this->host}/indexes/{$this->index}/documents", $documents);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("[Meili] Sync Exception: " . $e->getMessage());
            return false;
        }
    }
}

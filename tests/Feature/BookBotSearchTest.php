<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Modules\Bookstore\Models\Book as BookstoreBook;
use App\Modules\Book\Models\BookUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class BookBotSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_bot_responds_to_search_query()
    {
        // 1. Arrange: Create a book in the bookstore
        BookstoreBook::create([
            'title' => 'Sariq devni minib',
            'author' => 'Xudoyberdi To\'xtaboyev',
            'price' => 50000,
            'stock' => 10,
        ]);

        // Mock Telegram API
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        // 2. Act: Send a webhook request with a search query
        $response = $this->postJson('/telegram/book-bot-webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'from' => ['id' => 123456, 'first_name' => 'Test User'],
                'text' => 'Sariq dev',
            ],
        ]);

        // 3. Assert
        $response->assertStatus(200);
        
        // Check if Http::post was called with the search result
        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Sariq devni minib') &&
                   str_contains($request['text'], 'Xudoyberdi');
        });
    }

    public function test_bot_start_message()
    {
        // Mock Telegram API
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $response = $this->postJson('/telegram/book-bot-webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'from' => ['id' => 123456, 'first_name' => 'Test User'],
                'text' => '/start',
            ],
        ]);

        $response->assertStatus(200);
        
        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'rasmiy botiga xush kelibsiz');
        });
    }
}

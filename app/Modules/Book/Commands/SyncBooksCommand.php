<?php

namespace App\Modules\Book\Commands;

use Illuminate\Console\Command;
use App\Modules\Book\Services\BookService;

class SyncBooksCommand extends Command
{
    protected $signature = 'book:sync';
    protected $description = 'Sync bookstore books to Meilisearch';

    public function handle(BookService $bookService)
    {
        $this->info('Starting book sync to Meilisearch...');
        
        if ($bookService->syncBooks()) {
            $this->info('Successfully synced books to Meilisearch!');
        } else {
            $this->error('Failed to sync books to Meilisearch.');
        }

        return 0;
    }
}

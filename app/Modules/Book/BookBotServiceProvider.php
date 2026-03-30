<?php

namespace App\Modules\Book;

use Illuminate\Support\ServiceProvider;

class BookBotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No external packages needed — using vanilla Laravel Http facade.
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Modules\Book\Commands\BroadcastCommand::class,
                \App\Modules\Book\Commands\SyncBooksCommand::class,
            ]);
        }
    }
}

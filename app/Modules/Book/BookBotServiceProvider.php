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
        //
    }
}

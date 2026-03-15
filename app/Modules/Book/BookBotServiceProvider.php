<?php

namespace App\Modules\Book;

use Illuminate\Support\ServiceProvider;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Configuration;

class BookBotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Nutgram::class, function ($app) {
            $config = new Configuration(
                container: $app,
            );
            return new Nutgram(\App\Modules\Book\BotManager::TOKEN, $config);
        });
    }

    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Modules\Bookstore;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class BookstoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        
        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
            
        Route::middleware('api')
            ->prefix('api/bookstore')
            ->group(__DIR__ . '/Routes/api.php');
    }
}

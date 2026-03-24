<?php

namespace App\Modules\Contest;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Contest\Models\ContestBot;
use App\Modules\Contest\Models\Contest;
use App\Modules\Contest\Models\ContestChannel;
use App\Modules\Contest\Models\ContestKeyword;

class ContestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::model('bot', ContestBot::class);
        // Laravel will auto-bind {contest}, {channel}, {keyword} by convention
    }
}

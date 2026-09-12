<?php

namespace App\Modules\Parvoz;

use Illuminate\Support\ServiceProvider;

class ParvozServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Modellar konvensiya bo'yicha avtomatik bog'lanadi ({group}, {student}, ...)
    }
}

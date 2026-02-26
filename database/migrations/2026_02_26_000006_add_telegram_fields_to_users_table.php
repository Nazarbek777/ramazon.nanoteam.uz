<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->string('telegram_id')->nullable()->unique();
            $blueprint->string('phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function run_reverse(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['telegram_id', 'phone_number']);
        });
    }
};

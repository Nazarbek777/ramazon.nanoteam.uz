<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_bots', function (Blueprint $table) {
            $table->boolean('webhook_set')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('contest_bots', function (Blueprint $table) {
            $table->dropColumn('webhook_set');
        });
    }
};

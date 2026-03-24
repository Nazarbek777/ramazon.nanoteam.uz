<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_keywords', function (Blueprint $table) {
            $table->boolean('is_menu_button')->default(false)->after('response_photo');
            $table->string('action')->nullable()->after('is_menu_button');
            $table->integer('sort_order')->default(0)->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('contest_keywords', function (Blueprint $table) {
            $table->dropColumn(['is_menu_button', 'action', 'sort_order']);
        });
    }
};

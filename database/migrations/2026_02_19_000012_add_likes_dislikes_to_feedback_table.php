<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback', 'likes_count')) {
                $table->integer('likes_count')->default(0)->after('ip_address');
            }
            if (!Schema::hasColumn('feedback', 'dislikes_count')) {
                $table->integer('dislikes_count')->default(0)->after('likes_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn(['likes_count', 'dislikes_count']);
        });
    }
};

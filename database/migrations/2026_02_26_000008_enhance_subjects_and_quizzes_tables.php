<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('subjects')->onDelete('cascade');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->integer('random_questions_count')->nullable()->after('is_random');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('random_questions_count');
        });
    }
};

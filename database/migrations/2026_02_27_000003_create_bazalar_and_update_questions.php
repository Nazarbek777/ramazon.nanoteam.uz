<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create bazalar table — separate from subjects
        Schema::create('bazalar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade'); // belongs to a fan
            $table->unsignedBigInteger('parent_id')->nullable();   // for nested bazalar
            $table->foreign('parent_id')->references('id')->on('bazalar')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // Add baza_id to questions (nullable for backward compat)
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('baza_id')->nullable()->after('subject_id')
                ->constrained('bazalar')->onDelete('set null');
        });

        // Remove old quiz_sources and recreate with baza_id
        Schema::dropIfExists('quiz_sources');
        Schema::create('quiz_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('baza_id')->constrained('bazalar')->onDelete('cascade');
            $table->unsignedInteger('count')->default(10);
            $table->timestamps();
            $table->unique(['quiz_id', 'baza_id']);
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['baza_id']);
            $table->dropColumn('baza_id');
        });
        Schema::dropIfExists('quiz_sources');
        Schema::dropIfExists('bazalar');
    }
};

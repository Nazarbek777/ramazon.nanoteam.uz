<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('count')->default(10); // nechta savol olinsin
            $table->timestamps();
            $table->unique(['quiz_id', 'subject_id']); // bitta quizdan bitta baza bir marta
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_sources');
    }
};

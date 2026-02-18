<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_log_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->decimal('value', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['daily_log_id', 'habit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_log_items');
    }
};

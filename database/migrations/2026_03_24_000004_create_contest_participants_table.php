<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained('contests')->cascadeOnDelete();
            $table->bigInteger('telegram_id');
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('referrer_id')->nullable()->constrained('contest_participants')->nullOnDelete();
            $table->integer('referral_count')->default(0);
            $table->integer('points')->default(0);
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->boolean('is_registered')->default(false);
            $table->timestamps();

            $table->unique(['contest_id', 'telegram_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_participants');
    }
};

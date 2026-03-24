<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_bot_id')->constrained('contest_bots')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('start_text')->nullable();
            $table->text('rules_text')->nullable();
            $table->text('afisha_photo')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('require_phone')->default(true);
            $table->boolean('require_channel_join')->default(true);
            $table->boolean('require_referral')->default(false);
            $table->integer('referral_points')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};

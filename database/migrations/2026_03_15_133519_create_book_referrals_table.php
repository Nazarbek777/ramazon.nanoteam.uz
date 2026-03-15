<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('book_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_id');
            $table->integer('points_earned')->default(0);
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('book_users')->onDelete('cascade');
            $table->foreign('referred_id')->references('id')->on('book_users')->onDelete('cascade');
            $table->unique(['referrer_id', 'referred_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_referrals');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookstore_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookstore_user_id')->constrained('bookstore_users');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('payment_method')->default('cash'); // cash, card, click, payme
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookstore_sales');
    }
};

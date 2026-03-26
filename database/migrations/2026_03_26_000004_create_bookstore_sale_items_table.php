<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookstore_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('bookstore_sales')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('bookstore_books');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookstore_sale_items');
    }
};

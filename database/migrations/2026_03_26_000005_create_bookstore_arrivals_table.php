<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add cost_price to books
        Schema::table('bookstore_books', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
        });

        // Book arrivals / purchase ledger
        Schema::create('bookstore_arrivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('bookstore_books')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('cost_price', 12, 2);  // purchase price per unit at time of arrival
            $table->decimal('total_cost', 12, 2);   // quantity * cost_price
            $table->string('supplier')->nullable();
            $table->text('note')->nullable();
            $table->date('arrived_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookstore_arrivals');
        Schema::table('bookstore_books', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};

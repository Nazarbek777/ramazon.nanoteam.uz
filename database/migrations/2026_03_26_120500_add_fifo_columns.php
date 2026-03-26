<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookstore_arrivals', function (Blueprint $table) {
            $table->integer('remaining_stock')->default(0);
        });
 
        Schema::table('bookstore_sale_items', function (Blueprint $table) {
            $table->decimal('cost_price', 15, 2)->default(0)->after('unit_price');
        });
    }
 
    public function down(): void
    {
        Schema::table('bookstore_arrivals', function (Blueprint $table) {
            $table->dropColumn('remaining_stock');
        });
 
        Schema::table('bookstore_sale_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};

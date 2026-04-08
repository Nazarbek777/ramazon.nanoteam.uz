<?php
/*
 * (c) Nanoteam Bookstore
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookstore_sales', function (Blueprint $blueprint) {
            $blueprint->boolean('is_delivery')->default(false)->after('payment_method');
            $blueprint->string('status')->default('paid')->after('is_delivery'); // paid, pending
            $blueprint->string('customer_name')->nullable()->after('status');
            $blueprint->string('customer_phone')->nullable()->after('customer_name');
            $blueprint->text('address')->nullable()->after('customer_phone');
            $blueprint->decimal('delivery_fee', 15, 2)->default(0)->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('bookstore_sales', function (Blueprint $blueprint) {
            $blueprint->dropColumn([
                'is_delivery',
                'status',
                'customer_name',
                'customer_phone',
                'address',
                'delivery_fee'
            ]);
        });
    }
};

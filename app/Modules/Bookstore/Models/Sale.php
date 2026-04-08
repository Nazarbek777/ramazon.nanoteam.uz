<?php

namespace App\Modules\Bookstore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $table = 'bookstore_sales';

    protected $fillable = [
        'bookstore_user_id',
        'total_amount',
        'discount',
        'payment_method',
        'is_delivery',
        'status',
        'customer_name',
        'customer_phone',
        'address',
        'delivery_fee',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(BookstoreUser::class, 'bookstore_user_id');
    }
}

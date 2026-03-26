<?php

namespace App\Modules\Bookstore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $table = 'bookstore_sale_items';

    protected $fillable = [
        'sale_id',
        'book_id',
        'quantity',
        'unit_price',
        'cost_price',
        'total_price',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}

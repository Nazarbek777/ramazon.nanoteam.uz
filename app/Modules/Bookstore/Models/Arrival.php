<?php

namespace App\Modules\Bookstore\Models;

use Illuminate\Database\Eloquent\Model;

class Arrival extends Model
{
    protected $table = 'bookstore_arrivals';

    protected $fillable = [
        'book_id', 'quantity', 'cost_price', 'total_cost',
        'supplier', 'note', 'arrived_at',
    ];

    protected $casts = [
        'arrived_at' => 'date',
        'cost_price' => 'float',
        'total_cost' => 'float',
        'quantity'   => 'integer',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}

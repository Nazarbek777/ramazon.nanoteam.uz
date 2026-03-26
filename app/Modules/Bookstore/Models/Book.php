<?php

namespace App\Modules\Bookstore\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'bookstore_books';

    protected $fillable = [
        'title',
        'author',
        'barcode',
        'price',
        'cost_price',
        'stock',
    ];
}

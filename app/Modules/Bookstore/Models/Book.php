<?php

namespace App\Modules\Bookstore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;
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

<?php

namespace App\Modules\Book\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'book_books';

    protected $fillable = [
        'title',
        'author',
        'description',
        'price',
        'image',
        'stock',
    ];
}

<?php

namespace App\Modules\Bookstore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Book extends Model
{
    use SoftDeletes, Searchable;
    protected $table = 'bookstore_books';

    protected $fillable = [
        'title',
        'author',
        'barcode',
        'price',
        'cost_price',
        'stock',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'author' => $this->author,
        ];
    }
}

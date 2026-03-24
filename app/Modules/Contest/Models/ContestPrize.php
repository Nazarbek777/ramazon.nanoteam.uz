<?php

namespace App\Modules\Contest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestPrize extends Model
{
    protected $table = 'contest_prizes';

    protected $fillable = [
        'contest_id',
        'title',
        'description',
        'image',
        'points_required',
        'sort_order',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class, 'contest_id');
    }
}

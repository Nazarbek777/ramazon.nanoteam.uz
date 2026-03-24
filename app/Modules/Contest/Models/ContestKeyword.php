<?php

namespace App\Modules\Contest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestKeyword extends Model
{
    protected $table = 'contest_keywords';

    protected $fillable = [
        'contest_id',
        'keyword',
        'response_text',
        'response_photo',
        'is_menu_button',
        'action',
        'sort_order',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class, 'contest_id');
    }
}

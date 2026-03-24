<?php

namespace App\Modules\Contest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestChannel extends Model
{
    protected $table = 'contest_channels';

    protected $fillable = [
        'contest_id',
        'channel_id',
        'channel_name',
        'channel_url',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class, 'contest_id');
    }
}

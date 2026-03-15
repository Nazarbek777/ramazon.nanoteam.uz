<?php

namespace App\Modules\Book\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $table = 'book_referrals';

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'points_earned',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(BookUser::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(BookUser::class, 'referred_id');
    }
}

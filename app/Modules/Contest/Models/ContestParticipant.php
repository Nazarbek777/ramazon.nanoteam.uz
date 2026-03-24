<?php

namespace App\Modules\Contest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContestParticipant extends Model
{
    protected $table = 'contest_participants';

    protected $fillable = [
        'contest_id',
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'phone',
        'referrer_id',
        'referral_count',
        'points',
        'status',
        'is_registered',
    ];

    protected $casts = [
        'is_registered' => 'boolean',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class, 'contest_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(ContestParticipant::class, 'referrer_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(ContestParticipant::class, 'referrer_id');
    }
}

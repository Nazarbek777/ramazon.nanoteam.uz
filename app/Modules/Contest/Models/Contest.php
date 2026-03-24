<?php

namespace App\Modules\Contest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    protected $table = 'contests';

    protected $fillable = [
        'contest_bot_id',
        'title',
        'description',
        'start_text',
        'rules_text',
        'afisha_photo',
        'start_date',
        'end_date',
        'is_active',
        'require_phone',
        'require_channel_join',
        'require_referral',
        'referral_points',
        'referral_text',
        'referral_button_text',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'require_phone' => 'boolean',
        'require_channel_join' => 'boolean',
        'require_referral' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function bot(): BelongsTo
    {
        return $this->belongsTo(ContestBot::class, 'contest_bot_id');
    }

    public function channels(): HasMany
    {
        return $this->hasMany(ContestChannel::class, 'contest_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ContestParticipant::class, 'contest_id');
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(ContestKeyword::class, 'contest_id');
    }
}

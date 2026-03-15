<?php

namespace App\Modules\Book\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookUser extends Model
{
    protected $table = 'book_users';

    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'phone',
        'points',
        'referrer_id',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(BookUser::class, 'referrer_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(BookUser::class, 'referrer_id');
    }

    public function referralLogs(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }
}

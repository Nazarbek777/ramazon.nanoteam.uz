<?php

namespace App\Modules\Contest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContestBot extends Model
{
    protected $table = 'contest_bots';

    protected $fillable = [
        'name',
        'username',
        'token',
        'webhook_secret',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class, 'contest_bot_id');
    }

    public function activeContest()
    {
        return $this->contests()->where('is_active', true)->latest()->first();
    }
}

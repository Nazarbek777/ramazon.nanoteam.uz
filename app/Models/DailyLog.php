<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyLog extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'notes',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyLogItem::class);
    }

    public function getCompletedCountAttribute(): int
    {
        return $this->items->where('is_completed', true)->count();
    }

    public function getTotalCountAttribute(): int
    {
        return $this->items->count();
    }

    public function getCompletionPercentAttribute(): float
    {
        if ($this->total_count === 0) return 0;
        return round(($this->completed_count / $this->total_count) * 100, 1);
    }
}

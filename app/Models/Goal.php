<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'target_value',
        'current_value',
        'unit',
        'habit_id',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->target_value == 0) return 100;
        return min(100, round(($this->current_value / $this->target_value) * 100, 1));
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->target_value - $this->current_value);
    }
}

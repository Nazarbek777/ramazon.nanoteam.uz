<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLogItem extends Model
{
    protected $fillable = [
        'daily_log_id',
        'habit_id',
        'is_completed',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'value' => 'decimal:2',
        ];
    }

    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class);
    }

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }
}

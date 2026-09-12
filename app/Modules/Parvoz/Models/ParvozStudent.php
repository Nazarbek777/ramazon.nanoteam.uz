<?php

namespace App\Modules\Parvoz\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParvozStudent extends Model
{
    protected $table = 'parvoz_students';

    protected $fillable = ['parvoz_group_id', 'full_name', 'phone', 'telegram_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ParvozGroup::class, 'parvoz_group_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(ParvozGrade::class, 'parvoz_student_id');
    }

    /** O'rtacha ball (foizda, agar max_score bo'lsa) */
    public function averagePercent(): ?float
    {
        $rows = $this->grades()->whereNotNull('max_score')->where('max_score', '>', 0)->get();
        if ($rows->isEmpty()) return null;

        return round($rows->avg(fn ($g) => $g->score / $g->max_score * 100), 1);
    }

    /** O'rtacha ball (xom ball) */
    public function averageScore(): ?float
    {
        $avg = $this->grades()->avg('score');
        return $avg === null ? null : round((float) $avg, 2);
    }
}

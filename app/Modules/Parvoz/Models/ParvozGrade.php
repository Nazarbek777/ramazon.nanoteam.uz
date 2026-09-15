<?php

namespace App\Modules\Parvoz\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParvozGrade extends Model
{
    protected $table = 'parvoz_grades';

    protected $fillable = [
        'parvoz_student_id',
        'parvoz_group_id',
        'parvoz_teacher_id',
        'parvoz_subject_id',
        'score',
        'max_score',
        'comment',
        'graded_at',
    ];

    protected $casts = [
        'score'     => 'decimal:2',
        'max_score' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(ParvozStudent::class, 'parvoz_student_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ParvozGroup::class, 'parvoz_group_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(ParvozTeacher::class, 'parvoz_teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ParvozSubject::class, 'parvoz_subject_id');
    }

    /** "9" yoki "9/10" ko'rinishida */
    public function scoreLabel(): string
    {
        $score = rtrim(rtrim(number_format((float) $this->score, 2, '.', ''), '0'), '.');

        if ($this->max_score) {
            $max = rtrim(rtrim(number_format((float) $this->max_score, 2, '.', ''), '0'), '.');
            return "{$score}/{$max}";
        }

        return $score;
    }
}

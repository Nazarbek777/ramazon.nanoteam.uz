<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id', 
        'quiz_id', 
        'score', 
        'total_questions', 
        'correct_answers', 
        'started_at', 
        'completed_at', 
        'questions_order'
    ];

    protected $casts = [
        'questions_order' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(\App\Models\AttemptAnswer::class, 'quiz_attempt_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSource extends Model
{
    protected $fillable = ['quiz_id', 'subject_id', 'count'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}

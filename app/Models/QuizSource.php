<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSource extends Model
{
    protected $fillable = ['quiz_id', 'baza_id', 'count'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function baza()
    {
        return $this->belongsTo(Baza::class);
    }
}

<?php

namespace App\Modules\Parvoz\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParvozSubject extends Model
{
    protected $table = 'parvoz_subjects';

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function grades(): HasMany
    {
        return $this->hasMany(ParvozGrade::class, 'parvoz_subject_id');
    }
}

<?php

namespace App\Modules\Parvoz\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParvozTeacher extends Model
{
    protected $table = 'parvoz_teachers';

    protected $fillable = ['full_name', 'phone', 'telegram_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            ParvozGroup::class,
            'parvoz_group_teacher',
            'parvoz_teacher_id',
            'parvoz_group_id'
        );
    }

    public function grades(): HasMany
    {
        return $this->hasMany(ParvozGrade::class, 'parvoz_teacher_id');
    }
}

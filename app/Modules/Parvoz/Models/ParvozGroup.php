<?php

namespace App\Modules\Parvoz\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParvozGroup extends Model
{
    protected $table = 'parvoz_groups';

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function students(): HasMany
    {
        return $this->hasMany(ParvozStudent::class, 'parvoz_group_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            ParvozTeacher::class,
            'parvoz_group_teacher',
            'parvoz_group_id',
            'parvoz_teacher_id'
        );
    }
}

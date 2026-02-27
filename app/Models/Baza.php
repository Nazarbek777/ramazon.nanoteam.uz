<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Baza extends Model
{
    protected $table = 'bazalar';

    protected $fillable = ['subject_id', 'parent_id', 'name'];

    /** Parent fan */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** Parent baza (if nested) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Baza::class, 'parent_id');
    }

    /** Child bazalar */
    public function children(): HasMany
    {
        return $this->hasMany(Baza::class, 'parent_id');
    }

    /** Questions in this baza */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /** How deep this baza is (for display) */
    public function getDepthAttribute(): int
    {
        if (!$this->parent_id) return 0;
        return ($this->parent->depth ?? 0) + 1;
    }
}

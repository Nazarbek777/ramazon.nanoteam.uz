<?php

namespace App\Modules\Parvoz\Models;

use Illuminate\Database\Eloquent\Model;

class ParvozBot extends Model
{
    protected $table = 'parvoz_bots';

    protected $fillable = [
        'name',
        'username',
        'token',
        'webhook_secret',
        'webhook_set',
        'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'webhook_set' => 'boolean',
    ];
}

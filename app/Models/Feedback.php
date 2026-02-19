<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'is_public',
        'is_approved',
        'ip_address'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_approved' => 'boolean'
    ];
}

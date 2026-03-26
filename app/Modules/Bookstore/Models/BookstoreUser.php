<?php

namespace App\Modules\Bookstore\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class BookstoreUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'bookstore_users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

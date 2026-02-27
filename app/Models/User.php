<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'telegram_id', 'phone_number', 'is_blocked',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_blocked'        => 'boolean',
        ];
    }

    // ─── Role helpers ───────────────────────────────
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }

    /**
     * Check if this admin has permission for a page/action.
     * super_admin always has access.
     * $permission can be:
     *   'subjects'        → any access to subjects
     *   'subjects.create' → only create action
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) return true;
        // Check exact permission OR wildcard page
        $page = explode('.', $permission)[0];
        return $this->permissions()->whereIn('page', [$permission, $page])->exists();
    }

    // ─── Relationships ───────────────────────────────
    public function permissions()
    {
        return $this->hasMany(\App\Models\AdminPermission::class, 'admin_id');
    }

    public function attempts()
    {
        return $this->hasMany(\App\Models\QuizAttempt::class);
    }
}

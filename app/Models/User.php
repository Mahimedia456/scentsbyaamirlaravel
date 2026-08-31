<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'password_changed_at',
        'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'must_change_password' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return array_key_exists((string) $this->role, config('admin_roles.permissions', []))
            && $this->is_active;
    }

    public function canAdmin(string $permission): bool
    {
        if (!$this->is_active) return false;

        $permissions = config('admin_roles.permissions.' . $this->role, []);

        return in_array('*', $permissions, true)
            || in_array($permission, $permissions, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' && $this->is_active;
    }
}

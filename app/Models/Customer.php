<?php

namespace App\Models;

use App\Notifications\CustomerPasswordResetNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'marketing_opt_in' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_order_at' => 'datetime',
            'admin_archived_at' => 'datetime',
            'address' => 'array',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomerPasswordResetNotification((string) $token));
    }
}

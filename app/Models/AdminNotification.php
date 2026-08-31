<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function scopeVisible($query)
    {
        return $query
            ->whereNull('dismissed_at')
            ->whereNull('resolved_at');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}

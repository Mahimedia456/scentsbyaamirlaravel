<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalPost extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'wordpress_modified_at' => 'datetime',
            'wordpress_categories' => 'array',
            'wordpress_tags' => 'array',
            'imported_at' => 'datetime',
        ];
    }
}

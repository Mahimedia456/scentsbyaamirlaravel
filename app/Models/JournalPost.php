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
            'imported_at' => 'datetime',
            'categories' => 'array',
            'tags' => 'array',
        ];
    }

    public function isWordPressImport(): bool
    {
        return $this->source === 'wordpress' && (bool) $this->wordpress_id;
    }
}

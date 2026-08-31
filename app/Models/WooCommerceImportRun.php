<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommerceImportRun extends Model
{
    protected $table = 'woocommerce_import_runs';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'summary' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function maps()
    {
        return $this->hasMany(WooCommerceImportMap::class, 'run_id');
    }
}

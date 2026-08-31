<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommerceImportMap extends Model
{
    protected $table = 'woocommerce_import_maps';
    protected $guarded = [];

    public function run()
    {
        return $this->belongsTo(WooCommerceImportRun::class, 'run_id');
    }
}

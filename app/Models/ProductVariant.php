<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id','name','sku','size_label','price','compare_at_price','stock','is_active','sort_order'];
    protected $casts = ['price'=>'decimal:2','compare_at_price'=>'decimal:2','stock'=>'integer','is_active'=>'boolean','sort_order'=>'integer'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

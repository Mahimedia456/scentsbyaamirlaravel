<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id','name','slug','subtitle','description','story','notes','wear','status','is_featured','base_price','compare_at_price','stock','stock_quantity','track_inventory','is_in_stock','size_label','sku','meta_title','meta_description'
    ];

    protected $casts = [
        'is_featured'=>'boolean','base_price'=>'decimal:2','compare_at_price'=>'decimal:2','stock'=>'integer','stock_quantity'=>'integer','track_inventory'=>'boolean','is_in_stock'=>'boolean'
    ];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function collections(): BelongsToMany { return $this->belongsToMany(Collection::class); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class)->orderBy('sort_order'); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
}

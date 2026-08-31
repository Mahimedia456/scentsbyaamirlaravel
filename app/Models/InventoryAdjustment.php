<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryAdjustment extends Model { protected $guarded=[]; public function product(){return $this->belongsTo(Product::class);} public function variant(){return $this->belongsTo(ProductVariant::class,'product_variant_id');} public function user(){return $this->belongsTo(User::class);} }

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Coupon extends Model { protected $guarded=[]; protected function casts():array{return ['value'=>'decimal:2','minimum_order'=>'decimal:2','maximum_discount'=>'decimal:2','is_active'=>'boolean','starts_at'=>'datetime','ends_at'=>'datetime','product_ids'=>'array','collection_ids'=>'array'];} public function usages(){return $this->hasMany(CouponUsage::class);} public function isCurrentlyValid():bool{return $this->is_active && (!$this->starts_at || now()->gte($this->starts_at)) && (!$this->ends_at || now()->lte($this->ends_at)) && (!$this->usage_limit || $this->used_count < $this->usage_limit);} }

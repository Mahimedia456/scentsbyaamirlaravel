<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model { protected $guarded=[]; protected function casts():array{return ['subtotal'=>'decimal:2','discount_total'=>'decimal:2','shipping_total'=>'decimal:2','grand_total'=>'decimal:2','shipping_address'=>'array','billing_address'=>'array','placed_at'=>'datetime','fulfilled_at'=>'datetime','payment_verified_at'=>'datetime','gift_wrap'=>'boolean','gift_wrap_total'=>'decimal:2'];} public function customer(){return $this->belongsTo(Customer::class);} public function items(){return $this->hasMany(OrderItem::class);} public function couponUsages(){return $this->hasMany(CouponUsage::class);} }

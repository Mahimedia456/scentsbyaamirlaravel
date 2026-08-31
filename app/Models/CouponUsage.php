<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CouponUsage extends Model { protected $guarded=[]; protected function casts():array{return ['discount_amount'=>'decimal:2'];} public function coupon(){return $this->belongsTo(Coupon::class);} public function customer(){return $this->belongsTo(Customer::class);} public function order(){return $this->belongsTo(Order::class);} }

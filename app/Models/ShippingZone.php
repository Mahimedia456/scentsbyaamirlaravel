<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShippingZone extends Model { protected $guarded=[]; protected function casts():array{return ['active'=>'boolean','base_rate'=>'decimal:2','free_shipping_over'=>'decimal:2'];} }

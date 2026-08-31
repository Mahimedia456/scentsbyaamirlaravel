<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentMethod extends Model { protected $guarded=[]; protected function casts():array{return ['enabled'=>'boolean','test_mode'=>'boolean','config'=>'array'];} }

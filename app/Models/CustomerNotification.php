<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerNotification extends Model {
    protected $guarded=[];
    protected function casts():array{return ['data'=>'array','read_at'=>'datetime'];}
    public function customer(){return $this->belongsTo(Customer::class);}
    public function order(){return $this->belongsTo(Order::class);}
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NavigationItem extends Model { protected $guarded=[]; protected function casts():array{return ['is_active'=>'boolean'];} public function navigation(){return $this->belongsTo(Navigation::class);} public function parent(){return $this->belongsTo(self::class,'parent_id');} public function children(){return $this->hasMany(self::class,'parent_id')->orderBy('sort_order');} }

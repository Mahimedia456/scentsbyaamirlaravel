<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
class PromotionController extends Controller
{
    public function validateCode(Request $r){$d=$r->validate(['code'=>['required','string','max:80'],'subtotal'=>['required','numeric','min:0']]);$coupon=Coupon::where('code',strtoupper(trim($d['code'])))->first();if(!$coupon||!$coupon->isCurrentlyValid())return response()->json(['valid'=>false,'message'=>'Coupon is invalid or expired.'],422);if($coupon->minimum_order!==null&&(float)$d['subtotal']<(float)$coupon->minimum_order)return response()->json(['valid'=>false,'message'=>'Minimum order requirement not met.'],422);$discount=$coupon->type==='percentage'?(float)$d['subtotal']*((float)$coupon->value/100):(float)$coupon->value;if($coupon->maximum_discount!==null)$discount=min($discount,(float)$coupon->maximum_discount);$discount=min($discount,(float)$d['subtotal']);return response()->json(['valid'=>true,'code'=>$coupon->code,'discount'=>round($discount,2),'type'=>$coupon->type,'value'=>$coupon->value]);}
}

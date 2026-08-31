<?php
namespace App\Http\Controllers\Api\V1; use App\Http\Controllers\Controller; use App\Models\StoreSetting; use App\Models\ShippingZone; use App\Models\PaymentMethod;
class StoreConfigController extends Controller { public function __invoke(){return response()->json(['settings'=>StoreSetting::whereIn('key',['store_name','currency','support_email','support_phone'])->pluck('value','key'),'shipping'=>ShippingZone::where('active',1)->get(),'payments'=>PaymentMethod::where('enabled',1)->orderBy('sort_order')->get(['code','name'])]);} }

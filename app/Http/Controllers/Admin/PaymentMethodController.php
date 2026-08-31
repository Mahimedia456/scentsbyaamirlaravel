<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        if (!PaymentMethod::count()) {
            foreach ([['cod','Cash on Delivery',1],['bank_transfer','Bank Transfer',0]] as [$code,$name,$enabled]) {
                PaymentMethod::create(['code'=>$code,'name'=>$name,'enabled'=>$enabled,'sort_order'=>$code==='cod'?10:20]);
            }
        }
        return view('admin.payments.index',['methods'=>PaymentMethod::whereIn('code',['cod','bank_transfer'])->orderBy('sort_order')->get()]);
    }

    public function update(Request $request, PaymentMethod $payment)
    {
        abort_unless(in_array($payment->code, ['cod','bank_transfer'], true), 404);
        $config = $payment->config ?: [];
        if ($payment->code === 'bank_transfer') {
            $validated = $request->validate([
                'bank_name'=>'nullable|string|max:160','account_title'=>'nullable|string|max:160',
                'account_number'=>'nullable|string|max:120','iban'=>'nullable|string|max:120','instructions'=>'nullable|string|max:1000',
            ]);
            $config = $validated;
        }
        $payment->update(['enabled'=>$request->boolean('enabled'),'test_mode'=>false,'config'=>$config]);
        return back()->with('success','Payment method updated.');
    }
}

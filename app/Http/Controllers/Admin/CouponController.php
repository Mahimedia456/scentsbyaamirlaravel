<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $r){$coupons=Coupon::withCount('usages')->when($r->filled('q'),fn($q)=>$q->where('code','like','%'.$r->q.'%')->orWhere('name','like','%'.$r->q.'%'))->latest()->paginate(25)->withQueryString();return view('admin.coupons.index',compact('coupons'));}
    public function create(){return view('admin.coupons.form',['coupon'=>new Coupon]);}
    public function edit(Coupon $coupon){return view('admin.coupons.form',compact('coupon'));}
    public function store(Request $r){Coupon::create($this->validated($r));return redirect()->route('admin.coupons.index')->with('success','Coupon created.');}
    public function update(Request $r,Coupon $coupon){$coupon->update($this->validated($r,$coupon->id));return redirect()->route('admin.coupons.index')->with('success','Coupon updated.');}
    public function destroy(Coupon $coupon){$coupon->delete();return back()->with('success','Coupon deleted.');}
    private function validated(Request $r,?int $id=null):array{$d=$r->validate(['code'=>['required','string','max:80',Rule::unique('coupons','code')->ignore($id)],'name'=>['nullable','string','max:160'],'type'=>['required',Rule::in(['percentage','fixed'])],'value'=>['required','numeric','min:0'],'minimum_order'=>['nullable','numeric','min:0'],'maximum_discount'=>['nullable','numeric','min:0'],'usage_limit'=>['nullable','integer','min:1'],'usage_limit_per_customer'=>['nullable','integer','min:1'],'starts_at'=>['nullable','date'],'ends_at'=>['nullable','date','after_or_equal:starts_at']]);$d['code']=strtoupper(trim($d['code']));$d['is_active']=$r->boolean('is_active');return $d;}
}

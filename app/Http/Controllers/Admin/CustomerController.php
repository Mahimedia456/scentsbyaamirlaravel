<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request){$customers=Customer::withCount('orders')->withSum('orders','grand_total')->when($request->filled('q'),fn($q)=>$q->where(fn($x)=>$x->where('first_name','like','%'.$request->q.'%')->orWhere('last_name','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%')))->latest()->paginate(25)->withQueryString();return view('admin.customers.index',compact('customers'));}
    public function create(){return view('admin.customers.form',['customer'=>new Customer]);}
    public function store(Request $request){Customer::create($this->validated($request));return redirect()->route('admin.customers.index')->with('success','Customer created.');}
    public function show(Customer $customer){$customer->load(['orders'=>fn($q)=>$q->latest()->limit(20)]);return view('admin.customers.show',compact('customer'));}
    public function edit(Customer $customer){return view('admin.customers.form',compact('customer'));}
    public function update(Request $request, Customer $customer){$customer->update($this->validated($request,$customer->id));return redirect()->route('admin.customers.show',$customer)->with('success','Customer updated.');}
    public function destroy(Customer $customer){$customer->update(['is_active'=>false]);return back()->with('success','Customer deactivated.');}
    private function validated(Request $r,?int $id=null):array{$data=$r->validate(['first_name'=>['required','string','max:100'],'last_name'=>['nullable','string','max:100'],'company'=>['nullable','string','max:160'],'email'=>['nullable','email','max:190',Rule::unique('customers','email')->ignore($id)],'phone'=>['nullable','string','max:40',Rule::unique('customers','phone')->ignore($id)],'notes'=>['nullable','string','max:3000']]);$data['is_active']=$r->boolean('is_active');$data['marketing_opt_in']=$r->boolean('marketing_opt_in');return $data;}
}

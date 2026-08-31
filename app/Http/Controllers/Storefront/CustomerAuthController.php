<?php
namespace App\Http\Controllers\Storefront;
use App\Http\Controllers\Controller; use App\Models\Customer; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth; use Illuminate\Validation\Rules\Password;
class CustomerAuthController extends Controller {
 public function login(){return view('store.auth.login');}
 public function authenticate(Request $r){$data=$r->validate(['email'=>'required|email','password'=>'required|string']); if(Auth::guard('customer')->attempt(['email'=>$data['email'],'password'=>$data['password'],'is_active'=>1],$r->boolean('remember'))){$r->session()->regenerate(); return redirect()->intended(route('account'));} return back()->withErrors(['email'=>'Email or password is incorrect.'])->onlyInput('email');}
 public function register(){return view('store.auth.register');}
 public function store(Request $r){$data=$r->validate(['first_name'=>'required|string|max:100','last_name'=>'nullable|string|max:100','email'=>'required|email|max:190|unique:customers,email','phone'=>'nullable|string|max:40|unique:customers,phone','password'=>['required','confirmed',Password::min(8)]]); $c=Customer::create($data+['is_active'=>true]); Auth::guard('customer')->login($c); $r->session()->regenerate(); return redirect()->route('account')->with('success','Welcome to your Scents by Aamir account.');}
 public function logout(Request $r){Auth::guard('customer')->logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('home');}
}

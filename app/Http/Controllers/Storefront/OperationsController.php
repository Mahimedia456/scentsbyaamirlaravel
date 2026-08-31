<?php
namespace App\Http\Controllers\Storefront;
use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Services\TransactionalMailService;
class OperationsController extends Controller {
 public function contact(){return view('store.contact',['settings'=>$this->settings()]);}
 public function contactStore(Request $r, TransactionalMailService $mail){$d=$r->validate(['first_name'=>'required|string|max:100','last_name'=>'nullable|string|max:100','email'=>'required|email|max:190','subject'=>'required|in:order-support,shipping,returns,fragrance-guidance,gifting,other','order_number'=>'nullable|string|max:60','message'=>'required|string|min:10|max:5000']);$inquiry=ContactInquiry::create($d);$mail->contact($inquiry);return back()->with('success','Your request has been received. Our customer care team will contact you shortly.');}
 public function newsletter(Request $r){$d=$r->validate(['email'=>'required|email|max:190']);NewsletterSubscriber::updateOrCreate(['email'=>strtolower($d['email'])],['status'=>'subscribed','source'=>$r->input('source','footer'),'subscribed_at'=>now(),'unsubscribed_at'=>null]);return back()->with('newsletter_success','Thank you. You are now on our private notes list.');}
 public function track(){return view('store.track-order',['order'=>null]);}
 public function trackStore(Request $r){$d=$r->validate(['order_number'=>'required|string|max:60','identity'=>'required|string|max:190']);$order=Order::with('items')->where('order_number',$d['order_number'])->where(function($q)use($d){$q->whereRaw('LOWER(customer_email) = ?', [strtolower($d['identity'])])->orWhere('customer_phone',$d['identity']);})->first();return view('store.track-order',compact('order'))->with('searched',true);}
 private function settings(){return Schema::hasTable('store_settings')?StoreSetting::pluck('value','key'):collect();}
}

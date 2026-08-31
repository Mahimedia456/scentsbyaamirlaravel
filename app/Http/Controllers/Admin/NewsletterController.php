<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;use App\Models\NewsletterSubscriber;use Illuminate\Http\Request;
class NewsletterController extends Controller {public function index(Request $r){$q=NewsletterSubscriber::latest();if($r->filled('status'))$q->where('status',$r->status);return view('admin.newsletter.index',['subscribers'=>$q->paginate(40)->withQueryString()]);}public function update(NewsletterSubscriber $subscriber){$subscriber->update($subscriber->status==='subscribed'?['status'=>'unsubscribed','unsubscribed_at'=>now()]:['status'=>'subscribed','subscribed_at'=>now(),'unsubscribed_at'=>null]);return back()->with('success','Subscriber status updated.');}}

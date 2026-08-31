<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;use App\Models\ContactInquiry;use Illuminate\Http\Request;
class ContactInquiryController extends Controller {public function index(Request $r){$q=ContactInquiry::latest();if($r->filled('status'))$q->where('status',$r->status);return view('admin.contact-inquiries.index',['inquiries'=>$q->paginate(30)->withQueryString()]);}public function show(ContactInquiry $inquiry){return view('admin.contact-inquiries.show',compact('inquiry'));}public function update(Request $r,ContactInquiry $inquiry){$d=$r->validate(['status'=>'required|in:new,in-progress,resolved,closed','admin_notes'=>'nullable|string|max:5000']);$inquiry->update($d);return back()->with('success','Support request updated.');}}

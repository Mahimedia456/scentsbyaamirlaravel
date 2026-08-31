<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\JournalPost;
use App\Models\Navigation;
use App\Models\Page;
class ContentController extends Controller
{
    public function page(string $slug){$page=Page::where('slug',$slug)->where('status','published')->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()))->firstOrFail();return response()->json(['data'=>$page]);}
    public function journal(){return response()->json(['data'=>JournalPost::where('status','published')->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()))->latest('published_at')->paginate(12)]);}
    public function journalShow(string $slug){return response()->json(['data'=>JournalPost::where('slug',$slug)->where('status','published')->firstOrFail()]);}
    public function navigation(string $key){$nav=Navigation::with(['items'=>fn($q)=>$q->where('is_active',true)])->where('key',$key)->where('is_active',true)->firstOrFail();return response()->json(['data'=>$nav]);}
}

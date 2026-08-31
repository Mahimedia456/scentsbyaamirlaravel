<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\JournalPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class JournalPostController extends Controller
{
    public function index(Request $r){$posts=JournalPost::when($r->filled('q'),fn($q)=>$q->where('title','like','%'.$r->q.'%')->orWhere('slug','like','%'.$r->q.'%'))->latest()->paginate(25)->withQueryString();return view('admin.journal-posts.index',compact('posts'));}
    public function create(){return view('admin.journal-posts.form',['post'=>new JournalPost]);}
    public function edit(JournalPost $journal_post){return view('admin.journal-posts.form',['post'=>$journal_post]);}
    public function store(Request $r){JournalPost::create($this->validated($r));return redirect()->route('admin.journal-posts.index')->with('success','Journal post created.');}
    public function update(Request $r,JournalPost $journal_post){$journal_post->update($this->validated($r,$journal_post->id));return redirect()->route('admin.journal-posts.index')->with('success','Journal post updated.');}
    public function destroy(JournalPost $journal_post){$journal_post->delete();return back()->with('success','Journal post deleted.');}
    private function validated(Request $r,?int $id=null):array{$d=$r->validate(['title'=>['required','string','max:190'],'slug'=>['nullable','string','max:200',Rule::unique('journal_posts','slug')->ignore($id)],'eyebrow'=>['nullable','string','max:120'],'excerpt'=>['nullable','string','max:1500'],'content'=>['nullable','string'],'featured_image_path'=>['nullable','string','max:500'],'status'=>['required',Rule::in(['draft','published','archived'])],'meta_title'=>['nullable','string','max:190'],'meta_description'=>['nullable','string','max:1000'],'published_at'=>['nullable','date']]);$d['slug']=$d['slug']?:Str::slug($d['title']);if($d['status']==='published'&&!$d['published_at'])$d['published_at']=now();return $d;}
}

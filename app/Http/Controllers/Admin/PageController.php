<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class PageController extends Controller
{
    public function index(Request $r){$pages=Page::when($r->filled('q'),fn($q)=>$q->where('title','like','%'.$r->q.'%')->orWhere('slug','like','%'.$r->q.'%'))->latest()->paginate(25)->withQueryString();return view('admin.pages.index',compact('pages'));}
    public function create(){return view('admin.pages.form',['page'=>new Page]);}
    public function edit(Page $page){return view('admin.pages.form',compact('page'));}
    public function store(Request $r){Page::create($this->validated($r));return redirect()->route('admin.pages.index')->with('success','Page created.');}
    public function update(Request $r,Page $page){$page->update($this->validated($r,$page->id));return redirect()->route('admin.pages.index')->with('success','Page updated.');}
    public function duplicate(Page $page){$copy=$page->replicate();$copy->title=$page->title.' Copy';$copy->slug=Str::slug($copy->title).'-'.Str::lower(Str::random(4));$copy->status='draft';$copy->published_at=null;$copy->save();return redirect()->route('admin.pages.edit',$copy)->with('success','Page duplicated as draft.');}
    public function destroy(Page $page){$page->delete();return back()->with('success','Page deleted.');}
    private function validated(Request $r,?int $id=null):array{$d=$r->validate(['title'=>['required','string','max:180'],'slug'=>['nullable','string','max:190',Rule::unique('pages','slug')->ignore($id)],'template'=>['required','string','max:80'],'content'=>['nullable','string'],'status'=>['required',Rule::in(['draft','published','archived'])],'meta_title'=>['nullable','string','max:190'],'meta_description'=>['nullable','string','max:1000'],'published_at'=>['nullable','date']]);$d['slug']=$d['slug']?:Str::slug($d['title']);if($d['status']==='published'&&!$d['published_at'])$d['published_at']=now();return $d;}
}

<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Navigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class NavigationController extends Controller
{
    public function index(){return view('admin.navigations.index',['navigations'=>Navigation::withCount('items')->latest()->get()]);}
    public function create(){return view('admin.navigations.form',['navigation'=>new Navigation]);}
    public function edit(Navigation $navigation){$navigation->load('items');return view('admin.navigations.form',compact('navigation'));}
    public function store(Request $r){$this->persist($r,new Navigation);return redirect()->route('admin.navigations.index')->with('success','Navigation created.');}
    public function update(Request $r,Navigation $navigation){$this->persist($r,$navigation);return redirect()->route('admin.navigations.index')->with('success','Navigation updated.');}
    public function destroy(Navigation $navigation){$navigation->delete();return back()->with('success','Navigation deleted.');}
    private function persist(Request $r,Navigation $navigation):void{$d=$r->validate(['name'=>['required','string','max:120'],'key'=>['nullable','string','max:120',Rule::unique('navigations','key')->ignore($navigation->id)],'items'=>['nullable','array'],'items.*.label'=>['nullable','string','max:140'],'items.*.url'=>['nullable','string','max:500'],'items.*.target'=>['nullable',Rule::in(['_self','_blank'])]]);DB::transaction(function()use($r,$d,$navigation){$navigation->fill(['name'=>$d['name'],'key'=>$d['key']?:Str::slug($d['name'],'_'),'is_active'=>$r->boolean('is_active')])->save();$navigation->items()->delete();foreach($r->input('items',[]) as $i=>$row){if(blank($row['label']??null))continue;$navigation->items()->create(['label'=>$row['label'],'url'=>$row['url']??null,'target'=>$row['target']??'_self','sort_order'=>$i,'is_active'=>true]);}});}
}

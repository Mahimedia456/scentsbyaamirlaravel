<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $collections = Collection::query()->withCount('products')
            ->when($request->filled('q'), fn($q)=>$q->where('name','like','%'.$request->q.'%'))
            ->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.collections.index', compact('collections'));
    }
    public function create(){ return view('admin.collections.form',['collection'=>new Collection]); }
    public function store(Request $request){ $data=$this->validated($request); $data['slug']=$data['slug']?:Str::slug($data['name']); Collection::create($data); return redirect()->route('admin.collections.index')->with('success','Collection created.'); }
    public function edit(Collection $collection){ return view('admin.collections.form',compact('collection')); }
    public function update(Request $request, Collection $collection){ $data=$this->validated($request,$collection->id); $data['slug']=$data['slug']?:Str::slug($data['name']); $collection->update($data); return redirect()->route('admin.collections.index')->with('success','Collection updated.'); }
    public function destroy(Collection $collection){ $collection->products()->detach(); $collection->delete(); return back()->with('success','Collection deleted.'); }
    private function validated(Request $request, ?int $id=null):array { $data=$request->validate(['name'=>['required','string','max:120'],'slug'=>['nullable','string','max:140',Rule::unique('collections','slug')->ignore($id)],'description'=>['nullable','string'],'sort_order'=>['nullable','integer','min:0'],'is_active'=>['nullable','boolean']]); $data['is_active']=$request->boolean('is_active'); $data['sort_order']=(int)($data['sort_order']??0); return $data; }
}

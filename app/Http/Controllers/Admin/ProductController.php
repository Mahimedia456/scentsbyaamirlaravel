<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()->with('category')->withCount(['variants','images'])
            ->when($request->filled('q'), fn($q)=>$q->where(fn($x)=>$x->where('name','like','%'.$request->q.'%')->orWhere('sku','like','%'.$request->q.'%')))
            ->when($request->filled('status'), fn($q)=>$q->where('status',$request->status))
            ->when($request->filled('category_id'), fn($q)=>$q->where('category_id',$request->category_id))
            ->latest()->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        return view('admin.products.index', compact('products','categories'));
    }

    public function create(){ return $this->form(new Product); }
    public function edit(Product $product){ $product->load(['variants','images','collections']); return $this->form($product); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function() use ($request,$data){
            $product = Product::create($data);
            $this->syncRelations($request,$product);
        });
        return redirect()->route('admin.products.index')->with('success','Product created.');
    }

    public function update(Request $request, Product $product)
    {
        $data=$this->validated($request,$product->id);
        DB::transaction(function() use($request,$data,$product){ $product->update($data); $this->syncRelations($request,$product); });
        return redirect()->route('admin.products.index')->with('success','Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success','Product deleted.');
    }

    private function form(Product $product)
    {
        return view('admin.products.form',[
            'product'=>$product,
            'categories'=>Category::where('is_active',true)->orderBy('name')->get(),
            'collections'=>Collection::where('is_active',true)->orderBy('name')->get(),
        ]);
    }

    private function validated(Request $request, ?int $id=null):array
    {
        $data=$request->validate([
            'category_id'=>['nullable','exists:categories,id'],'name'=>['required','string','max:160'],
            'slug'=>['nullable','string','max:180',Rule::unique('products','slug')->ignore($id)],
            'subtitle'=>['nullable','string','max:200'],'description'=>['nullable','string'],'story'=>['nullable','string'],'notes'=>['nullable','string'],'wear'=>['nullable','string'],
            'status'=>['required',Rule::in(['draft','active','archived'])], 'sku'=>['nullable','string','max:100',Rule::unique('products','sku')->ignore($id)],
            'base_price'=>['nullable','numeric','min:0'],'compare_at_price'=>['nullable','numeric','min:0'],'stock'=>['nullable','integer','min:0'],
            'size_label'=>['nullable','string','max:80'],'track_inventory'=>['nullable','boolean'],'is_in_stock'=>['nullable','boolean'],
            'meta_title'=>['nullable','string','max:180'],'meta_description'=>['nullable','string','max:500'],
            'collections'=>['nullable','array'],'collections.*'=>['integer','exists:collections,id'],
            'variants'=>['nullable','array'],'variants.*.name'=>['nullable','string','max:120'],'variants.*.size_label'=>['nullable','string','max:80'],'variants.*.sku'=>['nullable','string','max:100'],'variants.*.price'=>['nullable','numeric','min:0'],'variants.*.compare_at_price'=>['nullable','numeric','min:0'],'variants.*.stock'=>['nullable','integer','min:0'],'variants.*.is_active'=>['nullable','boolean'],
            'images'=>['nullable','array'],'images.*.path'=>['nullable','string','max:500'],'images.*.alt_text'=>['nullable','string','max:180'],'images.*.is_primary'=>['nullable','boolean'],
            'image_uploads'=>['nullable','array','max:12'],'image_uploads.*'=>['image','mimes:jpg,jpeg,png,webp','max:8192'],
        ]);
        $data['slug']=$data['slug']?:Str::slug($data['name']);
        $data['is_featured']=$request->boolean('is_featured');
        $data['track_inventory']=$request->boolean('track_inventory');
        $data['is_in_stock']=$request->boolean('is_in_stock');
        $data['stock']=(int)($data['stock']??0);
        $data['stock_quantity']=$data['stock'];

        if ($data['track_inventory']) {
            $data['is_in_stock']=$data['stock'] > 0;
        }
        unset($data['collections'],$data['variants'],$data['images']);
        return $data;
    }

    private function syncRelations(Request $request, Product $product):void
    {
        $product->collections()->sync($request->input('collections',[]));
        $product->variants()->delete();
        foreach($request->input('variants',[]) as $i=>$row){ if(blank($row['name']??null)&&blank($row['sku']??null)&&blank($row['size_label']??null)) continue; $product->variants()->create(['name'=>$row['name']??($row['size_label']??'Variant'),'size_label'=>$row['size_label']??null,'sku'=>$row['sku']??null,'price'=>$row['price']??0,'compare_at_price'=>$row['compare_at_price']??null,'stock'=>$row['stock']??0,'is_active'=>isset($row['is_active'])?(bool)$row['is_active']:true,'sort_order'=>$i]); }
        $product->images()->delete();
        $primaryAssigned=false;
        $sortOrder = 0;

        foreach($request->input('images',[]) as $row){
            if(blank($row['path']??null)) continue;
            $isPrimary=!$primaryAssigned && !empty($row['is_primary']);
            if($isPrimary)$primaryAssigned=true;
            $product->images()->create([
                'path'=>$row['path'],
                'alt_text'=>$row['alt_text']??$product->name,
                'sort_order'=>$sortOrder++,
                'is_primary'=>$isPrimary,
            ]);
        }

        foreach($request->file('image_uploads', []) as $upload){
            if(!$upload || !$upload->isValid()) continue;

            $base = Str::slug(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'product';
            $filename = $base.'-'.Str::lower(Str::random(8)).'.'.$upload->getClientOriginalExtension();
            $path = $upload->storeAs('products/'.$product->id, $filename, 'public');

            $product->images()->create([
                'path'=>$path,
                'alt_text'=>$product->name,
                'sort_order'=>$sortOrder++,
                'is_primary'=>!$primaryAssigned,
            ]);
            $primaryAssigned=true;
        }

        if(!$primaryAssigned && $product->images()->exists()) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary'=>true]);
        }
    }
}

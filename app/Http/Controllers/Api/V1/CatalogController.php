<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function products(Request $request)
    {
        $items=Product::query()->where('status','active')->with(['category','collections','variants'=>fn($q)=>$q->where('is_active',true),'images'])
            ->when($request->filled('category'),fn($q)=>$q->whereHas('category',fn($x)=>$x->where('slug',$request->category)))
            ->when($request->filled('collection'),fn($q)=>$q->whereHas('collections',fn($x)=>$x->where('slug',$request->collection)))
            ->when($request->boolean('featured'),fn($q)=>$q->where('is_featured',true))
            ->latest()->paginate(min(max((int)$request->input('per_page',12),1),48));
        return response()->json($items);
    }
    public function product(string $slug){ return response()->json(Product::where('slug',$slug)->where('status','active')->with(['category','collections','variants'=>fn($q)=>$q->where('is_active',true),'images'])->firstOrFail()); }
    public function categories(){ return response()->json(Category::where('is_active',true)->withCount('products')->orderBy('sort_order')->orderBy('name')->get()); }
    public function collections(){ return response()->json(Collection::where('is_active',true)->withCount('products')->orderBy('sort_order')->orderBy('name')->get()); }
}

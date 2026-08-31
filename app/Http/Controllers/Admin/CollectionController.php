<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $collections = Collection::query()
            ->withCount('products')
            ->when($request->filled('q'), fn ($q) => $q->where('name','like','%'.$request->q.'%'))
            ->when($request->filled('state'), fn ($q) => $q->where('is_active', $request->state === 'active'))
            ->orderBy('sort_order')->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.collections.index', compact('collections'));
    }

    public function create()
    {
        return view('admin.collections.form', [
            'collection' => new Collection,
            'products' => Product::orderBy('name')->get(['id','name','status']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $collection = Collection::create($data);
        $collection->products()->sync($request->input('product_ids', []));

        return redirect()->route('admin.collections.edit', $collection)->with('success','Collection created.');
    }

    public function edit(Collection $collection)
    {
        $collection->load('products');

        return view('admin.collections.form', [
            'collection' => $collection,
            'products' => Product::orderBy('name')->get(['id','name','status']),
        ]);
    }

    public function update(Request $request, Collection $collection)
    {
        $data = $this->validated($request, $collection->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $collection->update($data);
        $collection->products()->sync($request->input('product_ids', []));

        return redirect()->route('admin.collections.edit', $collection)->with('success','Collection updated.');
    }

    public function destroy(Collection $collection)
    {
        $collection->products()->detach();
        $collection->delete();

        return back()->with('success','Collection deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'name'=>['required','string','max:120'],
            'slug'=>['nullable','string','max:140',Rule::unique('collections','slug')->ignore($id)],
            'description'=>['nullable','string'],
            'sort_order'=>['nullable','integer','min:0'],
            'is_active'=>['nullable','boolean'],
            'product_ids'=>['nullable','array'],
            'product_ids.*'=>['integer','exists:products,id'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);
        unset($data['product_ids']);

        return $data;
    }
}

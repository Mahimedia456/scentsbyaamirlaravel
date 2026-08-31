<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('products')
            ->when($request->filled('q'), fn ($q) => $q->where('name','like','%'.$request->q.'%'))
            ->when($request->filled('state'), fn ($q) => $q->where('is_active', $request->state === 'active'))
            ->orderBy('sort_order')->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new Category,
            'products' => Product::orderBy('name')->get(['id','name','category_id','status']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $category = Category::create($data);
        $this->syncProducts($request, $category);

        return redirect()->route('admin.categories.edit', $category)->with('success','Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'products' => Product::orderBy('name')->get(['id','name','category_id','status']),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $category->update($data);
        $this->syncProducts($request, $category);

        return redirect()->route('admin.categories.edit', $category)->with('success','Category updated.');
    }

    public function destroy(Category $category)
    {
        abort_if($category->products()->exists(), 422, 'Move products out of this category before deleting it.');
        $category->delete();

        return back()->with('success','Category deleted.');
    }

    private function syncProducts(Request $request, Category $category): void
    {
        $ids = collect($request->input('product_ids', []))->map(fn ($id) => (int) $id)->filter()->values();

        Product::where('category_id', $category->id)
            ->whereNotIn('id', $ids)
            ->update(['category_id' => null]);

        if ($ids->isNotEmpty()) {
            Product::whereIn('id', $ids)->update(['category_id' => $category->id]);
        }
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'name'=>['required','string','max:120'],
            'slug'=>['nullable','string','max:140',Rule::unique('categories','slug')->ignore($id)],
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

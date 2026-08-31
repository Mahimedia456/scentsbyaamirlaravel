<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('products')
            ->when($request->filled('q'), fn($q) => $q->where('name','like','%'.$request->q.'%'))
            ->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.categories.index', compact('categories'));
    }

    public function create() { return view('admin.categories.form', ['category'=>new Category]); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success','Category created.');
    }

    public function edit(Category $category) { return view('admin.categories.form', compact('category')); }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success','Category updated.');
    }

    public function destroy(Category $category)
    {
        abort_if($category->products()->exists(), 422, 'Move products out of this category before deleting it.');
        $category->delete();
        return back()->with('success','Category deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'name'=>['required','string','max:120'],
            'slug'=>['nullable','string','max:140', Rule::unique('categories','slug')->ignore($id)],
            'description'=>['nullable','string'],
            'sort_order'=>['nullable','integer','min:0'],
            'is_active'=>['nullable','boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);
        return $data;
    }
}

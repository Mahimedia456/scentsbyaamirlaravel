<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with('category')
            ->withCount(['variants', 'images'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->q);
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('availability'), function ($query) use ($request) {
                if ($request->availability === 'in_stock') {
                    $query->where(function ($q) {
                        $q->where(fn ($tracked) => $tracked->where('track_inventory', true)->where('stock', '>', 0))
                            ->orWhere(fn ($simple) => $simple->where('track_inventory', false)->where('is_in_stock', true));
                    });
                }
                if ($request->availability === 'out_of_stock') {
                    $query->where(function ($q) {
                        $q->where(fn ($tracked) => $tracked->where('track_inventory', true)->where('stock', '<=', 0))
                            ->orWhere(fn ($simple) => $simple->where('track_inventory', false)->where('is_in_stock', false));
                    });
                }
            })
            ->when($request->filled('featured'), fn ($q) => $q->where('is_featured', $request->featured === 'yes'))
            ->orderByDesc('updated_at')
            ->paginate(24)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        $summary = [
            'all' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'draft' => Product::where('status', 'draft')->count(),
            'archived' => Product::where('status', 'archived')->count(),
            'featured' => Product::where('is_featured', true)->count(),
        ];

        return view('admin.products.index', compact('products', 'categories', 'summary'));
    }

    public function create()
    {
        return $this->form(new Product);
    }

    public function edit(Product $product)
    {
        $product->load(['variants', 'images', 'collections']);
        return $this->form($product);
    }

    public function store(Request $request)
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($this->validated($request));
            $this->syncRelations($request, $product);

            return $product;
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product created.');
    }

    public function update(Request $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            $product->update($this->validated($request, $product->id));
            $this->syncRelations($request, $product);
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->load('images');

        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                $this->deleteManagedFile($image->path);
            }

            $product->delete();
        });

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function duplicate(Product $product)
    {
        $product->load(['collections', 'variants', 'images']);

        $copy = DB::transaction(function () use ($product) {
            $copy = $product->replicate();
            $copy->name = $product->name . ' — Copy';
            $copy->slug = Str::slug($copy->name) . '-' . Str::lower(Str::random(5));
            $copy->sku = null;
            $copy->status = 'draft';
            $copy->is_featured = false;
            $copy->save();

            $copy->collections()->sync($product->collections->pluck('id'));

            foreach ($product->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_id = $copy->id;
                $newVariant->sku = null;
                $newVariant->save();
            }

            foreach ($product->images as $image) {
                $newImage = $image->replicate();
                $newImage->product_id = $copy->id;
                $newImage->save();
            }

            return $copy;
        });

        return redirect()
            ->route('admin.products.edit', $copy)
            ->with('success', 'Product duplicated as a draft.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'action' => ['required', Rule::in([
                'activate',
                'draft',
                'archive',
                'feature',
                'unfeature',
                'mark_in_stock',
                'mark_out_of_stock',
                'delete',
            ])],
        ]);

        $products = Product::query()->whereIn('id', $data['ids'])->get();

        DB::transaction(function () use ($products, $data) {
            foreach ($products as $product) {
                match ($data['action']) {
                    'activate' => $product->update(['status' => 'active']),
                    'draft' => $product->update(['status' => 'draft']),
                    'archive' => $product->update(['status' => 'archived']),
                    'feature' => $product->update(['is_featured' => true]),
                    'unfeature' => $product->update(['is_featured' => false]),
                    'mark_in_stock' => $product->update(['is_in_stock' => true]),
                    'mark_out_of_stock' => $product->update(['is_in_stock' => false]),
                    'delete' => $this->deleteProduct($product),
                };
            }
        });

        return back()->with('success', count($products) . ' product(s) updated.');
    }

    private function deleteProduct(Product $product): void
    {
        $product->loadMissing('images');

        foreach ($product->images as $image) {
            $this->deleteManagedFile($image->path);
        }

        $product->delete();
    }

    private function form(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'collections' => Collection::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('products', 'slug')->ignore($id)],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'story' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'top_notes' => ['nullable', 'string', 'max:1000'],
            'heart_notes' => ['nullable', 'string', 'max:1000'],
            'base_notes' => ['nullable', 'string', 'max:1000'],
            'wear' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($id)],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'size_label' => ['nullable', 'string', 'max:80'],
            'track_inventory' => ['nullable', 'boolean'],
            'is_in_stock' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'collections' => ['nullable', 'array'],
            'collections.*' => ['integer', 'exists:collections,id'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:120'],
            'variants.*.size_label' => ['nullable', 'string', 'max:80'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'removed_images' => ['nullable', 'array'],
            'removed_images.*' => ['integer'],
            'primary_image_id' => ['nullable', 'integer'],
            'image_uploads' => ['nullable', 'array', 'max:12'],
            'image_uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['track_inventory'] = $request->boolean('track_inventory');
        $data['is_in_stock'] = $request->boolean('is_in_stock');
        $data['stock'] = (int) ($data['stock'] ?? 0);
        $data['stock_quantity'] = $data['stock'];

        $structuredNotes = collect([
            filled($data['top_notes'] ?? null) ? 'Top Notes: ' . trim((string) $data['top_notes']) : null,
            filled($data['heart_notes'] ?? null) ? 'Heart Notes: ' . trim((string) $data['heart_notes']) : null,
            filled($data['base_notes'] ?? null) ? 'Base Notes: ' . trim((string) $data['base_notes']) : null,
        ])->filter()->implode("\n");

        if ($structuredNotes !== '') {
            $data['notes'] = $structuredNotes;
        }

        if ($data['track_inventory']) {
            $data['is_in_stock'] = $data['stock'] > 0;
        }

        unset(
            $data['collections'],
            $data['variants'],
            $data['removed_images'],
            $data['primary_image_id'],
            $data['image_uploads']
        );

        return $data;
    }

    private function syncRelations(Request $request, Product $product): void
    {
        $product->collections()->sync($request->input('collections', []));

        // Variants remain an advanced feature. Existing variant rows are rebuilt
        // from the submitted advanced section, but simple products need none.
        $product->variants()->delete();

        foreach ($request->input('variants', []) as $i => $row) {
            if (
                blank($row['name'] ?? null)
                && blank($row['sku'] ?? null)
                && blank($row['size_label'] ?? null)
            ) {
                continue;
            }

            $product->variants()->create([
                'name' => $row['name'] ?? ($row['size_label'] ?? 'Variant'),
                'size_label' => $row['size_label'] ?? null,
                'sku' => $row['sku'] ?? null,
                'price' => $row['price'] ?? 0,
                'compare_at_price' => $row['compare_at_price'] ?? null,
                'stock' => $row['stock'] ?? 0,
                'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
                'sort_order' => $i,
            ]);
        }

        // Preserve existing gallery rows/files unless the admin explicitly
        // checks them for removal.
        $removedIds = collect($request->input('removed_images', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($removedIds->isNotEmpty()) {
            $toRemove = $product->images()->whereIn('id', $removedIds)->get();

            foreach ($toRemove as $image) {
                $this->deleteManagedFile($image->path);
                $image->delete();
            }
        }

        $sortOrder = (int) ($product->images()->max('sort_order') ?? -1) + 1;

        foreach ($request->file('image_uploads', []) as $upload) {
            if (!$upload || !$upload->isValid()) {
                continue;
            }

            $base = Str::slug(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'product';
            $filename = $base . '-' . Str::lower(Str::random(8)) . '.' . $upload->getClientOriginalExtension();
            $path = $upload->storeAs('products/' . $product->id, $filename, 'public');

            $product->images()->create([
                'path' => $path,
                'alt_text' => $product->name,
                'sort_order' => $sortOrder++,
                'is_primary' => false,
            ]);
        }

        $primaryId = (int) $request->input('primary_image_id', 0);

        if ($primaryId && $product->images()->whereKey($primaryId)->exists()) {
            $product->images()->update(['is_primary' => false]);
            $product->images()->whereKey($primaryId)->update(['is_primary' => true]);
        } elseif (!$product->images()->where('is_primary', true)->exists() && $product->images()->exists()) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }
    }

    private function deleteManagedFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        // Never delete remote/source URLs. Only remove files managed by the
        // Laravel public disk.
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

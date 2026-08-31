<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StorefrontCatalogService
{
    public function allProducts(array $filters = []): Collection
    {
        if ($this->catalogReady()) {
            $query = Product::query()
                ->where('status', 'active')
                ->with(['category', 'collections', 'variants' => fn ($q) => $q->where('is_active', true), 'images']);

            if (!empty($filters['search'])) {
                $term = trim((string) $filters['search']);
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('subtitle', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            }

            if (!empty($filters['audience'])) {
                $audience = Str::lower((string) $filters['audience']);
                $query->where(function ($q) use ($audience) {
                    if ($audience === 'women') {
                        $q->whereHas('category', fn ($c) => $c
                            ->where('slug', 'like', '%women%')
                            ->orWhere('slug', 'like', '%female%')
                            ->orWhere('name', 'like', '%Women%')
                            ->orWhere('name', 'like', '%Female%'))
                            ->orWhere('name', 'like', '%Womens%')
                            ->orWhere('name', 'like', "%Women's%")
                            ->orWhere('name', 'like', '%Women %')
                            ->orWhere('subtitle', 'like', '%Women%')
                            ->orWhere('description', 'like', '%women%');
                    } elseif ($audience === 'men') {
                        $q->whereHas('category', fn ($c) => $c
                            ->where('slug', 'like', '%-men%')
                            ->orWhere('slug', 'like', 'men%')
                            ->orWhere('slug', 'mens')
                            ->orWhere('name', 'like', 'Men%')
                            ->orWhere('name', 'like', '% Men'))
                            ->orWhere('name', 'like', '%Mens%')
                            ->orWhere('name', 'like', "%Men's%")
                            ->orWhere('name', 'like', '% Men %')
                            ->orWhere('subtitle', 'like', '%Mens%')
                            ->orWhere('subtitle', 'like', '% Men %');
                    } elseif ($audience === 'unisex') {
                        $q->whereHas('category', fn ($c) => $c
                            ->where('slug', 'like', '%unisex%')
                            ->orWhere('name', 'like', '%Unisex%'))
                            ->orWhere('name', 'like', '%Unisex%')
                            ->orWhere('subtitle', 'like', '%Unisex%')
                            ->orWhere('description', 'like', '%unisex%');
                    }
                });
            }

            if (!empty($filters['family'])) {
                $family = trim((string) $filters['family']);
                $query->where(function ($q) use ($family) {
                    $q->where('subtitle', 'like', "%{$family}%")
                        ->orWhere('name', 'like', "%{$family}%")
                        ->orWhere('notes', 'like', "%{$family}%")
                        ->orWhereHas('category', fn ($c) => $c
                            ->where('name', 'like', "%{$family}%")
                            ->orWhere('slug', 'like', '%'.Str::slug($family).'%'));
                });
            }

            if (!empty($filters['category'])) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
            }

            if (!empty($filters['collection'])) {
                $query->whereHas('collections', fn ($q) => $q->where('slug', $filters['collection']));
            }

            if (($filters['availability'] ?? null) === 'in-stock') {
                $query->where(function ($q) {
                    $q->where('stock', '>', 0)
                        ->orWhereHas('variants', fn ($v) => $v->where('is_active', true)->where('stock', '>', 0));
                });
            }

            if (($filters['edit'] ?? null) === 'featured' || !empty($filters['featured'])) {
                $query->where('is_featured', true);
            }

            if (($filters['edit'] ?? null) === 'new') {
                $query->latest('id');
            }

            if (($filters['min_price'] ?? null) !== null) {
                $min = (float) $filters['min_price'];
                $query->where(function ($q) use ($min) {
                    $q->where('base_price', '>=', $min)
                        ->orWhereHas('variants', fn ($v) => $v->where('is_active', true)->where('price', '>=', $min));
                });
            }

            if (($filters['max_price'] ?? null) !== null) {
                $max = (float) $filters['max_price'];
                $query->where(function ($q) use ($max) {
                    $q->where('base_price', '<=', $max)
                        ->orWhereHas('variants', fn ($v) => $v->where('is_active', true)->where('price', '<=', $max));
                });
            }

            $sort = $filters['sort'] ?? 'featured';
            match ($sort) {
                'newest' => $query->latest('id'),
                'name-asc' => $query->orderBy('name'),
                'price-asc' => $query->orderBy('base_price'),
                'price-desc' => $query->orderByDesc('base_price'),
                default => $query->orderByDesc('is_featured')->latest('id'),
            };

            $items = $query->get()->map(fn (Product $product) => $this->normalizeProduct($product));
            if ($items->isNotEmpty()) {
                return $items;
            }

            if (Product::query()->where('status', 'active')->exists()) {
                return collect();
            }
        }

        $items = collect(config('storefront.products', []))
            ->map(fn (array $product, string $slug) => $this->normalizeConfigProduct($slug, $product))
            ->values();

        if (!empty($filters['search'])) {
            $needle = Str::lower((string) $filters['search']);
            $items = $items->filter(fn ($p) => Str::contains(Str::lower(($p['name'] ?? '').' '.($p['family'] ?? '')), $needle));
        }

        if (!empty($filters['family'])) {
            $needle = Str::lower((string) $filters['family']);
            $items = $items->filter(fn ($p) => Str::contains(Str::lower(($p['family'] ?? '').' '.($p['name'] ?? '')), $needle));
        }

        return $items->values();
    }

    public function featuredProducts(int $limit = 4): Collection
    {
        $featured = $this->allProducts(['featured' => true]);
        if ($featured->isEmpty()) {
            $featured = $this->allProducts();
        }
        return $featured->take($limit)->values();
    }

    public function product(string $slug): ?array
    {
        if ($this->catalogReady()) {
            $product = Product::query()
                ->where('slug', $slug)
                ->where('status', 'active')
                ->with(['category', 'collections', 'variants' => fn ($q) => $q->where('is_active', true), 'images'])
                ->first();

            if ($product) {
                return $this->normalizeProduct($product);
            }
        }

        $product = config("storefront.products.$slug");
        return $product ? $this->normalizeConfigProduct($slug, $product) : null;
    }

    public function categories(): Collection
    {
        if (Schema::hasTable('categories')) {
            return Category::query()
                ->where('is_active', true)
                ->whereHas('products', fn ($q) => $q->where('status', 'active'))
                ->withCount(['products' => fn ($q) => $q->where('status', 'active')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'products_count' => $category->products_count,
                ])
                ->values();
        }

        return collect();
    }

    public function collections(): Collection
    {
        if (Schema::hasTable('collections') && Schema::hasTable('collection_product')) {
            $items = ProductCollection::query()
                ->where('is_active', true)
                ->withCount(['products' => fn ($q) => $q->where('status', 'active')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (ProductCollection $collection) => [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                    'description' => $collection->description,
                    'products_count' => $collection->products_count,
                ]);

            if ($items->isNotEmpty()) {
                return $items;
            }
        }

        return collect([
            ['id' => null, 'name' => 'Signature Worlds', 'slug' => 'signature-worlds', 'description' => 'The essential fragrance wardrobe.', 'products_count' => 4],
            ['id' => null, 'name' => 'Nocturnal', 'slug' => 'nocturnal', 'description' => 'Fragrance after daylight.', 'products_count' => 4],
            ['id' => null, 'name' => 'Light Studies', 'slug' => 'light-studies', 'description' => 'Air, skin and brightness.', 'products_count' => 4],
        ]);
    }

    public function collection(string $slug): ?array
    {
        if (Schema::hasTable('collections') && Schema::hasTable('collection_product')) {
            $collection = ProductCollection::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->with(['products' => fn ($q) => $q->where('status', 'active')->with(['category', 'variants' => fn ($v) => $v->where('is_active', true), 'images'])])
                ->first();

            if ($collection) {
                return [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                    'description' => $collection->description,
                    'products' => $collection->products->map(fn (Product $product) => $this->normalizeProduct($product))->values(),
                ];
            }
        }

        $fallback = [
            'signature-worlds' => ['Memory 01', 'Velvet Oud', 'Solar Skin', 'After Dark'],
            'nocturnal' => ['Velvet Oud', 'After Dark', 'Oud Noir', 'Midnight Resin'],
            'light-studies' => ['Solar Skin', 'White Musk', 'Neroli Skin', 'Cedar 09'],
        ][$slug] ?? null;

        if (!$fallback) {
            return null;
        }

        $all = $this->allProducts()->keyBy('name');
        $meta = $this->collections()->firstWhere('slug', $slug);

        return [
            'id' => null,
            'name' => $meta['name'],
            'slug' => $slug,
            'description' => $meta['description'],
            'products' => collect($fallback)->map(fn ($name) => $all->get($name))->filter()->values(),
        ];
    }

    public function related(string $slug, ?int $categoryId = null, int $limit = 4): Collection
    {
        if ($this->catalogReady()) {
            $query = Product::query()
                ->where('status', 'active')
                ->where('slug', '!=', $slug)
                ->with(['category', 'collections', 'variants' => fn ($q) => $q->where('is_active', true), 'images']);

            if ($categoryId) {
                $query->orderByRaw('category_id = ? desc', [$categoryId]);
            }

            $items = $query->latest('id')->limit($limit)->get()->map(fn (Product $p) => $this->normalizeProduct($p));
            if ($items->isNotEmpty()) {
                return $items;
            }
        }

        return $this->allProducts()->where('slug', '!=', $slug)->take($limit)->values();
    }

    private function catalogReady(): bool
    {
        return Schema::hasTable('products') && Schema::hasTable('product_variants') && Schema::hasTable('product_images');
    }

    private function normalizeProduct(Product $product): array
    {
        $primary = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
        $secondary = $product->images->first(fn ($image) => !$primary || $image->id !== $primary->id);
        $variant = $product->variants->first(fn ($item) => max((int) ($item->stock ?? 0), (int) ($item->stock_quantity ?? 0)) > 0) ?: $product->variants->first();
        $visual = config("storefront.products.{$product->slug}", []);

        $price = $variant?->price ?? $product->base_price ?? 0;
        $stock = $product->variants->isNotEmpty()
            ? (int) $product->variants->sum(fn ($v) => max((int) ($v->stock ?? 0), (int) ($v->stock_quantity ?? 0)))
            : max((int) ($product->stock ?? 0), (int) ($product->stock_quantity ?? 0));

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'display_name' => $this->displayName($product->name),
            'family' => $product->subtitle ?: ($product->category?->name ?: 'Fine Fragrance'),
            'category_id' => $product->category_id,
            'category' => $product->category?->only(['id', 'name', 'slug']),
            'price' => number_format((float) $price, 0),
            'price_value' => (float) $price,
            'compare_at_price' => $variant?->compare_at_price ?? $product->compare_at_price,
            'badge' => $product->is_featured ? 'Featured' : ($visual['badge'] ?? null),
            'image' => $this->imageUrl($primary?->path) ?: ($visual['image'] ?? null),
            'world_image' => $this->imageUrl($secondary?->path) ?: ($visual['world_image'] ?? $this->imageUrl($primary?->path) ?? $visual['image'] ?? null),
            'images' => $product->images
                ->sortByDesc(fn ($image) => (bool) $image->is_primary)
                ->map(fn ($image) => $this->imageUrl($image->path))
                ->filter()
                ->values()
                ->all(),
            'audience' => $this->inferAudience(
                $product->name,
                $product->subtitle,
                $product->description,
                $product->category?->name
            ),
            'theme' => $visual['theme'] ?? [],
            'world' => $visual['world'] ?? [],
            'description' => $this->plainText($product->description),
            'story' => $this->plainText($product->story),
            'notes' => $this->plainText($product->notes),
            'wear' => $this->plainText($product->wear),
            'sku' => $variant?->sku ?: $product->sku,
            'stock' => $stock,
            'in_stock' => $stock > 0,
            'is_featured' => (bool) $product->is_featured,
            'variants' => $product->variants
                ->sortBy(function ($v) {
                    $label = (string) ($v->size_label ?: $v->name ?: '');
                    preg_match('/(\d+(?:\.\d+)?)/', $label, $matches);
                    $size = isset($matches[1]) ? (float) $matches[1] : 9999;

                    return [
                        max((int) ($v->stock ?? 0), (int) ($v->stock_quantity ?? 0)) > 0 ? 0 : 1,
                        $size,
                        (int) ($v->sort_order ?? 0),
                    ];
                })
                ->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'size_label' => $v->size_label ?: $v->name,
                    'sku' => $v->sku,
                    'price' => number_format((float) $v->price, 0),
                    'price_value' => (float) $v->price,
                    'stock' => max((int) ($v->stock ?? 0), (int) ($v->stock_quantity ?? 0)),
                    'in_stock' => max((int) ($v->stock ?? 0), (int) ($v->stock_quantity ?? 0)) > 0,
                ])->values()->all(),
        ];
    }

    private function normalizeConfigProduct(string $slug, array $product): array
    {
        return [
            'id' => null,
            'slug' => $slug,
            'name' => $product['name'],
            'display_name' => $this->displayName($product['name']),
            'family' => $product['family'] ?? 'Fine Fragrance',
            'category_id' => null,
            'category' => null,
            'price' => $product['price'] ?? '0',
            'price_value' => (float) str_replace(',', '', $product['price'] ?? '0'),
            'compare_at_price' => null,
            'badge' => $product['badge'] ?? null,
            'image' => $product['image'] ?? null,
            'world_image' => $product['world_image'] ?? ($product['image'] ?? null),
            'images' => array_values(array_filter([
                $product['image'] ?? null,
                $product['world_image'] ?? null,
            ])),
            'audience' => $this->inferAudience(
                $product['name'] ?? '',
                $product['family'] ?? '',
                '',
                ''
            ),
            'theme' => $product['theme'] ?? [],
            'world' => $product['world'] ?? [],
            'description' => null,
            'story' => null,
            'notes' => null,
            'wear' => null,
            'sku' => null,
            'stock' => 99,
            'in_stock' => true,
            'is_featured' => in_array($slug, ['memory-01', 'velvet-oud', 'solar-skin', 'after-dark'], true),
            'variants' => [
                ['id' => null, 'name' => '50 ML', 'size_label' => '50 ML', 'sku' => null, 'price' => $product['price'] ?? '0', 'price_value' => (float) str_replace(',', '', $product['price'] ?? '0'), 'stock' => 99, 'in_stock' => true],
                ['id' => null, 'name' => '75 ML', 'size_label' => '75 ML', 'sku' => null, 'price' => $product['price'] ?? '0', 'price_value' => (float) str_replace(',', '', $product['price'] ?? '0'), 'stock' => 99, 'in_stock' => true],
                ['id' => null, 'name' => '100 ML', 'size_label' => '100 ML', 'sku' => null, 'price' => $product['price'] ?? '0', 'price_value' => (float) str_replace(',', '', $product['price'] ?? '0'), 'stock' => 99, 'in_stock' => true],
            ],
        ];
    }

    private function imageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path));

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Woo/imported media lives on Laravel's public disk. Serve it through a
        // dedicated route so Windows/local hosting does not depend on a storage symlink.
        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            $relative = preg_replace('#^/?storage/#', '', $path);
            return route('store.media', ['path' => $relative]);
        }

        if (Str::startsWith($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return route('store.media', ['path' => ltrim($path, '/')]);
    }

    private function inferAudience(?string ...$values): string
    {
        $haystack = Str::lower(implode(' ', array_filter($values)));

        if (Str::contains($haystack, ['unisex', 'for unisex'])) {
            return 'Unisex';
        }

        if (Str::contains($haystack, ['women', "women's", 'womens', 'female'])) {
            return 'Women';
        }

        if (Str::contains($haystack, [' men ', "men's", 'mens', 'male', 'for men'])) {
            return 'Men';
        }

        return 'Unisex';
    }

    private function displayName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 'Fragrance';
        }

        $clean = preg_split('/\s[-–—]\s(?:Inspired|Impression|Inspired By)\b/i', $name)[0] ?? $name;
        $clean = preg_replace('/\s+Inspired\s+by\s+.+$/i', '', $clean);

        return trim((string) $clean) ?: $name;
    }

    private function plainText(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim((string) $text);
    }

}

<?php

namespace App\Services;

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

            if (!empty($filters['category'])) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
            }

            if (!empty($filters['collection'])) {
                $query->whereHas('collections', fn ($q) => $q->where('slug', $filters['collection']));
            }

            if (!empty($filters['featured'])) {
                $query->where('is_featured', true);
            }

            $sort = $filters['sort'] ?? 'featured';
            match ($sort) {
                'newest' => $query->latest('id'),
                'price-asc' => $query->orderBy('base_price'),
                'price-desc' => $query->orderByDesc('base_price'),
                default => $query->orderByDesc('is_featured')->latest('id'),
            };

            $items = $query->get()->map(fn (Product $product) => $this->normalizeProduct($product));
            if ($items->isNotEmpty()) {
                return $items;
            }

            // When the Laravel catalog already contains active products, an empty
            // filtered result must remain empty instead of silently switching back
            // to the old static demo catalog. This is important for featured/home
            // queries after a WooCommerce import.
            if (Product::query()->where('status', 'active')->exists()) {
                return collect();
            }
        }

        return collect(config('storefront.products', []))
            ->map(fn (array $product, string $slug) => $this->normalizeConfigProduct($slug, $product))
            ->values();
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
        $variant = $product->variants->first();
        $visual = config("storefront.products.{$product->slug}", []);

        $price = $variant?->price ?? $product->base_price ?? 0;
        $stock = $product->variants->isNotEmpty() ? $product->variants->sum('stock') : (int) $product->stock;

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'family' => $product->subtitle ?: ($product->category?->name ?: 'Fine Fragrance'),
            'category_id' => $product->category_id,
            'category' => $product->category?->only(['id', 'name', 'slug']),
            'price' => number_format((float) $price, 0),
            'price_value' => (float) $price,
            'compare_at_price' => $variant?->compare_at_price ?? $product->compare_at_price,
            'badge' => $product->is_featured ? 'Featured' : ($visual['badge'] ?? null),
            'image' => $this->imageUrl($primary?->path) ?: ($visual['image'] ?? null),
            'world_image' => $this->imageUrl($secondary?->path) ?: ($visual['world_image'] ?? $this->imageUrl($primary?->path) ?? $visual['image'] ?? null),
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
            'variants' => $product->variants->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'size_label' => $v->size_label ?: $v->name,
                'sku' => $v->sku,
                'price' => number_format((float) $v->price, 0),
                'price_value' => (float) $v->price,
                'stock' => (int) $v->stock,
                'in_stock' => (int) $v->stock > 0,
            ])->values()->all(),
        ];
    }

    private function normalizeConfigProduct(string $slug, array $product): array
    {
        return [
            'id' => null,
            'slug' => $slug,
            'name' => $product['name'],
            'family' => $product['family'] ?? 'Fine Fragrance',
            'category_id' => null,
            'category' => null,
            'price' => $product['price'] ?? '0',
            'price_value' => (float) str_replace(',', '', $product['price'] ?? '0'),
            'compare_at_price' => null,
            'badge' => $product['badge'] ?? null,
            'image' => $product['image'] ?? null,
            'world_image' => $product['world_image'] ?? ($product['image'] ?? null),
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

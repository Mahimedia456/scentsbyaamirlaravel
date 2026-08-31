<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\Request;

class StorefrontCatalogController extends Controller
{
    public function __construct(private readonly StorefrontCatalogService $catalog) {}

    public function home()
    {
        return view('store.home', [
            'featuredProducts' => $this->catalog->featuredProducts(8),
            'homeProducts' => $this->catalog->allProducts(['sort' => 'featured'])->take(8)->values(),
        ]);
    }

    public function shop(Request $request)
    {
        $filters = $this->shopFilters($request);

        $payload = [
            'products' => $this->catalog->allProducts($filters),
            'collections' => $this->catalog->collections(),
            'categories' => $this->catalog->categories(),
            'filters' => $filters,
            'activeSort' => $filters['sort'],
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('store.partials.catalog-results', $payload)->render(),
                'count' => $payload['products']->count(),
                'filters' => $filters,
                'url' => $request->fullUrl(),
            ]);
        }

        return view('store.shop', $payload);
    }

    public function collections()
    {
        return view('store.collections', ['dbCollections' => $this->catalog->collections()]);
    }

    public function collection(string $slug)
    {
        $collection = $this->catalog->collection($slug);
        abort_unless($collection, 404);

        return view('store.collection-detail', [
            'collectionData' => $collection,
            'slug' => $slug,
        ]);
    }

    public function ingredients()
    {
        $definitions = collect(config('storefront.ingredients', []));

        $ingredients = $definitions->map(function (array $ingredient, string $slug) {
            $products = $this->catalog->allProducts(['family' => $slug]);

            return array_merge($ingredient, [
                'slug' => $slug,
                'products_count' => $products->count(),
            ]);
        })->values();

        return view('store.ingredients', compact('ingredients'));
    }

    public function ingredient(string $slug)
    {
        $ingredient = config("storefront.ingredients.{$slug}");
        abort_unless($ingredient, 404);

        $products = $this->catalog->allProducts(['family' => $slug]);

        return view('store.ingredient-detail', [
            'slug' => $slug,
            'ingredient' => array_merge($ingredient, ['slug' => $slug]),
            'products' => $products,
        ]);
    }

    public function families()
    {
        $families = collect(config('storefront.fragrance_families', []))
            ->map(function (array $family, string $slug) {
                return array_merge($family, [
                    'slug' => $slug,
                    'products_count' => $this->catalog->allProducts(['family' => $slug])->count(),
                ]);
            })->values();

        return view('store.families', compact('families'));
    }

    public function family(string $slug)
    {
        $family = config("storefront.fragrance_families.{$slug}");
        abort_unless($family, 404);

        return view('store.family-detail', [
            'slug' => $slug,
            'family' => array_merge($family, ['slug' => $slug]),
            'products' => $this->catalog->allProducts(['family' => $slug]),
        ]);
    }

    public function gifting()
    {
        $products = $this->catalog->allProducts();

        $testerBoxes = $products
            ->filter(function ($product) {
                $haystack = strtolower(implode(' ', array_filter([
                    $product['display_name'] ?? $product['name'] ?? '',
                    $product['name'] ?? '',
                    $product['slug'] ?? '',
                    $product['description'] ?? '',
                ])));

                return str_contains($haystack, 'tester')
                    || str_contains($haystack, 'sample')
                    || str_contains($haystack, 'discovery box');
            })
            ->values();

        $giftEdit = $products
            ->reject(fn ($product) => $testerBoxes->contains('slug', $product['slug'] ?? null))
            ->sortByDesc(fn ($product) => (bool) ($product['is_featured'] ?? false))
            ->take(6)
            ->values();

        return view('store.gifting', [
            'testerBoxes' => $testerBoxes,
            'giftEdit' => $giftEdit,
        ]);
    }

    public function product(string $slug)
    {
        $product = $this->catalog->product($slug);
        abort_unless($product, 404);

        return view('store.product', [
            'slug' => $slug,
            'productData' => $product,
            'relatedProducts' => $this->catalog->related($slug, $product['category_id'], 4),
        ]);
    }

    private function shopFilters(Request $request): array
    {
        return [
            'search' => trim($request->string('q')->toString()),
            'audience' => $request->string('audience')->toString(),
            'family' => $request->string('family')->toString(),
            'category' => $request->string('category')->toString(),
            'collection' => $request->string('collection')->toString(),
            'availability' => $request->string('availability')->toString(),
            'edit' => $request->string('edit')->toString(),
            'min_price' => $request->filled('min_price') ? (float) $request->input('min_price') : null,
            'max_price' => $request->filled('max_price') ? (float) $request->input('max_price') : null,
            'sort' => $request->string('sort')->toString() ?: 'featured',
        ];
    }
}

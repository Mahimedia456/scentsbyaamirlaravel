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
        return view('store.shop', [
            'products' => $this->catalog->allProducts([
                'category' => $request->string('category')->toString(),
                'collection' => $request->string('collection')->toString(),
                'sort' => $request->string('sort')->toString() ?: 'featured',
            ]),
            'collections' => $this->catalog->collections(),
            'activeSort' => $request->string('sort')->toString() ?: 'featured',
        ]);
    }

    public function collections()
    {
        return view('store.collections', ['dbCollections' => $this->catalog->collections()]);
    }

    public function collection(string $slug)
    {
        $collection = $this->catalog->collection($slug);
        abort_unless($collection, 404);

        return view('store.collection-detail', ['collectionData' => $collection, 'slug' => $slug]);
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
}

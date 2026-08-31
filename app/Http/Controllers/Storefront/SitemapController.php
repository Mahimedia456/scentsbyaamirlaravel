<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $static = collect([
            route('home'),
            route('shop'),
            route('collections'),
            route('finder'),
            route('ingredients'),
            route('families'),
            route('journal'),
            route('about'),
            route('gifting'),
            route('search'),
            route('contact'),
        ])->filter()->unique()->values();

        $products = Product::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->pluck('slug')
            ->map(fn ($slug) => route('product.show', $slug))
            ->values();

        $urls = $static->concat($products);

        $xml = view('store.sitemap', compact('urls'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}

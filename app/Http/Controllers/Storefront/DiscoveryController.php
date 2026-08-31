<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscoveryController extends Controller
{
    public function search(Request $request, StorefrontCatalogService $catalog)
    {
        $q = trim((string) $request->query('q', ''));
        $products = $catalog->allProducts();

        $results = $q === ''
            ? collect()
            : $products->filter(function ($product) use ($q) {
                $haystack = implode(' ', [
                    $product['display_name'] ?? $product['name'] ?? '',
                    $product['name'] ?? '',
                    $product['family'] ?? '',
                    $product['audience'] ?? '',
                    $product['description'] ?? '',
                    $product['story'] ?? '',
                    is_array($product['notes'] ?? null) ? implode(' ', $product['notes']) : ($product['notes'] ?? ''),
                    $product['category']['name'] ?? '',
                ]);

                return Str::contains(Str::lower($haystack), Str::lower($q));
            })->values();

        $payload = compact('q', 'results');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('store.partials.search-results', $payload)->render(),
                'count' => $results->count(),
                'query' => $q,
                'url' => $request->fullUrl(),
            ]);
        }

        return view('store.search', $payload);
    }

    public function finder(Request $request, StorefrontCatalogService $catalog)
    {
        $answers = [
            'mood' => $request->query('mood'),
            'intensity' => $request->query('intensity'),
            'occasion' => $request->query('occasion'),
            'audience' => $request->query('audience'),
        ];

        $recommendations = collect();

        if ($this->complete($answers)) {
            $recommendations = $this->recommendations($catalog, $answers);
        }

        $payload = compact('answers', 'recommendations');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('store.partials.finder-results', $payload)->render(),
                'count' => $recommendations->count(),
                'answers' => $answers,
                'url' => $request->fullUrl(),
            ]);
        }

        return view('store.fragrance-finder', $payload);
    }

    private function recommendations(StorefrontCatalogService $catalog, array $answers)
    {
        $terms = $this->terms($answers);

        return $catalog->allProducts()
            ->map(function ($product) use ($terms, $answers) {
                $haystack = Str::lower(implode(' ', [
                    $product['display_name'] ?? $product['name'] ?? '',
                    $product['name'] ?? '',
                    $product['family'] ?? '',
                    $product['audience'] ?? '',
                    $product['description'] ?? '',
                    $product['story'] ?? '',
                    is_array($product['notes'] ?? null) ? implode(' ', $product['notes']) : ($product['notes'] ?? ''),
                    $product['wear'] ?? '',
                    $product['category']['name'] ?? '',
                ]));

                $matched = collect($terms)
                    ->filter(fn ($term) => Str::contains($haystack, Str::lower($term)))
                    ->values();

                $score = $matched->count() * 2;

                if (($product['is_featured'] ?? false) === true) {
                    $score += 1;
                }

                $audience = Str::lower((string) ($answers['audience'] ?? ''));
                $productAudience = Str::lower((string) ($product['audience'] ?? ''));

                if ($audience === '' || $audience === 'any' || $productAudience === $audience || $productAudience === 'unisex') {
                    $score += 2;
                } else {
                    $score -= 2;
                }

                return [
                    'product' => $product,
                    'score' => $score,
                    'matched' => $matched->take(3)->all(),
                ];
            })
            ->sortByDesc('score')
            ->take(4)
            ->values();
    }

    private function complete(array $answers): bool
    {
        return filled($answers['mood'])
            && filled($answers['intensity'])
            && filled($answers['occasion']);
    }

    private function terms(array $answers): array
    {
        $map = [
            'Quiet' => ['musk', 'skin', 'soft', 'clean', 'sandalwood'],
            'Magnetic' => ['oud', 'amber', 'resin', 'spice', 'vanilla'],
            'Fresh' => ['citrus', 'fresh', 'bergamot', 'aquatic', 'green'],
            'Warm' => ['amber', 'vanilla', 'wood', 'warm', 'sandalwood'],
            'Dark' => ['oud', 'dark', 'smoke', 'resin', 'leather'],
            'Celebratory' => ['floral', 'bright', 'spice', 'rose', 'jasmine'],

            'Soft' => ['skin', 'musk', 'soft', 'floral'],
            'Moderate' => ['wood', 'floral', 'balanced', 'fresh'],
            'Strong' => ['oud', 'amber', 'resin', 'spice'],

            'Everyday' => ['fresh', 'skin', 'musk', 'clean', 'citrus'],
            'Evening' => ['oud', 'amber', 'dark', 'resin', 'vanilla'],
            'Formal' => ['wood', 'oud', 'floral', 'amber'],
            'Gifting' => ['signature', 'featured', 'floral', 'musk', 'amber'],
        ];

        $terms = [];

        foreach (['mood', 'intensity', 'occasion'] as $key) {
            $value = $answers[$key] ?? null;

            if ($value && isset($map[$value])) {
                $terms = array_merge($terms, $map[$value]);
            }
        }

        return array_values(array_unique($terms));
    }
}

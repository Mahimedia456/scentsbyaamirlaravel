<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Collection as ProductCollection;
use App\Models\JournalPost;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $maps = collect([
            ['route' => 'sitemap.pages', 'lastmod' => null],
            ['route' => 'sitemap.products', 'lastmod' => $this->maxUpdatedAt('products')],
            ['route' => 'sitemap.collections', 'lastmod' => $this->maxUpdatedAt('collections')],
            ['route' => 'sitemap.journal', 'lastmod' => $this->maxUpdatedAt('journal_posts')],
        ])->map(fn (array $map) => [
            'loc' => $this->publicRoute($map['route']),
            'lastmod' => $this->dateValue($map['lastmod']),
        ]);

        return $this->xml('store.sitemap-index', compact('maps'));
    }

    public function pages(): Response
    {
        $routeNames = [
            'home',
            'shop',
            'collections',
            'finder',
            'ingredients',
            'families',
            'journal',
            'about',
            'gifting',
            'contact',
            'faq',
            'shipping',
            'returns',
            'gift-wrapping',
            'personalized-message',
            'privacy',
            'terms',
            'cookies',
            'accessibility',
            'services',
        ];

        $urls = collect($routeNames)
            ->filter(fn (string $name) => Route::has($name))
            ->map(fn (string $name) => [
                'loc' => $this->publicRoute($name),
                'lastmod' => null,
            ]);

        if (Route::has('ingredients.show')) {
            $ingredientUrls = collect(array_keys((array) config('storefront.ingredients', [])))
                ->map(fn (string $slug) => [
                    'loc' => $this->publicRoute('ingredients.show', ['slug' => $slug]),
                    'lastmod' => null,
                ]);
            $urls = $urls->concat($ingredientUrls);
        }

        if (Route::has('families.show')) {
            $familyUrls = collect(array_keys((array) config('storefront.fragrance_families', [])))
                ->map(fn (string $slug) => [
                    'loc' => $this->publicRoute('families.show', ['slug' => $slug]),
                    'lastmod' => null,
                ]);
            $urls = $urls->concat($familyUrls);
        }

        return $this->urlset($urls);
    }

    public function products(): Response
    {
        if (!Schema::hasTable('products') || !Route::has('product.show')) {
            return $this->urlset($this->fallbackProducts());
        }

        $query = Product::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

        if (Schema::hasColumn('products', 'status')) {
            $query->where('status', 'active');
        }

        if (Schema::hasColumn('products', 'is_active')) {
            $query->where('is_active', true);
        }

        $products = $query
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->map(fn (Product $product) => [
                'loc' => $this->publicRoute('product.show', ['slug' => $product->slug]),
                'lastmod' => $this->dateValue($product->updated_at),
            ]);

        if ($products->isEmpty()) {
            $products = $this->fallbackProducts();
        }

        return $this->urlset($products);
    }

    public function collections(): Response
    {
        $urls = collect();

        if (
            Schema::hasTable('collections') &&
            Schema::hasTable('collection_product') &&
            Route::has('collections.show')
        ) {
            $query = ProductCollection::query()
                ->whereNotNull('slug')
                ->where('slug', '!=', '');

            if (Schema::hasColumn('collections', 'is_active')) {
                $query->where('is_active', true);
            }

            $query->whereHas('products', function (Builder $products) {
                if (Schema::hasColumn('products', 'status')) {
                    $products->where('status', 'active');
                }
                if (Schema::hasColumn('products', 'is_active')) {
                    $products->where('is_active', true);
                }
            });

            $urls = $query
                ->orderBy('id')
                ->get(['slug', 'updated_at'])
                ->map(fn (ProductCollection $collection) => [
                    'loc' => $this->publicRoute('collections.show', ['slug' => $collection->slug]),
                    'lastmod' => $this->dateValue($collection->updated_at),
                ]);
        }

        if ($urls->isEmpty() && Route::has('collections.show')) {
            $fallbackSlugs = collect([
                'signature-worlds',
                'nocturnal',
                'light-studies',
            ]);

            $urls = $fallbackSlugs->map(fn (string $slug) => [
                'loc' => $this->publicRoute('collections.show', ['slug' => $slug]),
                'lastmod' => null,
            ]);
        }

        return $this->urlset($urls);
    }

    public function journal(): Response
    {
        $urls = collect();

        if (Schema::hasTable('journal_posts') && Route::has('journal.show')) {
            $urls = JournalPost::query()
                ->where('status', 'published')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->where(fn (Builder $query) => $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now()))
                ->orderByDesc('published_at')
                ->get(['slug', 'updated_at', 'published_at'])
                ->map(fn (JournalPost $post) => [
                    'loc' => $this->publicRoute('journal.show', ['slug' => $post->slug]),
                    'lastmod' => $this->dateValue($post->updated_at ?: $post->published_at),
                ]);
        }

        if ($urls->isEmpty() && Route::has('journal.show')) {
            $urls = collect(array_keys((array) config('storefront.journal', [])))
                ->map(fn (string $slug) => [
                    'loc' => $this->publicRoute('journal.show', ['slug' => $slug]),
                    'lastmod' => null,
                ]);
        }

        return $this->urlset($urls);
    }

    private function fallbackProducts(): Collection
    {
        if (!Route::has('product.show')) {
            return collect();
        }

        return collect(array_keys((array) config('storefront.products', [])))
            ->map(fn (string $slug) => [
                'loc' => $this->publicRoute('product.show', ['slug' => $slug]),
                'lastmod' => null,
            ]);
    }

    private function urlset(Collection $urls): Response
    {
        $urls = $urls
            ->filter(fn (array $row) => !empty($row['loc']))
            ->unique('loc')
            ->values();

        return $this->xml('store.sitemap-urlset', compact('urls'));
    }

    private function xml(string $view, array $data): Response
    {
        return response()
            ->view($view, $data, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('X-Robots-Tag', 'noindex, follow');
    }

    private function publicRoute(string $name, array $parameters = []): string
    {
        $relative = route($name, $parameters, false);

        return rtrim((string) config('sitemap.base_url'), '/').'/'.ltrim($relative, '/');
    }

    private function maxUpdatedAt(string $table): mixed
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'updated_at')) {
            return null;
        }

        return \DB::table($table)->max('updated_at');
    }

    private function dateValue(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toAtomString();
        } catch (\Throwable) {
            return null;
        }
    }
}

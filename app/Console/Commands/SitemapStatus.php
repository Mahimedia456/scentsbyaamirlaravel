<?php

namespace App\Console\Commands;

use App\Models\Collection as ProductCollection;
use App\Models\JournalPost;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SitemapStatus extends Command
{
    protected $signature = 'seo:sitemap-status';
    protected $description = 'Show sitemap route registration, canonical base URL and indexable DB counts.';

    public function handle(): int
    {
        $productCount = 0;
        if (Schema::hasTable('products')) {
            $query = Product::query()->whereNotNull('slug');
            if (Schema::hasColumn('products', 'status')) $query->where('status', 'active');
            if (Schema::hasColumn('products', 'is_active')) $query->where('is_active', true);
            $productCount = $query->count();
        }

        $collectionCount = 0;
        if (Schema::hasTable('collections')) {
            $query = ProductCollection::query()->whereNotNull('slug');
            if (Schema::hasColumn('collections', 'is_active')) $query->where('is_active', true);
            $collectionCount = $query->count();
        }

        $journalCount = 0;
        if (Schema::hasTable('journal_posts')) {
            $journalCount = JournalPost::query()
                ->where('status', 'published')
                ->whereNotNull('slug')
                ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->count();
        }

        $this->line('Canonical sitemap base: '.config('sitemap.base_url'));
        $this->newLine();

        $this->table(
            ['Sitemap', 'Registered'],
            collect([
                'sitemap.index',
                'sitemap.pages',
                'sitemap.products',
                'sitemap.collections',
                'sitemap.journal',
            ])->map(fn ($name) => [$name, Route::has($name) ? 'YES' : 'NO'])->all()
        );

        $this->newLine();
        $this->table(
            ['Indexable source', 'Count'],
            [
                ['Active products', $productCount],
                ['Active collections', $collectionCount],
                ['Published Journal posts', $journalCount],
            ]
        );

        if (!Route::has('sitemap.index')) {
            $this->warn('Run: php artisan site:install-seo-navigation');
        }

        return self::SUCCESS;
    }
}

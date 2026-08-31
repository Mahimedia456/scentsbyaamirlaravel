<?php

namespace App\Console\Commands;

use App\Models\WooCommerceImportRun;
use App\Services\WooCommerceImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SyncWooCommerce extends Command
{
    protected $signature = 'woocommerce:sync
        {--url= : WordPress/WooCommerce site URL, e.g. https://shop.example.com}
        {--key= : WooCommerce REST API consumer key}
        {--secret= : WooCommerce REST API consumer secret}
        {--no-categories : Skip categories}
        {--no-products : Skip products and variants}
        {--no-customers : Skip customers}
        {--no-orders : Skip historical orders}
        {--no-media : Skip product image downloads}';

    protected $description = 'Create and execute a complete idempotent WooCommerce to Laravel import run';

    public function handle(WooCommerceImporter $importer): int
    {
        if (!Schema::hasTable('woocommerce_import_runs') || !Schema::hasTable('woocommerce_import_maps')) {
            $this->error('WooCommerce migration tables are missing. Run: php artisan migrate');
            return self::FAILURE;
        }

        $url = rtrim((string) ($this->option('url') ?: env('WOOCOMMERCE_URL')), '/');
        $key = (string) ($this->option('key') ?: env('WOOCOMMERCE_CONSUMER_KEY'));
        $secret = (string) ($this->option('secret') ?: env('WOOCOMMERCE_CONSUMER_SECRET'));

        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Set WOOCOMMERCE_URL in .env or pass --url=https://your-wordpress-site.com');
            return self::FAILURE;
        }

        if ($key === '' || $secret === '') {
            $this->error('Set WOOCOMMERCE_CONSUMER_KEY and WOOCOMMERCE_CONSUMER_SECRET in .env, or pass --key/--secret.');
            return self::FAILURE;
        }

        $options = [
            'categories' => !$this->option('no-categories'),
            'products' => !$this->option('no-products'),
            'customers' => !$this->option('no-customers'),
            'orders' => !$this->option('no-orders'),
            'media' => !$this->option('no-media'),
        ];

        $run = WooCommerceImportRun::create([
            'status' => 'pending',
            'source_url' => $url,
            'options' => $options,
            'summary' => [],
        ]);

        $this->info("Created WooCommerce import run #{$run->id}");
        $this->line('Source: '.$url);
        $this->line('Mode: create/update existing Laravel records + insert missing records.');

        try {
            $result = $importer->run($run, $key, $secret);
            $summary = $result->summary ?: [];

            $this->newLine();
            $this->info("WooCommerce import #{$result->id} completed.");
            $this->table(
                ['Entity', 'Processed'],
                collect($summary)->map(fn ($value, $name) => [$name, $value])->values()->all()
            );

            $this->call('storefront:repair-imported-catalog');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Import failed: '.$e->getMessage());
            $this->line("Review Admin > WooCommerce Migration Center, run #{$run->id}, and storage/logs/laravel.log.");
            return self::FAILURE;
        }
    }
}

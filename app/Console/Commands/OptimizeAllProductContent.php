<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductContentParser;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class OptimizeAllProductContent extends Command
{
    protected $signature = 'storefront:optimize-all-product-content
                            {--dry-run}
                            {--force}
                            {--active-only=1}';

    protected $description = 'Optimize all products into structured description, story, Top/Heart/Base notes and wear fields.';

    public function handle(ProductContentParser $parser): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $query = Product::query()->orderBy('name');

        if ((bool) $this->option('active-only')) {
            $query->where('status', 'active');
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->warn('No products found.');
            return self::SUCCESS;
        }

        $changed = 0;
        $rows = [];

        foreach ($products as $product) {
            $parsed = $parser->parse(
                $product->description,
                $product->notes,
                $product->story,
                $product->wear
            );

            $updates = [];

            foreach ([
                'description',
                'story',
                'top_notes',
                'heart_notes',
                'base_notes',
                'wear',
            ] as $field) {
                $newValue = trim((string) ($parsed[$field] ?? ''));

                if ($newValue === '') {
                    continue;
                }

                $currentValue = trim((string) ($product->{$field} ?? ''));

                if (
                    $force
                    || $currentValue === ''
                    || $currentValue !== $newValue
                ) {
                    $updates[$field] = $newValue;
                }
            }

            if (filled($parsed['notes_summary'] ?? null)) {
                $updates['notes'] = $parsed['notes_summary'];
            }

            $rows[] = [
                Str::limit($product->name, 42),
                filled($parsed['top_notes'] ?? null) ? 'YES' : '—',
                filled($parsed['heart_notes'] ?? null) ? 'YES' : '—',
                filled($parsed['base_notes'] ?? null) ? 'YES' : '—',
                filled($parsed['description'] ?? null) ? 'YES' : '—',
                filled($parsed['story'] ?? null) ? 'YES' : '—',
                filled($parsed['wear'] ?? null) ? 'YES' : '—',
                $updates === []
                    ? 'OK'
                    : implode(', ', array_keys($updates)),
            ];

            if ($updates === []) {
                continue;
            }

            $changed++;

            if (!$dryRun) {
                $product->forceFill($updates)->save();
            }
        }

        $this->table(
            [
                'Product',
                'Top',
                'Heart',
                'Base',
                'Description',
                'Story',
                'Wear',
                'Action',
            ],
            $rows
        );

        $this->newLine();

        $this->info(
            $dryRun
                ? "Dry-run complete: {$changed} product(s) would be updated."
                : "Optimization complete: {$changed} product(s) updated."
        );

        $this->line(
            'Missing note groups are not invented. Only source-backed values are written.'
        );

        return self::SUCCESS;
    }
}
<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductContentParser;
use Illuminate\Console\Command;

class NormalizeProductContent extends Command
{
    protected $signature = 'storefront:normalize-product-content
                            {--dry-run}
                            {--force}';

    protected $description = 'Split legacy Woo product content into description and structured Top/Heart/Base notes.';

    public function handle(ProductContentParser $parser): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $changed = 0;

        Product::query()
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($parser, $dryRun, $force, &$changed) {
                foreach ($products as $product) {
                    $parsed = $parser->parse($product->description, $product->notes);
                    $updates = [];

                    foreach (['top_notes', 'heart_notes', 'base_notes'] as $field) {
                        if (
                            filled($parsed[$field] ?? null)
                            && ($force || blank($product->{$field}))
                        ) {
                            $updates[$field] = $parsed[$field];
                        }
                    }

                    if (
                        filled($parsed['description'] ?? null)
                        && ($force || $parsed['description'] !== $product->description)
                    ) {
                        $updates['description'] = $parsed['description'];
                    }

                    if (filled($parsed['notes_summary'] ?? null)) {
                        $updates['notes'] = $parsed['notes_summary'];
                    }

                    if ($updates === []) {
                        $this->line("OK    {$product->name}");
                        continue;
                    }

                    $changed++;

                    $this->line(
                        ($dryRun ? 'WOULD ' : 'FIX   ')
                        . $product->name
                        . ' | '
                        . implode(', ', array_keys($updates))
                    );

                    if (!$dryRun) {
                        $product->forceFill($updates)->save();
                    }
                }
            });

        $this->newLine();
        $this->info(($dryRun ? 'Dry-run: ' : '') . "{$changed} product(s) require normalization.");

        return self::SUCCESS;
    }
}

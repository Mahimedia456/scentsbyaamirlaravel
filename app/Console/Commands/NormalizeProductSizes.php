<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class NormalizeProductSizes extends Command
{
    protected $signature = 'storefront:normalize-product-sizes {--dry-run : Show changes without writing them}';

    protected $description = 'Normalize Scents by Aamir simple-product sizes: tester products only use 5 ML; all other fragrances use 50 ML.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $changed = 0;

        Product::query()
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($dryRun, &$changed) {
                foreach ($products as $product) {
                    $name = strtolower((string) $product->name);
                    $slug = strtolower((string) $product->slug);
                    $isTester = str_contains($name, 'tester') || str_contains($slug, 'tester');

                    $target = $isTester ? '5 ML' : '50 ML';

                    if ((string) $product->size_label === $target) {
                        continue;
                    }

                    $this->line(sprintf(
                        '%s [%s]: %s -> %s',
                        $product->name,
                        $product->slug,
                        $product->size_label ?: '(empty)',
                        $target
                    ));

                    if (!$dryRun) {
                        $product->forceFill(['size_label' => $target])->save();
                    }

                    $changed++;
                }
            });

        $this->newLine();
        $this->info(($dryRun ? 'Would change ' : 'Changed ') . $changed . ' product(s).');

        return self::SUCCESS;
    }
}

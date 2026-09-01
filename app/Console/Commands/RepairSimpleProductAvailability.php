<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class RepairSimpleProductAvailability extends Command
{
    protected $signature = 'storefront:repair-simple-products {--dry-run}';

    protected $description = 'Repair Woo simple-product storefront size and availability without creating variants.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $changed = 0;

        foreach ($products as $product) {
            $haystack = strtolower(trim($product->name . ' ' . $product->slug));
            $isTester = str_contains($haystack, 'tester');

            // Source-of-truth product rule agreed for this catalog:
            // tester products = 5 ML; all normal fragrances = 50 ML.
            $size = $isTester ? '5 ML' : '50 ML';

            $updates = [];

            if ((string) $product->size_label !== $size) {
                $updates['size_label'] = $size;
            }

            /*
             * Woo import consists of simple products. For untracked active
             * fragrances, availability is a boolean rather than numeric stock.
             * Normal active fragrances imported as in-stock should remain
             * purchasable; tester products retain their current availability.
             */
            if (!$product->track_inventory && !$isTester && !$product->is_in_stock) {
                $updates['is_in_stock'] = true;
            }

            if ($updates === []) {
                $this->line("OK   {$product->name} | {$size}");
                continue;
            }

            $changed++;

            $this->line(
                ($dryRun ? 'WOULD ' : 'FIX  ')
                . $product->name
                . ' | '
                . json_encode($updates, JSON_UNESCAPED_SLASHES)
            );

            if (!$dryRun) {
                $product->forceFill($updates)->save();
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'Dry-run: ' : '') . "{$changed} product(s) require repair.");

        return self::SUCCESS;
    }
}

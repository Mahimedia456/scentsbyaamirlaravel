<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditStorefrontStock extends Command
{
    protected $signature = 'storefront:audit-stock
                            {--fix-legacy : Copy positive legacy stock_quantity values into stock when stock is zero}';

    protected $description = 'Audit simple-product availability, tracked stock and variant stock for the storefront.';

    public function handle(): int
    {
        if (!Schema::hasTable('products')) {
            $this->error('products table not found.');
            return self::FAILURE;
        }

        $hasProductLegacy = Schema::hasColumn('products', 'stock_quantity');
        $hasVariantLegacy = Schema::hasTable('product_variants')
            && Schema::hasColumn('product_variants', 'stock_quantity');

        if ($this->option('fix-legacy')) {
            if ($hasProductLegacy && Schema::hasColumn('products', 'stock')) {
                DB::table('products')
                    ->where('stock', 0)
                    ->where('stock_quantity', '>', 0)
                    ->update([
                        'stock' => DB::raw('stock_quantity'),
                        'is_in_stock' => true,
                    ]);
            }

            if ($hasVariantLegacy && Schema::hasColumn('product_variants', 'stock')) {
                DB::table('product_variants')
                    ->where('stock', 0)
                    ->where('stock_quantity', '>', 0)
                    ->update(['stock' => DB::raw('stock_quantity')]);
            }

            $this->info('Legacy numeric stock sync completed.');
        }

        $products = Product::query()
            ->where('status', 'active')
            ->with([
                'variants' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
            ])
            ->orderBy('name')
            ->get();

        foreach ($products as $product) {
            $trackedStock = max(
                (int) ($product->stock ?? 0),
                (int) ($product->stock_quantity ?? 0)
            );

            $this->newLine();
            $this->line("<info>{$product->name}</info>");
            $this->line("  slug={$product->slug}");
            $this->line("  size=" . ($product->size_label ?: '-'));

            if ($product->variants->isEmpty()) {
                $mode = (bool) ($product->track_inventory ?? false)
                    ? 'tracked quantity'
                    : 'simple availability';

                $available = (bool) ($product->track_inventory ?? false)
                    ? $trackedStock > 0
                    : (bool) ($product->is_in_stock ?? false);

                $this->line("  mode={$mode}");
                $this->line("  numeric_stock={$trackedStock}");
                $this->line("  is_in_stock=" . ($available ? 'YES' : 'NO'));
                $this->line('  variants=none (correct for Woo simple product)');
                continue;
            }

            $this->line('  mode=variants');

            foreach ($product->variants as $variant) {
                $stock = max(
                    (int) ($variant->stock ?? 0),
                    (int) ($variant->stock_quantity ?? 0)
                );

                $this->line(sprintf(
                    '  - %s | sku=%s | stock=%d | price=%s',
                    $variant->size_label ?: $variant->name,
                    $variant->sku ?: '-',
                    $stock,
                    number_format((float) $variant->price)
                ));
            }
        }

        return self::SUCCESS;
    }
}

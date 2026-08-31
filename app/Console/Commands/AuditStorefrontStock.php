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

    protected $description = 'Show product/variant storefront stock and optionally repair legacy stock_quantity -> stock mismatches.';

    public function handle(): int
    {
        if (!Schema::hasTable('products')) {
            $this->error('products table not found.');
            return self::FAILURE;
        }

        $hasProductLegacy = Schema::hasColumn('products', 'stock_quantity');
        $hasVariantLegacy = Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'stock_quantity');

        if ($this->option('fix-legacy')) {
            if ($hasProductLegacy && Schema::hasColumn('products', 'stock')) {
                DB::table('products')
                    ->where('stock', 0)
                    ->where('stock_quantity', '>', 0)
                    ->update(['stock' => DB::raw('stock_quantity')]);
            }

            if ($hasVariantLegacy && Schema::hasColumn('product_variants', 'stock')) {
                DB::table('product_variants')
                    ->where('stock', 0)
                    ->where('stock_quantity', '>', 0)
                    ->update(['stock' => DB::raw('stock_quantity')]);
            }

            $this->info('Legacy stock sync completed.');
        }

        $products = Product::query()
            ->where('status', 'active')
            ->with(['variants' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('name')
            ->get();

        foreach ($products as $product) {
            $productStock = max(
                (int) ($product->stock ?? 0),
                (int) ($product->stock_quantity ?? 0)
            );

            $this->newLine();
            $this->line("<info>{$product->name}</info> [{$product->slug}] product stock={$productStock}");

            if ($product->variants->isEmpty()) {
                $this->line('  - no active variants');
                continue;
            }

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

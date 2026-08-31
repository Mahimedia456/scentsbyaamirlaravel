<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class StorefrontCommerceService
{
    public function validateCart(array $lines): array
    {
        $items = collect($lines)->map(fn (array $line) => $this->validateLine($line));

        return [
            'items' => $items->values()->all(),
            'count' => $items->where('available', true)->sum('qty'),
            'subtotal' => round((float) $items->where('available', true)->sum('line_total'), 2),
            'has_adjustments' => $items->contains(fn (array $item) => ($item['status'] ?? 'ok') !== 'ok'),
        ];
    }

    public function resolveWishlist(array $items): array
    {
        return collect($items)->map(function (array $item) {
            $product = $this->findProduct($item);

            if (!$product) {
                $fallback = !empty($item['slug']) ? config('storefront.products.' . $item['slug']) : null;
                if (is_array($fallback)) {
                    $price = $this->toFloat($fallback['price'] ?? $item['price_value'] ?? $item['price'] ?? 0);
                    return array_merge($item, [
                        'product_id' => null,
                        'slug' => $item['slug'],
                        'name' => $fallback['name'] ?? ($item['name'] ?? 'Fragrance'),
                        'family' => $fallback['family'] ?? ($item['family'] ?? 'Fine Fragrance'),
                        'price' => number_format($price, 0, '.', ','),
                        'price_value' => $price,
                        'image' => $fallback['image'] ?? ($item['image'] ?? null),
                        'available' => true,
                        'stock' => 99,
                        'status' => 'fallback',
                    ]);
                }

                return array_merge($item, [
                    'available' => false,
                    'status' => 'unavailable',
                ]);
            }

            $primary = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
            $variant = $product->variants->first(fn ($v) => $v->is_active && $this->variantStock($v) > 0)
                ?: $product->variants->firstWhere('is_active', true);
            $price = (float) ($variant?->price ?? $product->base_price ?? 0);
            $stock = $product->variants->where('is_active', true)->isNotEmpty()
                ? (int) $product->variants->where('is_active', true)->sum(fn ($v) => $this->variantStock($v))
                : $this->productStock($product);

            return [
                'product_id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'family' => $product->subtitle ?: ($product->category?->name ?: 'Fine Fragrance'),
                'price' => number_format($price, 0, '.', ','),
                'price_value' => $price,
                'image' => $primary?->path ? $this->imageUrl($primary->path) : Arr::get($item, 'image'),
                'available' => $stock > 0,
                'stock' => $stock,
                'status' => $stock > 0 ? 'ok' : 'out_of_stock',
            ];
        })->values()->all();
    }

    private function validateLine(array $line): array
    {
        // Static storefront fallback remains usable until a real catalog exists.
        if (empty($line['product_id']) && empty($line['variant_id'])) {
            $qty = max(1, (int) ($line['qty'] ?? 1));
            $price = $this->toFloat($line['price_value'] ?? $line['price'] ?? 0);

            return array_merge($line, [
                'line_key' => $line['line_key'] ?? $this->fallbackKey($line),
                'qty' => $qty,
                'price_value' => $price,
                'available' => true,
                'status' => 'fallback',
                'line_total' => round($price * $qty, 2),
            ]);
        }

        $product = $this->findProduct($line);
        if (!$product) {
            return array_merge($line, [
                'available' => false,
                'status' => 'product_unavailable',
                'line_total' => 0,
            ]);
        }

        $variant = null;
        if (!empty($line['variant_id'])) {
            $variant = $product->variants->firstWhere('id', (int) $line['variant_id']);
        }

        if (!$variant && !empty($line['sku'])) {
            $variant = $product->variants->firstWhere('sku', $line['sku']);
        }

        if (!$variant && $product->variants->isNotEmpty()) {
            $variant = $product->variants->first(fn ($v) => $this->variantStock($v) > 0)
                ?: $product->variants->first();
        }

        $stock = $variant ? $this->variantStock($variant) : $this->productStock($product);
        $requestedQty = max(1, (int) ($line['qty'] ?? 1));
        $qty = min($requestedQty, max(0, $stock));
        $price = (float) ($variant?->price ?? $product->base_price ?? 0);
        $available = $stock > 0 && $qty > 0;
        $status = !$available ? 'out_of_stock' : ($qty !== $requestedQty ? 'quantity_adjusted' : 'ok');
        $primary = $product->images->firstWhere('is_primary', true) ?: $product->images->first();

        return [
            'line_key' => $this->lineKey($product->id, $variant?->id, $line),
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'family' => $product->subtitle ?: ($product->category?->name ?: 'Fine Fragrance'),
            'sku' => $variant?->sku ?: $product->sku,
            'size' => $variant?->size_label ?: ($variant?->name ?: ($line['size'] ?? null)),
            'price' => number_format($price, 0, '.', ','),
            'price_value' => $price,
            'image' => $primary?->path ? $this->imageUrl($primary->path) : ($line['image'] ?? null),
            'qty' => $qty,
            'stock' => $stock,
            'available' => $available,
            'status' => $status,
            'line_total' => round($price * $qty, 2),
        ];
    }

    private function findProduct(array $item): ?Product
    {
        if (!Schema::hasTable('products')) {
            return null;
        }

        return Product::query()
            ->where('status', 'active')
            ->when(!empty($item['product_id']), fn ($q) => $q->whereKey((int) $item['product_id']))
            ->when(empty($item['product_id']) && !empty($item['slug']), fn ($q) => $q->where('slug', $item['slug']))
            ->with([
                'category',
                'images',
                'variants' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->first();
    }

    private function lineKey(int $productId, ?int $variantId, array $line): string
    {
        return $variantId
            ? "product:{$productId}:variant:{$variantId}"
            : "product:{$productId}:size:" . strtolower((string) ($line['size'] ?? 'default'));
    }

    private function fallbackKey(array $line): string
    {
        return 'fallback:' . ($line['slug'] ?? 'product') . ':' . strtolower((string) ($line['size'] ?? 'default'));
    }

    private function variantStock(ProductVariant $variant): int
    {
        return max(
            (int) ($variant->stock ?? 0),
            (int) ($variant->stock_quantity ?? 0)
        );
    }

    private function productStock(Product $product): int
    {
        return max(
            (int) ($product->stock ?? 0),
            (int) ($product->stock_quantity ?? 0)
        );
    }

    private function toFloat(mixed $value): float
    {
        return (float) str_replace(',', '', (string) $value);
    }

    private function imageUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}

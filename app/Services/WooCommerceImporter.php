<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\WooCommerceImportMap;
use App\Models\WooCommerceImportRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WooCommerceImporter
{
    private string $base;
    private string $key;
    private string $secret;
    private WooCommerceImportRun $run;
    private array $stats = [];

    public function run(WooCommerceImportRun $run, string $key, string $secret): WooCommerceImportRun
    {
        $this->run = $run;
        $this->base = rtrim((string) $run->source_url, '/') . '/wp-json/wc/v3';
        $this->key = $key;
        $this->secret = $secret;

        $this->stats = [
            'categories' => 0,
            'products' => 0,
            'variants' => 0,
            'customers' => 0,
            'orders' => 0,
            'media' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $run->update([
            'status' => 'running',
            'started_at' => now(),
            'finished_at' => null,
            'last_error' => null,
            'summary' => $this->stats,
        ]);

        try {
            $options = $run->options ?: [];

            if (!empty($options['categories'])) $this->categories();
            if (!empty($options['products'])) $this->products(!empty($options['media']));
            if (!empty($options['customers'])) $this->customers();
            if (!empty($options['orders'])) $this->orders();

            $run->update([
                'status' => 'completed',
                'summary' => $this->stats,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->stats['errors']++;

            $run->update([
                'status' => 'failed',
                'summary' => $this->stats,
                'last_error' => Str::limit($e->getMessage(), 60000, ''),
                'finished_at' => now(),
            ]);

            throw $e;
        }

        return $run->fresh();
    }

    private function pages(string $endpoint, callable $fn, array $params = []): void
    {
        for ($page = 1; ; $page++) {
            $response = $this->get($endpoint, array_merge([
                'per_page' => 50,
                'page' => $page,
            ], $params));

            $rows = $response->json();

            if (!is_array($rows) || count($rows) === 0) break;

            foreach ($rows as $row) {
                try {
                    $fn($row);
                } catch (\Throwable $e) {
                    $this->stats['errors']++;
                    $this->map(
                        $endpoint,
                        (int) ($row['id'] ?? 0),
                        null,
                        'error',
                        Str::limit($e->getMessage(), 2000, '')
                    );
                }
            }

            if (count($rows) < 50) break;
        }
    }

    private function get(string $endpoint, array $query = [])
    {
        $response = Http::withBasicAuth($this->key, $this->secret)
            ->acceptJson()
            ->timeout(45)
            ->retry(2, 500)
            ->get($this->base . '/' . ltrim($endpoint, '/'), $query);

        $response->throw();

        return $response;
    }

    private function categories(): void
    {
        $this->pages('products/categories', function ($woo) {
            $category = Category::updateOrCreate(
                ['slug' => $this->slug($woo['slug'] ?? $woo['name'] ?? 'category-' . $woo['id'])],
                [
                    'name' => $woo['name'] ?? 'Category',
                    'description' => strip_tags($woo['description'] ?? ''),
                    'is_active' => true,
                    'sort_order' => (int) ($woo['menu_order'] ?? 0),
                ]
            );

            $this->map('category', (int) $woo['id'], $category->id);
            $this->stats['categories']++;
        });
    }

    private function products(bool $media): void
    {
        $this->pages('products', function ($woo) use ($media) {
            DB::transaction(function () use ($woo, $media) {
                $categoryId = null;

                foreach (($woo['categories'] ?? []) as $wooCategory) {
                    $categoryId = $this->mappedLocal('category', (int) $wooCategory['id'])
                        ?: Category::where(
                            'slug',
                            $this->slug($wooCategory['slug'] ?? $wooCategory['name'])
                        )->value('id');

                    if ($categoryId) break;
                }

                $status = in_array($woo['status'] ?? '', ['publish', 'private'], true)
                    ? 'active'
                    : 'draft';

                $price = (float) (
                    ($woo['price'] ?? '') !== ''
                        ? ($woo['price'] ?? 0)
                        : ($woo['regular_price'] ?? 0)
                );

                $trackInventory = (bool) ($woo['manage_stock'] ?? false);
                $stockQuantity = max(0, (int) ($woo['stock_quantity'] ?? 0));
                $stockStatus = strtolower((string) ($woo['stock_status'] ?? ''));
                $isInStock = $stockStatus !== ''
                    ? in_array($stockStatus, ['instock', 'onbackorder'], true)
                    : ($trackInventory ? $stockQuantity > 0 : true);

                $product = Product::updateOrCreate(
                    ['slug' => $this->slug($woo['slug'] ?? $woo['name'] . '-' . $woo['id'])],
                    [
                        'category_id' => $categoryId,
                        'name' => $woo['name'] ?? 'Imported product',
                        'subtitle' => null,
                        'description' => $this->clean($woo['description'] ?? ''),
                        'story' => $this->clean($woo['short_description'] ?? ''),
                        'status' => $status,
                        'is_featured' => (bool) ($woo['featured'] ?? false),
                        'base_price' => $price,
                        'compare_at_price' => $this->compare($woo),
                        'stock' => $stockQuantity,
                        'stock_quantity' => $stockQuantity,
                        'track_inventory' => $trackInventory,
                        'is_in_stock' => $isInStock,
                        'size_label' => $this->sizeLabel($woo),
                        'sku' => blank($woo['sku'] ?? null) ? null : $woo['sku'],
                        'meta_title' => $woo['name'] ?? null,
                        'meta_description' => Str::limit(
                            strip_tags($woo['short_description'] ?? ''),
                            500,
                            ''
                        ),
                    ]
                );

                $this->map('product', (int) $woo['id'], $product->id);
                $this->stats['products']++;

                if (($woo['type'] ?? '') === 'variable') {
                    $this->variations($woo, $product);
                }

                if ($media) {
                    $this->images($woo, $product);
                }
            });
        });
    }

    private function variations(array $woo, Product $product): void
    {
        $ids = $woo['variations'] ?? [];

        foreach (array_chunk($ids, 50) as $chunk) {
            foreach ($chunk as $id) {
                try {
                    $variant = $this->get(
                        'products/' . $woo['id'] . '/variations/' . $id
                    )->json();

                    $attributes = collect($variant['attributes'] ?? [])
                        ->pluck('option')
                        ->filter()
                        ->implode(' / ');

                    $sku = blank($variant['sku'] ?? null)
                        ? ('WC-' . $woo['id'] . '-' . $id)
                        : $variant['sku'];

                    $local = ProductVariant::updateOrCreate(
                        ['sku' => $sku],
                        [
                            'product_id' => $product->id,
                            'name' => $attributes ?: 'Variant',
                            'size_label' => $attributes ?: null,
                            'price' => (float) (
                                $variant['price']
                                ?? $variant['regular_price']
                                ?? $product->base_price
                            ),
                            'compare_at_price' => $this->compare($variant),
                            'stock' => max(0, (int) ($variant['stock_quantity'] ?? 0)),
                            'is_active' => ($variant['status'] ?? 'publish') === 'publish',
                            'sort_order' => (int) ($variant['menu_order'] ?? 0),
                        ]
                    );

                    $this->map('variant', (int) $id, $local->id);
                    $this->stats['variants']++;
                } catch (\Throwable $e) {
                    $this->stats['errors']++;
                    $this->map('variant', (int) $id, null, 'error', $e->getMessage());
                }
            }
        }
    }

    private function images(array $woo, Product $product): void
    {
        foreach (($woo['images'] ?? []) as $index => $image) {
            $source = $image['src'] ?? null;
            $sourceId = (int) ($image['id'] ?? 0);

            if (!$source) continue;

            if ($sourceId && $this->mappedLocal('media', $sourceId)) {
                $this->stats['skipped']++;
                continue;
            }

            try {
                $extension = pathinfo(
                    parse_url($source, PHP_URL_PATH) ?: '',
                    PATHINFO_EXTENSION
                ) ?: 'jpg';

                $path = 'products/imported/' . $product->id . '/'
                    . Str::uuid() . '.'
                    . preg_replace('/[^a-z0-9]/i', '', $extension);

                $response = Http::timeout(30)->retry(2, 400)->get($source);
                $response->throw();

                Storage::disk('public')->put($path, $response->body());

                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'path' => $path],
                    [
                        'alt_text' => $image['alt'] ?? $product->name,
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ]
                );

                $this->stats['media']++;

                if ($sourceId) {
                    $this->map('media', $sourceId, $product->id);
                }
            } catch (\Throwable $e) {
                $this->stats['errors']++;

                if ($sourceId) {
                    $this->map('media', $sourceId, null, 'error', $e->getMessage());
                }
            }
        }
    }

    private function customers(): void
    {
        $this->pages('customers', function ($woo) {
            $email = strtolower(trim((string) ($woo['email'] ?? '')));

            if (!$email) {
                $this->stats['skipped']++;
                return;
            }

            $customer = Customer::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $woo['first_name'] ?: 'Customer',
                    'last_name' => $woo['last_name'] ?? null,
                    'phone' => $woo['billing']['phone'] ?? null,
                    'company' => $woo['billing']['company'] ?? null,
                    'address' => $woo['billing'] ?? null,
                    'marketing_opt_in' => false,
                    'is_active' => true,
                ]
            );

            if (!$customer->password) {
                $customer->password = Hash::make(Str::random(48));
                $customer->save();
            }

            $this->map('customer', (int) $woo['id'], $customer->id);
            $this->stats['customers']++;
        });
    }

    private function orders(): void
    {
        $this->pages('orders', function ($woo) {
            DB::transaction(function () use ($woo) {
                $customer = $this->mappedLocal(
                    'customer',
                    (int) ($woo['customer_id'] ?? 0)
                );

                if (!$customer && !empty($woo['billing']['email'])) {
                    $customer = Customer::where(
                        'email',
                        strtolower($woo['billing']['email'])
                    )->value('id');
                }

                $status = [
                    'on-hold' => 'pending',
                    'pending' => 'pending',
                    'processing' => 'processing',
                    'completed' => 'delivered',
                    'cancelled' => 'cancelled',
                    'refunded' => 'refunded',
                    'failed' => 'cancelled',
                ][$woo['status'] ?? 'pending'] ?? 'pending';

                $paymentStatus = ($woo['date_paid'] ?? null)
                    ? 'paid'
                    : (($woo['status'] ?? '') === 'failed' ? 'failed' : 'pending');

                $number = 'WC-' . ($woo['number'] ?? $woo['id']);

                $order = Order::updateOrCreate(
                    ['order_number' => $number],
                    [
                        'customer_id' => $customer,
                        'status' => $status,
                        'payment_status' => $paymentStatus,
                        'currency' => $woo['currency'] ?? 'PKR',
                        'subtotal' => (float) ($woo['total'] ?? 0)
                            - (float) ($woo['shipping_total'] ?? 0)
                            + (float) ($woo['discount_total'] ?? 0),
                        'discount_total' => (float) ($woo['discount_total'] ?? 0),
                        'shipping_total' => (float) ($woo['shipping_total'] ?? 0),
                        'grand_total' => (float) ($woo['total'] ?? 0),
                        'customer_name' => trim(
                            ($woo['billing']['first_name'] ?? '') . ' '
                            . ($woo['billing']['last_name'] ?? '')
                        ),
                        'customer_email' => $woo['billing']['email'] ?? null,
                        'customer_phone' => $woo['billing']['phone'] ?? null,
                        'shipping_address' => $woo['shipping'] ?? null,
                        'billing_address' => $woo['billing'] ?? null,
                        'payment_method' => $woo['payment_method'] ?? null,
                        'shipping_method' => collect($woo['shipping_lines'] ?? [])
                            ->pluck('method_title')
                            ->implode(', '),
                        'notes' => $woo['customer_note'] ?? null,
                        'placed_at' => $woo['date_created'] ?? now(),
                        'fulfilled_at' => $status === 'delivered'
                            ? ($woo['date_completed'] ?? now())
                            : null,
                    ]
                );

                $order->items()->delete();

                foreach (($woo['line_items'] ?? []) as $line) {
                    $productId = $this->mappedLocal(
                        'product',
                        (int) ($line['product_id'] ?? 0)
                    );
                    $variantId = $this->mappedLocal(
                        'variant',
                        (int) ($line['variation_id'] ?? 0)
                    );
                    $qty = max(1, (int) ($line['quantity'] ?? 1));

                    $order->items()->create([
                        'product_id' => $productId,
                        'product_variant_id' => $variantId,
                        'product_name' => $line['name'] ?? 'Imported item',
                        'sku' => $line['sku'] ?? null,
                        'quantity' => $qty,
                        'unit_price' => $qty
                            ? ((float) ($line['total'] ?? 0) / $qty)
                            : 0,
                        'line_total' => (float) ($line['total'] ?? 0),
                    ]);
                }

                $this->map('order', (int) $woo['id'], $order->id);
                $this->stats['orders']++;
            });
        });
    }

    private function sizeLabel(array $woo): ?string
    {
        $name = strtolower((string) ($woo['name'] ?? ''));

        if (str_contains($name, 'tester box') || str_contains($name, '5 x 5ml') || str_contains($name, '5ml each')) {
            return '5 × 5 ML';
        }

        foreach (($woo['attributes'] ?? []) as $attribute) {
            $attributeName = strtolower(trim((string) ($attribute['name'] ?? '')));

            if (!in_array($attributeName, ['size', 'volume', 'capacity'], true)) {
                continue;
            }

            $options = $attribute['options'] ?? [];
            $value = is_array($options) ? ($options[0] ?? null) : $options;

            if ($value) {
                return $this->normalizeSize((string) $value);
            }
        }

        // Project rule: standard Scents by Aamir fragrance bottles are 50 ML.
        return ($woo['type'] ?? 'simple') === 'simple' ? '50 ML' : null;
    }

    private function normalizeSize(string $value): string
    {
        $value = trim($value);

        if (preg_match('/(\d+(?:\.\d+)?)\s*ml/i', $value, $matches)) {
            return rtrim(rtrim($matches[1], '0'), '.') . ' ML';
        }

        return strtoupper($value);
    }

    private function map(
        string $type,
        int $source,
        ?int $local,
        string $status = 'imported',
        ?string $message = null
    ): void {
        if (!$source) return;

        WooCommerceImportMap::updateOrCreate(
            [
                'run_id' => $this->run->id,
                'entity_type' => $type,
                'source_id' => $source,
            ],
            [
                'local_id' => $local,
                'status' => $status,
                'message' => Str::limit((string) $message, 2000, '') ?: null,
            ]
        );
    }

    private function mappedLocal(string $type, int $source): ?int
    {
        if (!$source) return null;

        return WooCommerceImportMap::where('entity_type', $type)
            ->where('source_id', $source)
            ->whereHas(
                'run',
                fn ($query) => $query->where('source_url', $this->run->source_url)
            )
            ->whereNotNull('local_id')
            ->latest('id')
            ->value('local_id');
    }

    private function slug(string $value): string
    {
        return Str::slug($value) ?: 'imported-' . Str::random(8);
    }

    private function clean(string $value): string
    {
        $text = html_entity_decode(
            strip_tags($value),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = preg_replace('/\s+/u', ' ', $text);

        return trim((string) $text);
    }

    private function compare(array $woo): ?float
    {
        $regular = (float) ($woo['regular_price'] ?? 0);
        $sale = (float) ($woo['sale_price'] ?? 0);

        return $sale > 0 && $regular > $sale ? $regular : null;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class RepairImportedCatalog extends Command
{
    protected $signature = 'storefront:repair-imported-catalog';
    protected $description = 'Repair imported product media paths and convert WooCommerce HTML descriptions to clean text';

    public function handle(): int
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('product_images')) {
            $this->error('Catalog tables are missing. Run php artisan migrate first.');
            return self::FAILURE;
        }

        $images = 0;
        ProductImage::query()->chunkById(200, function ($rows) use (&$images): void {
            foreach ($rows as $image) {
                $path = trim(str_replace('\\', '/', (string) $image->path));
                $fixed = preg_replace('#^/?storage/#', '', $path);
                if ($fixed !== $path) {
                    $image->update(['path' => $fixed]);
                    $images++;
                }
            }
        });

        $products = 0;
        Product::query()->chunkById(100, function ($rows) use (&$products): void {
            foreach ($rows as $product) {
                $changes = [];
                foreach (['description', 'story', 'notes', 'wear'] as $field) {
                    $original = $product->{$field};
                    if ($original === null || trim((string) $original) === '') {
                        continue;
                    }
                    $plain = $this->plain((string) $original);
                    if ($plain !== $original) {
                        $changes[$field] = $plain;
                    }
                }
                if ($changes) {
                    $product->update($changes);
                    $products++;
                }
            }
        });

        $categories = 0;
        if (Schema::hasTable('categories')) {
            Category::query()->chunkById(100, function ($rows) use (&$categories): void {
                foreach ($rows as $category) {
                    if (!$category->description) continue;
                    $plain = $this->plain((string) $category->description);
                    if ($plain !== $category->description) {
                        $category->update(['description' => $plain]);
                        $categories++;
                    }
                }
            });
        }

        $this->info("Fixed image paths: {$images}");
        $this->info("Sanitized products: {$products}");
        $this->info("Sanitized categories: {$categories}");
        $this->info('Catalog repair complete.');

        return self::SUCCESS;
    }

    private function plain(string $value): string
    {
        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim((string) $text);
    }
}

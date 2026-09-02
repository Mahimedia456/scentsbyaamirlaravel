<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SeedMasterProductContent extends Command
{
    protected $signature = 'storefront:seed-master-product-content
        {--dry-run : Show matched products and writable fields without changing the database}
        {--strict : Fail if any of the 26 configured products cannot be matched}
        {--backup : Write a JSON backup of current content fields before updating}';

    protected $description = 'Overwrite curated SEO/content fields for the 26 Scents by Aamir storefront products without changing commerce data.';

    private array $candidateColumns = [
        'subtitle',
        'short_description',
        'description',
        'story',
        'top_notes',
        'heart_notes',
        'base_notes',
        'wear',
        'notes',
        'meta_title',
        'meta_description',
    ];

    public function handle(): int
    {
        $master = config('master_product_content');

        if (!is_array($master) || count($master) !== 26) {
            $this->error('Expected exactly 26 entries in config/master_product_content.php.');
            return self::FAILURE;
        }

        if (!Schema::hasTable('products')) {
            $this->error('Table "products" does not exist.');
            return self::FAILURE;
        }

        $available = array_values(array_filter(
            $this->candidateColumns,
            fn (string $column) => Schema::hasColumn('products', $column)
        ));

        if (!$available) {
            $this->error('None of the expected content columns exist on products.');
            return self::FAILURE;
        }

        $matches = [];
        $missing = [];

        foreach ($master as $expectedSlug => $item) {
            $product = DB::table('products')->where('slug', $expectedSlug)->first();

            if (!$product) {
                foreach (($item['aliases'] ?? []) as $alias) {
                    $product = DB::table('products')
                        ->whereRaw('LOWER(name) LIKE ?', [mb_strtolower($alias) . '%'])
                        ->first();

                    if ($product) {
                        break;
                    }
                }
            }

            if (!$product) {
                $missing[] = $expectedSlug;
                continue;
            }

            $matches[] = [$product, $item];
        }

        $this->table(
            ['Matched product', 'DB slug', 'Type', 'Writable fields'],
            array_map(
                fn ($row) => [
                    $row[0]->name ?? ('#'.$row[0]->id),
                    $row[0]->slug ?? '—',
                    $row[1]['type'] ?? 'fragrance',
                    implode(', ', $available),
                ],
                $matches
            )
        );

        if ($missing) {
            $this->warn('Missing configured products: '.implode(', ', $missing));
            if ($this->option('strict')) {
                $this->error('Strict mode enabled: no changes were applied.');
                return self::FAILURE;
            }
        }

        if ($this->option('dry-run')) {
            $this->info(sprintf('Dry-run complete: %d/26 products matched. No database changes made.', count($matches)));
            return self::SUCCESS;
        }

        if ($this->option('backup')) {
            $this->backup($matches, $available);
        }

        try {
            DB::transaction(function () use ($matches, $available) {
                foreach ($matches as [$product, $item]) {
                    $payload = [];

                    foreach ($available as $column) {
                        $value = $item[$column] ?? null;

                        if (in_array($column, ['top_notes', 'heart_notes', 'base_notes'], true)) {
                            // Existing SBA schemas may use JSON, TEXT or VARCHAR. JSON is portable across all.
                            $value = json_encode(array_values($value ?: []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        }

                        $payload[$column] = $value;
                    }

                    // Commerce-critical fields are intentionally never included:
                    // price, sale_price, stock, sku, slug, name, images, category_id,
                    // inventory, status, variants, dimensions and timestamps (except DB-managed updated_at).
                    DB::table('products')->where('id', $product->id)->update($payload);
                }
            });
        } catch (Throwable $e) {
            $this->error('Update failed and transaction was rolled back: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Master content applied to %d product(s). Commerce data was not modified.',
            count($matches)
        ));

        return self::SUCCESS;
    }

    private function backup(array $matches, array $available): void
    {
        $rows = [];

        foreach ($matches as [$product]) {
            $row = [
                'id' => $product->id,
                'name' => $product->name ?? null,
                'slug' => $product->slug ?? null,
            ];

            foreach ($available as $column) {
                $row[$column] = $product->{$column} ?? null;
            }

            $rows[] = $row;
        }

        $dir = storage_path('app/master-content-backups');

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir.'/products-before-master-content-'.now()->format('Ymd-His').'.json';
        file_put_contents(
            $path,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info('Backup written: '.$path);
    }
}

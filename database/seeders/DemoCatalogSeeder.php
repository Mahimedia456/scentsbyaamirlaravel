<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $categoryId = DB::table('categories')->updateOrInsert(
            ['slug' => 'eau-de-parfum'],
            ['name' => 'Eau de Parfum', 'description' => 'Signature Scents by Aamir fragrances.', 'is_active' => true, 'sort_order' => 1, 'updated_at' => $now, 'created_at' => $now]
        );

        $category = DB::table('categories')->where('slug', 'eau-de-parfum')->first();

        foreach ([
            ['name' => 'Nocturne Ember', 'price' => 28500, 'stock' => 18],
            ['name' => 'Saffron Veil', 'price' => 26500, 'stock' => 24],
            ['name' => 'Velvet Oud', 'price' => 32000, 'stock' => 12],
        ] as $index => $item) {
            $slug = Str::slug($item['name']);
            DB::table('products')->updateOrInsert(
                ['slug' => $slug],
                [
                    'category_id' => $category?->id,
                    'name' => $item['name'],
                    'sku' => 'SBA-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'subtitle' => 'Signature fragrance',
                    'short_description' => 'A refined Scents by Aamir composition.',
                    'price' => $item['price'],
                    'currency' => 'PKR',
                    'stock_quantity' => $item['stock'],
                    'track_inventory' => true,
                    'is_active' => true,
                    'is_featured' => $index < 2,
                    'published_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}

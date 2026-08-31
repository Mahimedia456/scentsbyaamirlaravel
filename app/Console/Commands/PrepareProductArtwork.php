<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PrepareProductArtwork extends Command
{
    protected $signature = 'storefront:prepare-product-artwork
                            {--force : Replace existing PROMPTS.md and manifest.json files}
                            {--status=active : Product status to include}';

    protected $description = 'Create one Phase 17 artwork folder and prompt pack for every storefront product.';

    public function handle(): int
    {
        $products = Product::query()
            ->where('status', (string) $this->option('status'))
            ->with(['category', 'variants', 'images'])
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            $this->warn('No matching products found.');
            return self::SUCCESS;
        }

        $root = public_path('images/products');

        if (!is_dir($root)) {
            mkdir($root, 0775, true);
        }

        foreach ($products as $product) {
            $slug = $product->slug ?: Str::slug($product->name);
            $dir = $root.DIRECTORY_SEPARATOR.$slug;

            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $variantSummary = $product->variants
                ->map(fn ($v) => trim(($v->size_label ?: $v->name).' | stock '.(int) $v->stock.' | PKR '.number_format((float) $v->price)))
                ->filter()
                ->implode("\n- ");

            $importedNotes = trim(strip_tags((string) $product->notes));
            $description = trim(strip_tags((string) $product->description));
            $story = trim(strip_tags((string) $product->story));

            $referenceInstruction = "Upload the exact Scents by Aamir bottle/product reference for {$product->name}. Preserve the exact bottle silhouette, cap, glass, label, logo, printed product name, proportions and liquid colour. Do not copy the inspired-by brand's bottle, logo or packaging.";

            $prompt = <<<MD
# {$product->name}

**Slug:** `{$slug}`  
**Category:** {$product->category?->name}  
**SKU:** {$product->sku}  

## Imported Woo/Laravel product data

**Description:**  
{$description}

**Story / short description:**  
{$story}

**Imported notes:**  
{$importedNotes}

**Variants:**  
- {$variantSummary}

---

## 01 — `hero.webp`

**Save path:**  
`public/images/products/{$slug}/hero.webp`

**Bottle reference:** YES

{$referenceInstruction}

Create an ORIGINAL premium Scents by Aamir product-detail hero for "{$product->name}".

Use the uploaded exact bottle as the hero subject. Place the bottle in the right half / centre-right with generous breathing space. Build the surrounding art direction only from the fragrance materials, mood and notes supported by the imported product data above.

The image must feel like luxury fragrance campaign photography: controlled studio light, tactile stone or material surface, cinematic depth, restrained atmosphere, polished reflections, premium editorial composition.

Do not add invented scent notes. Do not add competitor branding or competitor bottle design. No people. No extra readable text. No watermark.

Landscape 1800x1500.

---

## 02 — `notes.webp`

**Save path:**  
`public/images/products/{$slug}/notes.webp`

**Bottle reference:** NO

Create a premium fragrance-note still life for "{$product->name}" using ONLY note elements supported by the Imported notes / description above.

Show the aromatic ingredients as a sculptural editorial composition on refined stone, paper, glass or dark mineral material. Make the note hierarchy visually legible through scale and placement without adding text.

NO perfume bottle.
NO fake ingredients.
NO competitor branding.
NO readable text.
NO people.
NO watermark.

1800x1500.

---

## 03 — `world.webp`

**Save path:**  
`public/images/products/{$slug}/world.webp`

**Bottle reference:** NO

Create an atmospheric visual world for "{$product->name}" derived from its imported story, description and notes above.

This image should communicate the fragrance's temperature, texture, time of day, materials and emotional character without showing a perfume bottle. Use abstract environmental detail, raw ingredients, fabric, stone, smoke, light, water or botanical material only when supported by the product data.

NO bottle.
NO competitor references.
NO readable text.
NO people.
NO watermark.

1800x1500.

---

## 04 — `story.webp`

**Save path:**  
`public/images/products/{$slug}/story.webp`

**Bottle reference:** NO

Create a quiet luxury editorial story image for "{$product->name}".

Interpret the fragrance's imported story and description through material, atmosphere and light rather than literal product photography. Keep it original to Scents by Aamir and visually compatible with a premium cream/black product-detail page.

NO perfume bottle unless a later art-direction decision explicitly requests one.
NO competitor branding.
NO readable text.
NO people.
NO watermark.

1800x1500.

MD;

            $manifest = [
                'product_id' => $product->id,
                'slug' => $slug,
                'name' => $product->name,
                'category' => $product->category?->name,
                'sku' => $product->sku,
                'description' => $description,
                'story' => $story,
                'notes' => $importedNotes,
                'variants' => $product->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'size' => $v->size_label ?: $v->name,
                    'sku' => $v->sku,
                    'price' => (float) $v->price,
                    'stock' => (int) $v->stock,
                    'in_stock' => (int) $v->stock > 0,
                ])->values()->all(),
                'images' => $product->images->pluck('path')->values()->all(),
                'artwork' => [
                    'hero' => "images/products/{$slug}/hero.webp",
                    'notes' => "images/products/{$slug}/notes.webp",
                    'world' => "images/products/{$slug}/world.webp",
                    'story' => "images/products/{$slug}/story.webp",
                ],
            ];

            $promptPath = $dir.DIRECTORY_SEPARATOR.'PROMPTS.md';
            $manifestPath = $dir.DIRECTORY_SEPARATOR.'manifest.json';

            if ($this->option('force') || !file_exists($promptPath)) {
                file_put_contents($promptPath, $prompt);
            }

            if ($this->option('force') || !file_exists($manifestPath)) {
                file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            foreach (['hero.webp', 'notes.webp', 'world.webp', 'story.webp'] as $asset) {
                $assetPath = $dir.DIRECTORY_SEPARATOR.$asset;
                if (!file_exists($assetPath)) {
                    file_put_contents($dir.DIRECTORY_SEPARATOR.'.gitkeep', '');
                }
            }

            $this->line("Prepared: {$slug}");
        }

        $this->newLine();
        $this->info("Prepared {$products->count()} product artwork folders under public/images/products.");

        return self::SUCCESS;
    }
}

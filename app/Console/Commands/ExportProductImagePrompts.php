<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ExportProductImagePrompts extends Command
{
    protected $signature = 'storefront:export-product-note-prompts
                            {--dir=PRODUCT_NOTE_PROMPTS_26}
                            {--active-only=1}';

    protected $description = 'Export exact per-product prompts for top-notes.webp, heart-notes.webp and base-notes.webp.';

    public function handle(): int
    {
        if (!Schema::hasTable('products')) {
            $this->error('products table does not exist.');
            return self::FAILURE;
        }

        $query = Product::query()
            ->with(['category', 'variants'])
            ->orderBy('id');

        if ((bool) $this->option('active-only')) {
            $query->where('status', 'active');
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->error('No products found.');
            return self::FAILURE;
        }

        $directory = base_path((string) $this->option('dir'));
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $written = 0;

        foreach ($products as $product) {
            $out = [];
            $out[] = '# Scents by Aamir — Product Note Images';
        $out[] = '';
        $out[] = '> Exact data is read from the current Laravel database.';
        $out[] = '> Existing hero.webp, world.webp and story.webp are NOT regenerated.';
        $out[] = '> Product shopping gallery continues using hero.webp + official/imported product gallery images.';
        $out[] = '';
        $out[] = '**Generate only:** `top-notes.webp`, `heart-notes.webp`, `base-notes.webp`';
        $out[] = '';
        $out[] = '---';
        $out[] = '';
            $name = trim((string) $product->name);
            $slug = trim((string) $product->slug);
            $category = $product->category?->name ?: 'Uncategorised';
            $sku = $product->sku ?: '—';

            $description = trim(strip_tags((string) ($product->description ?? '')));
            $story = trim(strip_tags((string) ($product->short_description ?? $product->story ?? '')));
            $notes = trim(strip_tags((string) ($product->notes ?? '')));
            $topNotes = trim(strip_tags((string) ($product->top_notes ?? '')));
            $heartNotes = trim(strip_tags((string) ($product->heart_notes ?? '')));
            $baseNotes = trim(strip_tags((string) ($product->base_notes ?? '')));

            $descriptionText = $description !== ''
                ? $description
                : 'No imported description is currently stored for this product.';

            $storyText = $story !== ''
                ? $story
                : 'No separate imported short description/story is currently stored for this product.';

            $notesText = $notes !== ''
                ? $notes
                : 'No separate imported notes field is currently stored. Extract note groups ONLY from explicit Top Notes / Heart Notes / Base Notes information present in the description above.';

            $variantLabels = $product->variants
                ->map(fn ($variant) => trim((string) ($variant->size_label ?: $variant->name)))
                ->filter()
                ->unique()
                ->values();

            $variantText = $variantLabels->isNotEmpty()
                ? $variantLabels->map(fn ($label) => '- ' . $label)->implode("\n")
                : '- Simple product — normal fragrances display as 50 ML; tester products display as 5 ML.';

            $out[] = '# ' . $name;
            $out[] = '';
            $out[] = '**Slug:** `' . $slug . '`';
            $out[] = '';
            $out[] = '**Category:** ' . $category;
            $out[] = '';
            $out[] = '**SKU:** ' . $sku;
            $out[] = '';
            $out[] = '## Imported Woo/Laravel product data';
            $out[] = '';
            $out[] = '**Description:**';
            $out[] = '';
            $out[] = $descriptionText;
            $out[] = '';
            $out[] = '**Story / short description:**';
            $out[] = '';
            $out[] = $storyText;
            $out[] = '';
            $out[] = '**Imported notes:**';
            $out[] = '';
            $out[] = $notesText;
            $out[] = '';
            $out[] = '**Variants:**';
            $out[] = '';
            $out[] = $variantText;
            $out[] = '';
            $out[] = '---';
            $out[] = '';

            $stages = [
                [
                    '01',
                    'top-notes.webp',
                    'TOP NOTES',
                    'Use ONLY ingredients explicitly identified as Top Notes / opening notes in the imported product data. Do not include heart or base materials unless the source itself explicitly classifies them as top notes.',
                    'Brightest / most immediate opening character. Premium fragrance ingredient still life with crisp controlled light and strong material separation.',
                ],
                [
                    '02',
                    'heart-notes.webp',
                    'HEART NOTES',
                    'Use ONLY ingredients explicitly identified as Heart Notes / middle notes in the imported product data. Do not include top or base materials unless the source itself explicitly classifies them as heart notes.',
                    'The central signature of the perfume. More enveloping, dimensional and tactile than the top-note image, while remaining elegant and editorial.',
                ],
                [
                    '03',
                    'base-notes.webp',
                    'BASE NOTES',
                    'Use ONLY ingredients explicitly identified as Base Notes / dry-down notes in the imported product data. Do not include top or heart materials unless the source itself explicitly classifies them as base notes.',
                    'Deepest / longest-lasting dry-down character. Richer shadows, denser materials and sophisticated luxury texture appropriate to the exact supported base notes.',
                ],
            ];

            foreach ($stages as [$number, $filename, $stage, $rule, $direction]) {
                $out[] = '## ' . $number . ' — `' . $filename . '`';
                $out[] = '';
                $out[] = '**Save path:**';
                $out[] = '';
                $out[] = '`public/images/products/' . $slug . '/' . $filename . '`';
                $out[] = '';
                $out[] = '**Bottle reference:** NO';
                $out[] = '';
                $out[] = 'Create an ORIGINAL premium ' . strtolower($stage) . ' fragrance ingredient image for "' . $name . '" by Scents by Aamir.';
                $out[] = '';
                $out[] = $rule;
                $out[] = '';
                $out[] = $direction;
                $out[] = '';
                $out[] = 'Compose the ingredients as one self-contained still life. The image will be displayed as its own card on a premium cream/black Scents by Aamir product page, so keep all important ingredients comfortably inside the frame.';
                $out[] = '';
                $out[] = 'Art direction: luxury fragrance campaign photography, tactile natural ingredients, refined stone/mineral/paper/glass/fabric surfaces where suitable, controlled studio light, cinematic depth, realistic texture, polished editorial composition.';
                $out[] = '';
                $out[] = 'NO perfume bottle.';
                $out[] = 'NO invented ingredients.';
                $out[] = 'NO competitor bottle, branding or packaging.';
                $out[] = 'NO readable text.';
                $out[] = 'NO people.';
                $out[] = 'NO watermark.';
                $out[] = '';
                $out[] = 'Landscape 1600x1100.';
                $out[] = '';
                $out[] = '---';
                $out[] = '';
            }
            $filename = str_pad((string) ($written + 1), 2, '0', STR_PAD_LEFT)
                . '-' . $slug . '-NOTE-IMAGES-PROMPT.md';
            file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, implode("\n", $out) . "\n");
            $written++;
        }

        $this->info("Exported {$written} separate product prompt files.");
        $this->line($directory);

        return self::SUCCESS;
    }
}

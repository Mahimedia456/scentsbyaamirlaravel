<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ApplyMasterProductContent extends Command
{
    protected $signature = 'storefront:apply-master-product-content
                            {--dry-run}
                            {--active-only=1}';

    protected $description =
        'Clean structured content for all products and remove description pollution from fragrance notes.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = Product::query()->orderBy('name');

        if ((bool) $this->option('active-only')) {
            $query->where('status', 'active');
        }

        $products = $query->get();

        $changed = 0;
        $rows = [];

        foreach ($products as $product) {
            $description = $this->cleanText($product->description);
            $story = $this->cleanText(
                $product->story
                ?: $product->short_description
                ?: $product->meta_description
            );

            $top = $this->cleanNote(
                $product->top_notes,
                $product
            );

            $heart = $this->cleanNote(
                $product->heart_notes,
                $product
            );

            $base = $this->cleanNote(
                $product->base_notes,
                $product
            );

            /*
             * Legacy notes are fallback only.
             * Never concatenate notes + description.
             */
            $legacy = $this->cleanText($product->notes);

            if (!$top) {
                $top = $this->extractLegacy(
                    $legacy,
                    'Top Notes',
                    [
                        'Heart Notes',
                        'Middle Notes',
                        'Base Notes',
                    ]
                );
            }

            if (!$heart) {
                $heart = $this->extractLegacy(
                    $legacy,
                    'Heart Notes',
                    ['Base Notes']
                );

                if (!$heart) {
                    $heart = $this->extractLegacy(
                        $legacy,
                        'Middle Notes',
                        ['Base Notes']
                    );
                }
            }

            if (!$base) {
                $base = $this->extractLegacy(
                    $legacy,
                    'Base Notes',
                    []
                );

                $base = $this->cleanNote(
                    $base,
                    $product
                );
            }

            $wear = $this->cleanWear($product->wear);

            /*
             * If story missing, use a concise portion of description.
             */
            if (!$story && $description) {
                $story = $this->firstSentences(
                    $description,
                    3,
                    520
                );
            }

            $notesSummary = collect([
                $top
                    ? 'Top Notes: ' . $top
                    : null,

                $heart
                    ? 'Heart Notes: ' . $heart
                    : null,

                $base
                    ? 'Base Notes: ' . $base
                    : null,
            ])
                ->filter()
                ->implode("\n");

            $content = [
                'description' => $description,
                'story' => $story,
                'top_notes' => $top,
                'heart_notes' => $heart,
                'base_notes' => $base,
                'wear' => $wear,
                'notes' => $notesSummary ?: null,
            ];

            $updates = [];

            foreach ($content as $field => $newValue) {
                $new = trim((string) ($newValue ?? ''));
                $old = trim((string) ($product->{$field} ?? ''));

                if ($new !== $old) {
                    $updates[$field] =
                        $new !== ''
                            ? $new
                            : null;
                }
            }

            $rows[] = [
                Str::limit($product->name, 40),
                $top ?: '—',
                $heart ?: '—',
                $base ?: '—',
                $updates === []
                    ? 'OK'
                    : implode(', ', array_keys($updates)),
            ];

            if ($updates === []) {
                continue;
            }

            $changed++;

            if (!$dryRun) {
                $product
                    ->forceFill($updates)
                    ->save();
            }
        }

        $this->table(
            [
                'Product',
                'Top',
                'Heart',
                'Base',
                'Action',
            ],
            $rows
        );

        $this->newLine();

        $this->info(
            $dryRun
                ? "Dry-run: {$changed} product(s) would be fixed."
                : "Complete: {$changed} product(s) fixed."
        );

        return self::SUCCESS;
    }

    private function cleanNote(
        ?string $value,
        Product $product
    ): ?string {
        $value = $this->cleanText($value);

        if (!$value) {
            return null;
        }

        /*
         * Stop as soon as a non-note section begins.
         */
        $value = preg_split(
            '/\b(?:'
            . 'Product Description'
            . '|Description'
            . '|Short Description'
            . '|Story'
            . '|Longevity'
            . '|Occasion'
            . '|Why Choose(?: This)?'
            . '|Materials?\s*(?:&|and)?\s*Care'
            . '|Care'
            . '|Packaging'
            . '|Features'
            . '|Discover'
            . '|Content'
            . ')\b\s*:?/i',
            $value,
            2
        )[0] ?? $value;

        /*
         * Remove exact description/story accidentally appended by
         * previous optimizer runs.
         */
        foreach ([
            $product->description,
            $product->story,
            $product->short_description,
            $product->meta_description,
        ] as $body) {
            $body = $this->cleanText($body);

            if (
                $body
                && str_contains($value, $body)
            ) {
                $value = trim(
                    str_replace(
                        $body,
                        '',
                        $value
                    )
                );
            }
        }

        /*
         * Example:
         * Musk, Patchouli, Ambergris Bold Heat combines...
         *
         * Cut when the product name starts again.
         */
        $productLead = trim(
            (string) Str::before(
                (string) $product->name,
                ' - '
            )
        );

        if ($productLead !== '') {
            $position = mb_stripos(
                $value,
                $productLead
            );

            if (
                $position !== false
                && $position > 0
            ) {
                $value = mb_substr(
                    $value,
                    0,
                    $position
                );
            }
        }

        /*
         * Last defensive check:
         * ingredient lists should not become marketing paragraphs.
         */
        if (preg_match(
            '/^(.{2,180}?)'
            . '(?=\s+[A-Z][a-z]+\s+'
            . '(?:is|combines|opens|offers|delivers|creates|'
            . 'brings|blends|captures|features|provides)\b)/u',
            $value,
            $match
        )) {
            $value = $match[1];
        }

        $value = $this->cleanText($value);

        $value = rtrim(
            (string) $value,
            " .;,-"
        );

        return $value !== ''
            ? $value
            : null;
    }

    private function cleanWear(
        ?string $wear
    ): ?string {
        $wear = $this->cleanText($wear);

        if (!$wear) {
            return null;
        }

        $wear = preg_replace(
            '/^Best worn:\s*\.\s*/i',
            '',
            $wear
        ) ?? $wear;

        $wear = preg_replace(
            '/^Best worn:\s*/i',
            '',
            $wear
        ) ?? $wear;

        $wear = trim($wear);

        if ($wear === '') {
            return null;
        }

        return ucfirst(
            rtrim($wear, '.')
        ) . '.';
    }

    private function extractLegacy(
        ?string $source,
        string $label,
        array $stops
    ): ?string {
        $source = $this->cleanText($source);

        if (!$source) {
            return null;
        }

        $lookahead = '$';

        if ($stops !== []) {
            $alternation = implode(
                '|',
                array_map(
                    fn ($stop) =>
                        preg_quote($stop, '/'),
                    $stops
                )
            );

            $lookahead =
                '(?=\s*(?:'
                . $alternation
                . ')\s*:?\s*|$)';
        }

        if (!preg_match(
            '/'
            . preg_quote($label, '/')
            . '\s*:?\s*(.+?)'
            . $lookahead
            . '/is',
            $source,
            $match
        )) {
            return null;
        }

        return $this->cleanText(
            $match[1]
        );
    }

    private function firstSentences(
        string $value,
        int $count,
        int $maxLength
    ): ?string {
        $sentences = preg_split(
            '/(?<=[.!?])\s+/',
            $value
        ) ?: [$value];

        $result = trim(
            implode(
                ' ',
                array_slice(
                    $sentences,
                    0,
                    $count
                )
            )
        );

        if (
            mb_strlen($result)
            > $maxLength
        ) {
            $result = rtrim(
                mb_substr(
                    $result,
                    0,
                    $maxLength - 1
                )
            ) . '…';
        }

        return $result !== ''
            ? $result
            : null;
    }

    private function cleanText(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = html_entity_decode(
            strip_tags(
                (string) $value
            ),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $value = str_replace(
            [
                "\r\n",
                "\r",
                "\n",
                "\t",
                '•',
            ],
            ' ',
            $value
        );

        $value = preg_replace(
            '/\s+/',
            ' ',
            $value
        ) ?? $value;

        $value = trim($value);

        return $value !== ''
            ? $value
            : null;
    }
}
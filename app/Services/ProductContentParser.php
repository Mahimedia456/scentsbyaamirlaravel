<?php

namespace App\Services;

class ProductContentParser
{
    public function parse(
        ?string $rawDescription,
        ?string $legacyNotes = null,
        ?string $existingStory = null,
        ?string $existingWear = null
    ): array {
        $descriptionSource = $this->normalize($rawDescription);
        $notesSource = $this->normalize($legacyNotes);
        $storySource = $this->normalize($existingStory);
        $wearSource = $this->normalize($existingWear);

        $combined = trim(implode(' ', array_filter([
            $notesSource,
            $descriptionSource,
        ])));

        $metadataStops = [
            'Longevity',
            'Occasion',
            'Why Choose',
            'Why Choose This',
            'Materials & Care',
            'Materials and Care',
            'Materials',
            'Care',
            'Packaging',
            'Features',
            'Discover',
            'Content',
            'Product Description',
            'Short Description',
            'Story',
        ];

        $topNotes = $this->extract(
            $combined,
            'Top Notes',
            array_merge(['Heart Notes', 'Middle Notes', 'Base Notes'], $metadataStops)
        );

        $heartNotes = $this->extractFirstAvailable(
            $combined,
            ['Heart Notes', 'Middle Notes'],
            array_merge(['Base Notes'], $metadataStops)
        );

        $baseNotes = $this->extract(
            $combined,
            'Base Notes',
            $metadataStops
        );

        $productDescription = $this->extract(
            $descriptionSource,
            'Product Description',
            [
                'Materials & Care',
                'Materials and Care',
                'Care',
                'Packaging',
            ]
        );

        if (!$productDescription) {
            $productDescription = $this->stripStructuredSections($descriptionSource);
        }

        $story = $storySource !== ''
            ? $storySource
            : $this->firstSentences(
                $productDescription ?: $descriptionSource,
                3,
                520
            );

        $wear = $wearSource !== ''
            ? $wearSource
            : $this->buildWearFromSource($descriptionSource);

        return [
            'top_notes' => $this->clean($topNotes),
            'heart_notes' => $this->clean($heartNotes),
            'base_notes' => $this->clean($baseNotes),
            'description' => $this->clean($productDescription),
            'story' => $this->clean($story),
            'wear' => $this->clean($wear),
            'notes_summary' => $this->notesSummary(
                $topNotes,
                $heartNotes,
                $baseNotes
            ),
        ];
    }

    private function extract(
        string $source,
        string $label,
        array $stops
    ): ?string {
        if ($source === '') {
            return null;
        }

        $lookahead = '$';

        if ($stops !== []) {
            $alternation = implode('|', array_map(
                static fn (string $stop) => preg_quote($stop, '/'),
                array_values(array_unique($stops))
            ));

            $lookahead = '(?=\s*(?:' . $alternation . ')\s*:?\s*|$)';
        }

        if (!preg_match(
            '/' . preg_quote($label, '/') . '\s*:?\s*(.+?)' . $lookahead . '/is',
            $source,
            $match
        )) {
            return null;
        }

        return $this->clean($match[1]);
    }

    private function extractFirstAvailable(
        string $source,
        array $labels,
        array $stops
    ): ?string {
        foreach ($labels as $label) {
            $value = $this->extract($source, $label, $stops);

            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    private function stripStructuredSections(string $source): ?string
    {
        if ($source === '') {
            return null;
        }

        $clean = $source;

        foreach ([
            [
                'Top Notes',
                [
                    'Heart Notes',
                    'Middle Notes',
                    'Base Notes',
                    'Product Description',
                    'Longevity',
                    'Occasion',
                    'Why Choose',
                    'Materials & Care',
                    'Materials and Care',
                    'Care',
                    'Packaging',
                ],
            ],
            [
                'Heart Notes',
                [
                    'Base Notes',
                    'Product Description',
                    'Longevity',
                    'Occasion',
                    'Why Choose',
                    'Materials & Care',
                    'Materials and Care',
                    'Care',
                    'Packaging',
                ],
            ],
            [
                'Middle Notes',
                [
                    'Base Notes',
                    'Product Description',
                    'Longevity',
                    'Occasion',
                    'Why Choose',
                    'Materials & Care',
                    'Materials and Care',
                    'Care',
                    'Packaging',
                ],
            ],
            [
                'Base Notes',
                [
                    'Product Description',
                    'Longevity',
                    'Occasion',
                    'Why Choose',
                    'Materials & Care',
                    'Materials and Care',
                    'Care',
                    'Packaging',
                ],
            ],
        ] as [$label, $stops]) {
            $alternation = implode('|', array_map(
                static fn (string $stop) => preg_quote($stop, '/'),
                $stops
            ));

            $clean = preg_replace(
                '/' . preg_quote($label, '/')
                . '\s*:?\s*.+?(?=\s*(?:'
                . $alternation
                . ')\s*:?\s*|$)/is',
                ' ',
                $clean
            ) ?? $clean;
        }

        $clean = preg_replace(
            '/\b(?:Product Description|Features|Longevity|Occasion|Why Choose(?: This)?|Materials?\s*(?:&|and)?\s*Care|Care|Packaging|Discover|Content)\b\s*:?\s*/i',
            ' ',
            $clean
        ) ?? $clean;

        return $this->clean($clean);
    }

    private function buildWearFromSource(string $source): ?string
    {
        if ($source === '') {
            return null;
        }

        $occasion = $this->extract(
            $source,
            'Occasion',
            [
                'Why Choose',
                'Materials & Care',
                'Materials and Care',
                'Care',
                'Packaging',
                'Product Description',
            ]
        );

        $longevity = $this->extract(
            $source,
            'Longevity',
            [
                'Occasion',
                'Why Choose',
                'Materials & Care',
                'Materials and Care',
                'Care',
                'Packaging',
                'Product Description',
            ]
        );

        $parts = [];

        if ($occasion) {
            $parts[] = 'Best worn: ' . rtrim($occasion, '.') . '.';
        }

        if ($longevity) {
            $parts[] = 'Longevity: ' . rtrim($longevity, '.') . '.';
        }

        return $parts !== [] ? implode(' ', $parts) : null;
    }

    private function firstSentences(
        string $value,
        int $count,
        int $maxLength
    ): ?string {
        $value = $this->clean($value);

        if (!$value) {
            return null;
        }

        $sentences = preg_split('/(?<=[.!?])\s+/', $value) ?: [$value];

        $story = trim(
            implode(' ', array_slice($sentences, 0, $count))
        );

        if (mb_strlen($story) > $maxLength) {
            $story = rtrim(
                mb_substr($story, 0, $maxLength - 1)
            ) . '…';
        }

        return $story;
    }

    private function notesSummary(
        ?string $top,
        ?string $heart,
        ?string $base
    ): ?string {
        $rows = [];

        if (filled($top)) {
            $rows[] = 'Top Notes: ' . $this->clean($top);
        }

        if (filled($heart)) {
            $rows[] = 'Heart Notes: ' . $this->clean($heart);
        }

        if (filled($base)) {
            $rows[] = 'Base Notes: ' . $this->clean($base);
        }

        return $rows !== [] ? implode("\n", $rows) : null;
    }

    private function normalize(?string $value): string
    {
        $value = html_entity_decode(
            strip_tags((string) $value),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $value = str_replace(
            ["\r\n", "\r", "\n", "\t", '•'],
            ' ',
            $value
        );

        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = $this->normalize($value);
        $value = trim($value, " \t\n\r\0\x0B:;,-");

        return $value !== '' ? $value : null;
    }
}
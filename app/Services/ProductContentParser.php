<?php

namespace App\Services;

class ProductContentParser
{
    public function parse(?string $rawDescription, ?string $legacyNotes = null): array
    {
        $descriptionSource = $this->normalize($rawDescription);
        $notesSource = $this->normalize($legacyNotes);
        $combined = trim(implode(' ', array_filter([$notesSource, $descriptionSource])));

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

        $top = $this->extract($combined, 'Top Notes', array_merge(['Heart Notes', 'Base Notes'], $metadataStops));
        $heart = $this->extract($combined, 'Heart Notes', array_merge(['Base Notes'], $metadataStops));
        $base = $this->extract($combined, 'Base Notes', $metadataStops);

        $description = $this->extract($descriptionSource, 'Product Description', [
            'Materials & Care',
            'Materials and Care',
            'Care',
            'Packaging',
        ]);

        if (!$description) {
            $description = $this->stripNoteAndMetadataSections($descriptionSource);
        }

        return [
            'top_notes' => $this->clean($top),
            'heart_notes' => $this->clean($heart),
            'base_notes' => $this->clean($base),
            'description' => $this->clean($description),
            'notes_summary' => $this->summary($top, $heart, $base),
        ];
    }

    private function extract(string $source, string $label, array $stops): ?string
    {
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

    private function stripNoteAndMetadataSections(string $source): ?string
    {
        if ($source === '') {
            return null;
        }

        $clean = $source;

        foreach ([
            ['Top Notes', ['Heart Notes', 'Base Notes', 'Product Description', 'Longevity', 'Occasion', 'Why Choose', 'Materials & Care', 'Materials and Care', 'Care', 'Packaging']],
            ['Heart Notes', ['Base Notes', 'Product Description', 'Longevity', 'Occasion', 'Why Choose', 'Materials & Care', 'Materials and Care', 'Care', 'Packaging']],
            ['Base Notes', ['Product Description', 'Longevity', 'Occasion', 'Why Choose', 'Materials & Care', 'Materials and Care', 'Care', 'Packaging']],
        ] as [$label, $stops]) {
            $alternation = implode('|', array_map(
                static fn (string $stop) => preg_quote($stop, '/'),
                $stops
            ));

            $clean = preg_replace(
                '/' . preg_quote($label, '/') . '\s*:?\s*.+?(?=\s*(?:' . $alternation . ')\s*:?\s*|$)/is',
                ' ',
                $clean
            ) ?? $clean;
        }

        // Keep actual body text, remove only labels/metadata headings.
        $clean = preg_replace(
            '/\b(?:Product Description|Features|Longevity|Occasion|Why Choose(?: This)?|Materials?\s*(?:&|and)?\s*Care|Care|Packaging|Discover|Content)\b\s*:?\s*/i',
            ' ',
            $clean
        ) ?? $clean;

        return $this->clean($clean);
    }

    private function summary(?string $top, ?string $heart, ?string $base): ?string
    {
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
        $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace(["\r\n", "\r", "\n", "\t", '•'], ' ', $value);
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

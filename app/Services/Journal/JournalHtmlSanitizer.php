<?php

namespace App\Services\Journal;

use DOMDocument;
use DOMElement;
use DOMXPath;

class JournalHtmlSanitizer
{
    private array $allowedTags = [
        'p','br','h2','h3','h4','h5','strong','b','em','i','u','blockquote',
        'ul','ol','li','a','img','figure','figcaption','hr','div','span'
    ];

    private array $allowedAttributes = [
        'a' => ['href','title','target','rel'],
        'img' => ['src','alt','title','width','height','loading','decoding'],
        'figure' => ['class'],
        'div' => ['class'],
        'span' => ['class'],
    ];

    public function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') return '';

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="journal-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//*[@id="journal-root"]//*');

        for ($i = $nodes->length - 1; $i >= 0; $i--) {
            $node = $nodes->item($i);
            if (!$node instanceof DOMElement) continue;

            $tag = strtolower($node->tagName);
            if (!in_array($tag, $this->allowedTags, true)) {
                $this->unwrap($node);
                continue;
            }

            $allowed = $this->allowedAttributes[$tag] ?? [];
            $remove = [];
            foreach ($node->attributes as $attr) {
                if (!in_array(strtolower($attr->name), $allowed, true)) {
                    $remove[] = $attr->name;
                }
            }
            foreach ($remove as $attr) $node->removeAttribute($attr);

            if ($tag === 'a') {
                $href = trim((string) $node->getAttribute('href'));
                if ($href !== '' && !preg_match('#^(https?://|mailto:|tel:|/)#i', $href)) {
                    $node->removeAttribute('href');
                }
                if ($node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            }

            if ($tag === 'img') {
                $src = trim((string) $node->getAttribute('src'));
                if ($src !== '' && !preg_match('#^(https?://|/)#i', $src)) {
                    $node->removeAttribute('src');
                }
                if (!$node->hasAttribute('loading')) $node->setAttribute('loading', 'lazy');
                if (!$node->hasAttribute('decoding')) $node->setAttribute('decoding', 'async');
            }
        }

        $root = $dom->getElementById('journal-root');
        if (!$root) return '';

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    private function unwrap(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if (!$parent) return;
        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }
        $parent->removeChild($node);
    }
}

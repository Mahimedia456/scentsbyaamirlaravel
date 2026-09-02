<?php

namespace App\Services;

use App\Models\JournalPost;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class WordPressJournalImporter
{
    private string $baseUrl;
    private int $timeout;
    private int $perPage;
    private string $userAgent;
    private array $warnings = [];

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('wordpress.url'), '/');
        $this->timeout = (int) config('wordpress.timeout', 25);
        $this->perPage = (int) config('wordpress.per_page', 50);
        $this->userAgent = (string) config('wordpress.user_agent', 'ScentsByAamir-Laravel-Journal-Importer/1.0');
    }

    public function run(bool $dryRun = false, bool $updateExisting = false, bool $downloadImages = true, ?int $limit = null): array
    {
        $this->warnings = [];
        $stats = [
            'source' => $this->baseUrl,
            'found' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'images' => 0,
            'dry_run' => $dryRun,
            'warnings' => [],
        ];

        $processed = 0;
        $page = 1;
        $totalPages = 1;

        do {
            $response = $this->client()->get($this->baseUrl.'/wp-json/wp/v2/posts', [
                'status' => 'publish',
                'context' => 'view',
                '_embed' => 1,
                'per_page' => $this->perPage,
                'page' => $page,
                'orderby' => 'date',
                'order' => 'asc',
            ]);

            if ($response->status() === 400 && str_contains((string) $response->body(), 'rest_post_invalid_page_number')) {
                break;
            }

            if (! $response->successful()) {
                throw new RuntimeException('WordPress REST API request failed with HTTP '.$response->status().'.');
            }

            $posts = $response->json();
            if (! is_array($posts)) {
                throw new RuntimeException('WordPress REST API returned an unexpected response.');
            }

            $totalPages = max(1, (int) $response->header('X-WP-TotalPages'));
            $stats['found'] += count($posts);

            foreach ($posts as $wpPost) {
                if ($limit !== null && $processed >= $limit) {
                    break 2;
                }

                $processed++;
                $wordpressId = (int) ($wpPost['id'] ?? 0);
                if (! $wordpressId) {
                    $this->warnings[] = 'Skipped a WordPress post with no ID.';
                    $stats['skipped']++;
                    continue;
                }

                $existing = JournalPost::query()->where('wordpress_id', $wordpressId)->first();

                if ($existing && ! $updateExisting) {
                    $stats['skipped']++;
                    continue;
                }

                if ($dryRun) {
                    $existing ? $stats['updated']++ : $stats['created']++;
                    continue;
                }

                try {
                    $mapped = $this->mapPost($wpPost, $downloadImages, $stats);

                    if ($existing) {
                        $existing->fill($mapped)->save();
                        $stats['updated']++;
                    } else {
                        JournalPost::create($mapped);
                        $stats['created']++;
                    }
                } catch (Throwable $e) {
                    $stats['skipped']++;
                    $this->warnings[] = 'Post '.$wordpressId.': '.$e->getMessage();
                }
            }

            $page++;
        } while ($page <= $totalPages);

        $stats['warnings'] = $this->warnings;

        return $stats;
    }

    private function mapPost(array $post, bool $downloadImages, array &$stats): array
    {
        $wpId = (int) $post['id'];
        $title = $this->plainText(Arr::get($post, 'title.rendered')) ?: 'Untitled article';
        $incomingSlug = trim((string) ($post['slug'] ?? '')) ?: Str::slug($title);
        $slug = $this->uniqueSlug($incomingSlug, $wpId);
        $excerpt = $this->plainText(Arr::get($post, 'excerpt.rendered'));
        $rawContent = (string) Arr::get($post, 'content.rendered', '');
        $terms = $this->embeddedTerms($post);
        $author = $this->embeddedAuthor($post);
        $featuredRemote = $this->featuredImageUrl($post);
        $yoast = is_array($post['yoast_head_json'] ?? null) ? $post['yoast_head_json'] : [];

        $featuredPath = null;
        if ($featuredRemote && $downloadImages) {
            $featuredPath = $this->downloadImage($featuredRemote, $slug, 'featured', $stats);
        }
        $featuredPath ??= $featuredRemote;

        $content = $this->prepareContent($rawContent, $slug, $downloadImages, $stats);

        $ogRemote = Arr::get($yoast, 'og_image.0.url') ?: $featuredRemote;
        $ogPath = null;
        if ($ogRemote && $downloadImages) {
            if ($ogRemote === $featuredRemote && $featuredPath) {
                $ogPath = $featuredPath;
            } else {
                $ogPath = $this->downloadImage($ogRemote, $slug, 'social', $stats);
            }
        }
        $ogPath ??= $ogRemote ?: $featuredPath;

        $publishedAt = $this->carbon($post['date_gmt'] ?? $post['date'] ?? null);
        $modifiedAt = $this->carbon($post['modified_gmt'] ?? $post['modified'] ?? null);
        $categoryNames = $terms['categories'];

        return [
            'title' => Str::limit($title, 190, ''),
            'slug' => Str::limit($slug, 200, ''),
            'eyebrow' => Str::limit($categoryNames[0] ?? 'House Journal', 120, ''),
            'excerpt' => Str::limit($excerpt ?: Str::squish(strip_tags($content)), 1500, ''),
            'content' => $content,
            'featured_image_path' => $featuredPath,
            'status' => 'published',
            'source' => 'wordpress',
            'wordpress_id' => $wpId,
            'wordpress_url' => (string) ($post['link'] ?? ''),
            'wordpress_modified_at' => $modifiedAt,
            'author_name' => Str::limit($author ?: 'Scents by Aamir', 190, ''),
            'categories' => $categoryNames,
            'tags' => $terms['tags'],
            'meta_title' => Str::limit($this->plainText($yoast['title'] ?? '') ?: $title, 190, ''),
            'meta_description' => Str::limit($this->plainText($yoast['description'] ?? '') ?: $excerpt, 1000, ''),
            'canonical_url' => url('/journal/'.$slug),
            'og_title' => Str::limit($this->plainText($yoast['og_title'] ?? '') ?: $title, 190, ''),
            'og_description' => Str::limit($this->plainText($yoast['og_description'] ?? '') ?: $excerpt, 1000, ''),
            'og_image_path' => $ogPath,
            'published_at' => $publishedAt ?: now(),
            'imported_at' => now(),
        ];
    }

    private function prepareContent(string $html, string $slug, bool $downloadImages, array &$stats): string
    {
        if (trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="sba-import-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('sba-import-root');
        if (! $root) {
            return $this->sanitizeFallback($html);
        }

        $removeTags = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'noscript'];
        foreach ($removeTags as $tag) {
            while ($nodes = $root->getElementsByTagName($tag)) {
                if ($nodes->length === 0) break;
                $node = $nodes->item(0);
                $node?->parentNode?->removeChild($node);
            }
        }

        foreach (iterator_to_array($root->getElementsByTagName('*')) as $element) {
            if (! $element instanceof DOMElement) continue;

            foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
                $name = strtolower($attribute->name);
                if (str_starts_with($name, 'on') || in_array($name, ['style', 'srcdoc'], true)) {
                    $element->removeAttribute($attribute->name);
                }
            }

            if ($element->tagName === 'a') {
                $href = trim((string) $element->getAttribute('href'));
                if ($href !== '' && ! preg_match('#^(https?://|/|#|mailto:|tel:)#i', $href)) {
                    $element->removeAttribute('href');
                }
                if (preg_match('#^https?://#i', $href)) {
                    $element->setAttribute('rel', 'noopener noreferrer');
                }
            }

            if ($element->tagName === 'img') {
                $remote = $this->bestImageSource($element);
                if ($remote && $downloadImages) {
                    $local = $this->downloadImage($remote, $slug, 'inline-'.substr(sha1($remote), 0, 12), $stats);
                    if ($local) {
                        $element->setAttribute('src', Storage::disk('public')->url($local));
                    }
                }
                $element->removeAttribute('srcset');
                $element->removeAttribute('sizes');
                $element->removeAttribute('data-src');
                $element->removeAttribute('data-lazy-src');
                $element->setAttribute('loading', 'lazy');
                $element->setAttribute('decoding', 'async');
            }
        }

        return trim($this->innerHtml($root));
    }

    private function sanitizeFallback(string $html): string
    {
        return strip_tags($html, '<p><br><h2><h3><h4><h5><ul><ol><li><blockquote><strong><b><em><i><a><img><figure><figcaption><hr><table><thead><tbody><tr><th><td>');
    }

    private function bestImageSource(DOMElement $img): ?string
    {
        foreach (['src', 'data-src', 'data-lazy-src'] as $attribute) {
            $value = trim((string) $img->getAttribute($attribute));
            if ($value !== '' && ! str_starts_with($value, 'data:')) {
                return $this->absoluteUrl($value);
            }
        }

        return null;
    }

    private function downloadImage(string $url, string $slug, string $label, array &$stats): ?string
    {
        $url = $this->absoluteUrl($url);
        if (! $url || ! preg_match('#^https?://#i', $url)) {
            return null;
        }

        try {
            $response = $this->client()->get($url);
            if (! $response->successful()) {
                $this->warnings[] = 'Image download failed (HTTP '.$response->status().'): '.$url;
                return null;
            }

            $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            $extension = $this->extensionFromMime($mime) ?: strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
            $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true) ? $extension : 'jpg';
            if ($extension === 'jpeg') $extension = 'jpg';

            $filename = Str::slug($label).'-'.substr(sha1($url), 0, 10).'.'.$extension;
            $path = 'journal/'.$slug.'/'.$filename;
            Storage::disk('public')->put($path, $response->body());
            $stats['images']++;

            return $path;
        } catch (Throwable $e) {
            $this->warnings[] = 'Image download error: '.$url.' — '.$e->getMessage();
            return null;
        }
    }

    private function embeddedTerms(array $post): array
    {
        $categories = [];
        $tags = [];

        foreach (Arr::get($post, '_embedded.wp:term', []) as $group) {
            foreach ((array) $group as $term) {
                $name = $this->plainText($term['name'] ?? '');
                $taxonomy = (string) ($term['taxonomy'] ?? '');
                if ($name === '') continue;
                if ($taxonomy === 'category') $categories[] = $name;
                if ($taxonomy === 'post_tag') $tags[] = $name;
            }
        }

        return [
            'categories' => array_values(array_unique($categories)),
            'tags' => array_values(array_unique($tags)),
        ];
    }

    private function embeddedAuthor(array $post): ?string
    {
        $name = Arr::get($post, '_embedded.author.0.name');
        return $name ? $this->plainText($name) : null;
    }

    private function featuredImageUrl(array $post): ?string
    {
        $url = Arr::get($post, '_embedded.wp:featuredmedia.0.source_url');
        return $url ? $this->absoluteUrl((string) $url) : null;
    }

    private function uniqueSlug(string $slug, int $wordpressId): string
    {
        $base = Str::slug($slug) ?: 'journal-'.$wordpressId;
        $candidate = $base;
        $suffix = 2;

        while (JournalPost::query()
            ->where('slug', $candidate)
            ->where(function ($query) use ($wordpressId) {
                $query->whereNull('wordpress_id')->orWhere('wordpress_id', '!=', $wordpressId);
            })
            ->exists()) {
            $candidate = Str::limit($base, 190, '').'-'.$suffix++;
        }

        return $candidate;
    }

    private function plainText(?string $value): string
    {
        return Str::squish(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function carbon(?string $value): ?Carbon
    {
        if (! $value) return null;
        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function innerHtml(DOMElement $element): string
    {
        $html = '';
        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument->saveHTML($child);
        }
        return $html;
    }

    private function absoluteUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') return '';
        if (str_starts_with($url, '//')) return 'https:'.$url;
        if (preg_match('#^https?://#i', $url)) return $url;
        return $this->baseUrl.'/'.ltrim($url, '/');
    }

    private function extensionFromMime(string $mime): ?string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
            default => null,
        };
    }

    private function client()
    {
        return Http::acceptJson()
            ->withUserAgent($this->userAgent)
            ->timeout($this->timeout)
            ->connectTimeout(min(10, $this->timeout))
            ->retry(3, 700, throw: false);
    }
}

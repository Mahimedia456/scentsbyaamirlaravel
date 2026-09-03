<?php

namespace App\Services\Journal;

use App\Models\JournalPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WordPressJournalImporter
{
    public function __construct(private JournalHtmlSanitizer $sanitizer) {}

    public function import(array $options = []): array
    {
        $baseUrl = rtrim($options['url'] ?? config('journal_import.wordpress_url'), '/');
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $updateExisting = (bool) ($options['update_existing'] ?? false);
        $limit = isset($options['limit']) ? max(1, (int) $options['limit']) : null;
        $perPage = min(100, max(1, (int) config('journal_import.per_page', 20)));

        $stats = ['found'=>0,'created'=>0,'updated'=>0,'skipped'=>0,'images'=>0,'errors'=>[]];
        $page = 1;

        while (true) {
            $response = Http::acceptJson()
                ->timeout((int) config('journal_import.timeout', 30))
                ->retry(2, 500)
                ->get($baseUrl.'/wp-json/wp/v2/posts', [
                    'status' => 'publish',
                    'per_page' => $perPage,
                    'page' => $page,
                    '_embed' => 1,
                    'orderby' => 'date',
                    'order' => 'desc',
                ]);

            if ($response->status() === 400 && $page > 1) break;
            if (!$response->successful()) {
                throw new RuntimeException('WordPress API request failed: HTTP '.$response->status());
            }

            $posts = $response->json();
            if (!is_array($posts) || !$posts) break;

            foreach ($posts as $wp) {
                if ($limit !== null && $stats['found'] >= $limit) break 2;
                $stats['found']++;

                try {
                    $wpId = (int) ($wp['id'] ?? 0);
                    if ($wpId <= 0) throw new RuntimeException('Post has no valid WordPress ID.');

                    $existing = JournalPost::where('wordpress_id', $wpId)->first();
                    if ($existing && !$updateExisting) {
                        $stats['skipped']++;
                        continue;
                    }

                    $payload = $this->mapPost($wp, $baseUrl, $dryRun, $stats);

                    if ($dryRun) {
                        $existing ? $stats['updated']++ : $stats['created']++;
                        continue;
                    }

                    if ($existing) {
                        $existing->update($payload);
                        $stats['updated']++;
                    } else {
                        JournalPost::create($payload);
                        $stats['created']++;
                    }
                } catch (\Throwable $e) {
                    $stats['errors'][] = [
                        'id' => $wp['id'] ?? null,
                        'title' => html_entity_decode(strip_tags((string) data_get($wp, 'title.rendered', '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'message' => $e->getMessage(),
                    ];
                }
            }

            if (count($posts) < $perPage) break;
            $page++;
        }

        return $stats;
    }

    private function mapPost(array $wp, string $baseUrl, bool $dryRun, array &$stats): array
    {
        $title = $this->plain(data_get($wp, 'title.rendered', 'Untitled'));
        $slug = trim((string) ($wp['slug'] ?? '')) ?: Str::slug($title);
        $excerpt = trim($this->plain(data_get($wp, 'excerpt.rendered', '')));
        $content = (string) data_get($wp, 'content.rendered', '');

        [$content, $inlineCount] = $this->localizeInlineImages($content, $slug, $dryRun);
        $stats['images'] += $inlineCount;
        $content = $this->sanitizer->clean($content);

        $featured = $this->featuredImageUrl($wp);
        $featuredPath = null;
        if ($featured) {
            try {
                $featuredPath = $this->downloadImage($featured, $slug, 'featured', $dryRun);
            } catch (\Throwable $e) {
                $featuredPath = null;
            }

            // Keep a remote fallback if local download fails. This prevents
            // imported Journal cards/details from losing their featured image.
            if (!$featuredPath) {
                $featuredPath = $featured;
            } else {
                $stats['images']++;
            }
        }

        $yoast = is_array($wp['yoast_head_json'] ?? null) ? $wp['yoast_head_json'] : [];
        $author = $this->embeddedAuthor($wp);
        $categories = $this->embeddedTerms($wp, 'category');
        $tags = $this->embeddedTerms($wp, 'post_tag');
        $category = $categories[0]['name'] ?? null;

        return [
            'wordpress_id' => (int) $wp['id'],
            'title' => $title,
            'slug' => $slug,
            'eyebrow' => $category ?: 'House Journal',
            'excerpt' => $excerpt ?: Str::limit(trim(strip_tags($content)), 420),
            'content' => $content,
            'featured_image_path' => $featuredPath,
            'source_url' => $wp['link'] ?? $baseUrl,
            'author_name' => $author,
            'wordpress_categories' => $categories,
            'wordpress_tags' => $tags,
            'status' => 'published',
            'meta_title' => $this->plain($yoast['title'] ?? $title),
            'meta_description' => $this->plain($yoast['description'] ?? $excerpt),
            'published_at' => $wp['date_gmt'] ?? $wp['date'] ?? now(),
            'wordpress_modified_at' => $wp['modified_gmt'] ?? $wp['modified'] ?? null,
            'imported_at' => now(),
        ];
    }

    private function featuredImageUrl(array $wp): ?string
    {
        $media = data_get($wp, '_embedded.wp:featuredmedia.0');
        if (!is_array($media)) return null;
        return $media['source_url'] ?? data_get($media, 'media_details.sizes.full.source_url');
    }

    private function embeddedAuthor(array $wp): ?string
    {
        $name = data_get($wp, '_embedded.author.0.name');
        return $name ? $this->plain($name) : null;
    }

    private function embeddedTerms(array $wp, string $taxonomy): array
    {
        $groups = data_get($wp, '_embedded.wp:term', []);
        if (!is_array($groups)) return [];

        $terms = [];

        foreach ($groups as $group) {
            if (!is_array($group)) continue;

            foreach ($group as $term) {
                if (($term['taxonomy'] ?? null) !== $taxonomy || empty($term['name'])) {
                    continue;
                }

                $terms[] = [
                    'id' => isset($term['id']) ? (int) $term['id'] : null,
                    'name' => $this->plain($term['name']),
                    'slug' => trim((string) ($term['slug'] ?? '')),
                    'link' => isset($term['link']) ? (string) $term['link'] : null,
                ];
            }
        }

        return array_values(array_filter($terms, fn (array $term) => $term['name'] !== ''));
    }

    private function localizeInlineImages(string $html, string $slug, bool $dryRun): array
    {
        if (trim($html) === '') return ['', 0];

        $count = 0;
        $index = 0;
        $html = preg_replace_callback('/(<img\b[^>]*?\bsrc=["\'])(https?:\/\/[^"\']+)(["\'][^>]*>)/i', function ($m) use ($slug, $dryRun, &$count, &$index) {
            $index++;
            $path = $this->downloadImage(html_entity_decode($m[2]), $slug, 'inline-'.$index, $dryRun);
            if (!$path) return $m[0];
            $count++;
            $url = $dryRun ? $m[2] : '/media/'.ltrim(str_replace('\\', '/', $path), '/');
            return $m[1].e($url).$m[3];
        }, $html) ?? $html;

        return [$html, $count];
    }

    private function downloadImage(string $url, string $slug, string $prefix, bool $dryRun): ?string
    {
        $url = trim($url);
        if ($url === '') return null;
        if ($dryRun) return 'journal/'.Str::slug($slug).'/'.$prefix.'.webp';

        $response = Http::timeout((int) config('journal_import.timeout', 30))->retry(2, 500)->get($url);
        if (!$response->successful()) return null;

        $contentType = strtolower((string) $response->header('Content-Type'));
        $ext = match (true) {
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'gif') => 'gif',
            str_contains($contentType, 'jpeg'), str_contains($contentType, 'jpg') => 'jpg',
            default => strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION)) ?: 'jpg',
        };
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) $ext = 'jpg';

        $directory = trim(config('journal_import.storage_directory', 'journal'), '/').'/'.Str::slug($slug);
        $filename = $prefix.'-'.substr(sha1($url), 0, 12).'.'.$ext;
        $path = $directory.'/'.$filename;

        Storage::disk(config('journal_import.storage_disk', 'public'))->put($path, $response->body());
        return $path;
    }

    private function plain(mixed $value): string
    {
        return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

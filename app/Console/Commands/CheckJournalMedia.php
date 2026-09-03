<?php

namespace App\Console\Commands;

use App\Models\JournalPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckJournalMedia extends Command
{
    protected $signature = 'journal:media-check {--limit=5}';
    protected $description = 'Check Journal featured image DB paths, public-disk files and /media URLs.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $posts = JournalPost::query()
            ->whereNotNull('featured_image_path')
            ->latest('published_at')
            ->limit($limit)
            ->get();

        if ($posts->isEmpty()) {
            $this->warn('No Journal rows with featured_image_path were found.');
            return self::SUCCESS;
        }

        $rows = [];

        foreach ($posts as $post) {
            $path = trim((string) $post->featured_image_path);
            $remote = str_starts_with($path, 'http://') || str_starts_with($path, 'https://');

            $normalized = $path;
            if (str_starts_with($normalized, '/storage/')) {
                $normalized = substr($normalized, strlen('/storage/'));
            } elseif (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, strlen('storage/'));
            }

            $exists = !$remote && Storage::disk('public')->exists(ltrim($normalized, '/'));

            $rows[] = [
                $post->id,
                mb_strimwidth($post->title, 0, 34, '…'),
                mb_strimwidth($path, 0, 48, '…'),
                $remote ? 'REMOTE' : ($exists ? 'YES' : 'NO'),
                $remote ? $path : url('/media/'.ltrim($normalized, '/')),
            ];
        }

        $this->table(['ID', 'Post', 'DB image path', 'Local file', 'Browser URL'], $rows);
        $this->newLine();
        $this->line('Public disk root: '.Storage::disk('public')->path(''));
        $this->line('This project serves Journal media through /media/{path}; public/storage does not need to contain copied files.');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Journal\WordPressJournalImporter;
use Illuminate\Console\Command;

class ImportWordPressJournal extends Command
{
    protected $signature = 'wordpress:import-journal
        {--url= : WordPress site base URL}
        {--dry-run : Inspect what would be imported without changing DB/storage}
        {--update-existing : Refresh posts already linked by wordpress_id}
        {--limit= : Import only the first N posts}';

    protected $description = 'Import published WordPress posts, categories, tags, SEO data and images into the existing Laravel Journal. Comments are intentionally ignored.';

    public function handle(WordPressJournalImporter $importer): int
    {
        $url = trim((string) ($this->option('url') ?: config('journal_import.wordpress_url')));
        $this->info('WordPress source: '.$url);
        $this->line('Categories: imported');
        $this->line('Tags: imported');
        $this->line('Comments: ignored');

        try {
            $stats = $importer->import([
                'url' => $url,
                'dry_run' => (bool) $this->option('dry-run'),
                'update_existing' => (bool) $this->option('update-existing'),
                'limit' => $this->option('limit'),
            ]);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->table(['Metric','Count'], [
            ['Found', $stats['found']],
            [$this->option('dry-run') ? 'Would create' : 'Created', $stats['created']],
            [$this->option('dry-run') ? 'Would update' : 'Updated', $stats['updated']],
            ['Skipped', $stats['skipped']],
            [$this->option('dry-run') ? 'Images detected' : 'Images localized', $stats['images']],
            ['Errors', count($stats['errors'])],
        ]);

        foreach ($stats['errors'] as $error) {
            $this->warn(sprintf('[WP %s] %s — %s', $error['id'] ?? '?', $error['title'] ?? 'Untitled', $error['message']));
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No database or storage changes were made.');
        } else {
            $this->info('Journal import complete. Existing Journal/Admin routes and site layout were not changed.');
        }

        return count($stats['errors']) ? self::FAILURE : self::SUCCESS;
    }
}

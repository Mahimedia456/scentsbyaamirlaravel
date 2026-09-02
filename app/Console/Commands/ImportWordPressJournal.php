<?php

namespace App\Console\Commands;

use App\Services\WordPressJournalImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressJournal extends Command
{
    protected $signature = 'wordpress:import-journal
        {--dry-run : Read WordPress and report what would happen without writing DB/files}
        {--update-existing : Update records previously imported from WordPress}
        {--no-images : Import post data but leave image URLs remote}
        {--limit= : Import only the first N posts for testing}';

    protected $description = 'Import published WordPress posts into the Laravel Journal, including local copies of featured and inline images.';

    public function handle(WordPressJournalImporter $importer): int
    {
        $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;

        $this->info('WordPress source: '.config('wordpress.url'));
        if ($this->option('dry-run')) $this->warn('DRY RUN — no database or image files will be changed.');

        try {
            $stats = $importer->run(
                dryRun: (bool) $this->option('dry-run'),
                updateExisting: (bool) $this->option('update-existing'),
                downloadImages: ! (bool) $this->option('no-images'),
                limit: $limit,
            );
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->table(['Found', 'Created', 'Updated', 'Skipped', 'Images'], [[
            $stats['found'], $stats['created'], $stats['updated'], $stats['skipped'], $stats['images'],
        ]]);

        foreach ($stats['warnings'] as $warning) {
            $this->warn($warning);
        }

        $this->info($this->option('dry-run') ? 'Dry run complete.' : 'Journal import complete.');

        return self::SUCCESS;
    }
}

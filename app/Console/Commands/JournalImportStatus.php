<?php
namespace App\Console\Commands;

use App\Models\JournalPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JournalImportStatus extends Command
{
    protected $signature = 'journal:status';
    protected $description = 'Show the Laravel database and Journal import status.';

    public function handle(): int
    {
        $this->line('Database: '.DB::connection()->getDatabaseName());
        if (!Schema::hasTable('journal_posts')) {
            $this->error('journal_posts table does not exist. Run php artisan migrate.');
            return self::FAILURE;
        }
        $total=JournalPost::count(); $published=JournalPost::where('status','published')->count();
        $imported=Schema::hasColumn('journal_posts','wordpress_id') ? JournalPost::whereNotNull('wordpress_id')->count() : 0;
        $this->table(['Metric','Count'], [['All Journal rows',$total],['Published',$published],['WordPress imported',$imported]]);
        if ($total===0) $this->warn('No database Journal rows exist. A dry-run does not save anything; run php artisan wordpress:import-journal.');
        return self::SUCCESS;
    }
}

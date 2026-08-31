<?php
namespace App\Console\Commands;

use App\Models\WooCommerceImportRun;
use App\Services\WooCommerceImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ImportWooCommerce extends Command
{
    protected $signature = 'woocommerce:import {run : Numeric import run ID from Admin > WooCommerce Migration Center} {--key= : WooCommerce consumer key} {--secret= : WooCommerce consumer secret}';
    protected $description = 'Execute a queued one-time WooCommerce to Laravel migration run';

    public function handle(WooCommerceImporter $importer): int
    {
        if (!Schema::hasTable('woocommerce_import_runs')) {
            $this->error('WooCommerce migration tables are missing. Run: php artisan migrate');
            return self::FAILURE;
        }

        $rawRun = (string) $this->argument('run');
        if (!ctype_digit($rawRun) || (int) $rawRun < 1) {
            $this->error('Use a real numeric run ID, not RUN_ID. Create a run in Admin > WooCommerce Migration Center, then run for example: php artisan woocommerce:import 1 --key=ck_xxx --secret=cs_xxx');
            return self::FAILURE;
        }

        $run = WooCommerceImportRun::find((int) $rawRun);
        if (!$run) {
            $this->error("WooCommerce import run {$rawRun} was not found. Create/select a run in Admin > WooCommerce Migration Center first.");
            return self::FAILURE;
        }

        $key = $this->option('key') ?: env('WOOCOMMERCE_CONSUMER_KEY');
        $secret = $this->option('secret') ?: env('WOOCOMMERCE_CONSUMER_SECRET');
        if (!$key || !$secret) {
            $this->error('Provide --key and --secret or set WOOCOMMERCE_CONSUMER_KEY/WOOCOMMERCE_CONSUMER_SECRET.');
            return self::FAILURE;
        }

        try {
            $run = $importer->run($run, $key, $secret);
            $this->info('Import completed: '.json_encode($run->summary));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}

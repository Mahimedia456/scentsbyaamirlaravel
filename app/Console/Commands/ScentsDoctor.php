<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScentsDoctor extends Command
{
    protected $signature = 'scents:doctor';
    protected $description = 'Run a production-readiness diagnostic for Scents by Aamir';

    public function handle(): int
    {
        $failed = false;
        $check = function (bool $ok, string $label, string $fix = '') use (&$failed): void {
            if ($ok) {
                $this->info("[OK] {$label}");
            } else {
                $failed = true;
                $this->error("[FAIL] {$label}".($fix ? " — {$fix}" : ''));
            }
        };

        try {
            DB::connection()->getPdo();
            $check(true, 'Database connection');
        } catch (\Throwable $e) {
            $check(false, 'Database connection', $e->getMessage());
        }

        foreach ([
            'users','products','product_variants','customers','orders','order_items',
            'payment_methods','shipping_zones','store_settings','woocommerce_import_runs','woocommerce_import_maps',
        ] as $table) {
            $check(Schema::hasTable($table), "Table: {$table}", 'Run php artisan migrate');
        }

        $check((bool) config('app.key'), 'APP_KEY', 'Run php artisan key:generate');
        $check(is_dir(storage_path('framework/sessions')), 'Session storage directory');
        $check(is_writable(storage_path()), 'Storage writable', 'Fix hosting permissions for storage/');
        $check(file_exists(public_path('build/manifest.json')), 'Vite production manifest', 'Run npm run build');

        if (app()->environment('production')) {
            $check(config('app.debug') === false, 'APP_DEBUG=false');
            $check(str_starts_with((string) config('app.url'), 'https://'), 'APP_URL uses HTTPS');
            $check(config('session.secure') === true, 'SESSION_SECURE_COOKIE=true');
        } else {
            $this->warn('[INFO] Production-only HTTPS/cookie checks skipped because APP_ENV is not production.');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}

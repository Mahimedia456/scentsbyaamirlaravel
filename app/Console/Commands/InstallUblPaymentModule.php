<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class InstallUblPaymentModule extends Command
{
    protected $signature = 'ubl:install';
    protected $description = 'Attach UBL payment routes to the current Laravel routes/web.php without replacing the route file.';

    public function handle(): int
    {
        $path = base_path('routes/web.php');
        if (!File::exists($path)) throw new RuntimeException('routes/web.php not found.');

        $contents = File::get($path);
        $require = "require __DIR__.'/payments.php';";

        if (!str_contains($contents, $require)) {
            File::put($path, rtrim($contents).PHP_EOL.PHP_EOL.$require.PHP_EOL);
            $this->info('UBL payment routes attached to routes/web.php.');
        } else {
            $this->info('UBL payment routes are already attached.');
        }

        $this->line('Next: php artisan optimize:clear');
        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class UblDiagnose extends Command
{
    protected $signature = 'ubl:diagnose {--connect}';
    protected $description = 'Show UBL gateway configuration and optionally test outbound connectivity to the configured EPG server.';

    public function handle(): int
    {
        $this->table(['Setting', 'Value'], [
            ['Mode', config('ubl.mode')],
            ['Base URL', config('ubl.base_url')],
            ['Public URL', config('ubl.public_url')],
            ['Customer', config('ubl.customer')],
            ['Store', config('ubl.store')],
            ['Terminal', config('ubl.terminal')],
            ['Username', config('ubl.username')],
            ['Password', filled(config('ubl.password')) ? '[SET]' : '[MISSING]'],
            ['Currency', config('ubl.currency')],
            ['SSL verify', config('ubl.verify_ssl') ? 'YES' : 'NO'],
        ]);

        if (!$this->option('connect')) {
            $this->comment('Run with --connect to test HTTPS/TLS connectivity (no payment is created).');
            return self::SUCCESS;
        }

        try {
            $response = Http::timeout((int) config('ubl.timeout', 30))
                ->connectTimeout((int) config('ubl.connect_timeout', 12))
                ->withOptions(['verify' => (bool) config('ubl.verify_ssl', true)])
                ->get((string) config('ubl.base_url'));
            $this->info('Connectivity OK. HTTP '.$response->status());
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Connectivity failed: '.$e->getMessage());
            $this->warn('If this is cURL error 7/timeout, ask the host to allow outbound TCP 2443 to the UBL/EPG gateway.');
            return self::FAILURE;
        }
    }
}

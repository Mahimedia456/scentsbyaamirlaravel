<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CheckMailConfiguration extends Command
{
    protected $signature = 'storefront:mail-check {--to=}';

    protected $description = 'Check the effective mail configuration and optionally send a live test email.';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $from = (string) config('mail.from.address');
        $host = (string) config('mail.mailers.smtp.host');
        $port = (string) config('mail.mailers.smtp.port');
        $username = (string) config('mail.mailers.smtp.username');

        $this->table(
            ['Setting', 'Effective value'],
            [
                ['MAIL_MAILER', $mailer ?: '(empty)'],
                ['MAIL_FROM_ADDRESS', $from ?: '(empty)'],
                ['SMTP host', $host ?: '(empty)'],
                ['SMTP port', $port ?: '(empty)'],
                ['SMTP username', $username ?: '(empty)'],
                ['SMTP password', filled(config('mail.mailers.smtp.password')) ? 'SET' : 'MISSING'],
            ]
        );

        if ($mailer === 'log') {
            $this->warn('MAIL_MAILER=log: messages are written to Laravel logs and are NOT delivered to inboxes.');
        }

        $to = trim((string) $this->option('to'));

        if ($to === '') {
            $this->info('Configuration check complete. Add --to=email@example.com to send a live test.');
            return self::SUCCESS;
        }

        try {
            Mail::raw(
                'Scents by Aamir mail configuration test. If you received this message, Laravel handed the message to the configured mail transport successfully.',
                fn ($message) => $message->to($to)->subject('Scents by Aamir — mail test')
            );
        } catch (Throwable $e) {
            $this->error('Mail test failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Mail transport accepted the test message for {$to}.");
        return self::SUCCESS;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class MailDiagnosticsController extends Controller
{
    public function index(): View
    {
        $mailer = config('mail.default');
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $from = config('mail.from.address');
        $fromName = config('mail.from.name');
        $scheme = config('mail.mailers.smtp.scheme');
        $username = config('mail.mailers.smtp.username');
        $passwordSet = filled(config('mail.mailers.smtp.password'));

        $configured = $mailer === 'smtp'
            && in_array($scheme, ['smtp', 'smtps'], true)
            && filled($host)
            && $host !== '127.0.0.1'
            && filled($username)
            && $passwordSet
            && filled($from);

        return view('admin.system.mail-diagnostics', compact(
            'mailer',
            'host',
            'port',
            'from',
            'fromName',
            'scheme',
            'username',
            'passwordSet',
            'configured'
        ));
    }

    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        try {
            Mail::send('emails.admin-mail-test', [
                'recipient' => $data['email'],
                'sentAt' => now(),
            ], function ($message) use ($data) {
                $message
                    ->to($data['email'])
                    ->subject('Scents by Aamir — Email delivery test');
            });

            if (Schema::hasTable('admin_notifications')) {
                AdminNotification::updateOrCreate(
                    ['key' => 'mail-test-latest'],
                    [
                        'type' => 'success',
                        'title' => 'Test email accepted by mail transport',
                        'message' => 'A test message was sent to ' . $data['email'] . '. Confirm delivery in the destination inbox.',
                        'action_url' => route('admin.mail-diagnostics.index'),
                        'action_label' => 'Mail diagnostics',
                        'read_at' => null,
                        'dismissed_at' => null,
                        'resolved_at' => null,
                    ]
                );
            }

            return back()->with('success', 'Test email was handed to the configured mail transport. Check the destination inbox and spam folder.');
        } catch (Throwable $e) {
            report($e);

            if (Schema::hasTable('admin_notifications')) {
                AdminNotification::updateOrCreate(
                    ['key' => 'mail-test-latest'],
                    [
                        'type' => 'danger',
                        'title' => 'Test email failed',
                        'message' => mb_substr($e->getMessage(), 0, 1000),
                        'action_url' => route('admin.mail-diagnostics.index'),
                        'action_label' => 'Mail diagnostics',
                        'read_at' => null,
                        'dismissed_at' => null,
                        'resolved_at' => null,
                    ]
                );
            }

            return back()->with('error', 'Email could not be sent. Review SMTP settings and the Laravel log.');
        }
    }
}

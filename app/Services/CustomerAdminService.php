<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class CustomerAdminService
{
    public function sendActivation(Customer $customer): bool
    {
        if (!filled($customer->email)) {
            return false;
        }

        $url = URL::temporarySignedRoute(
            'customer.activate',
            now()->addHours(48),
            ['customer' => $customer->id, 'hash' => sha1($customer->email)]
        );

        return $this->send(
            customer: $customer,
            subject: 'Activate your Scents by Aamir account',
            headline: 'Activate your account',
            message: 'Confirm your email address to activate your Scents by Aamir account.',
            buttonLabel: 'Activate account',
            buttonUrl: $url,
            key: 'customer-activation-' . $customer->id . '-' . now()->format('YmdHi'),
        );
    }

    public function sendAccountStatus(Customer $customer, bool $active): bool
    {
        return $this->send(
            customer: $customer,
            subject: $active ? 'Your Scents by Aamir account is active' : 'Scents by Aamir account status update',
            headline: $active ? 'Your account is active' : 'Your account is inactive',
            message: $active
                ? 'Your customer account is active and available for sign in.'
                : 'Your customer account has been deactivated. Contact Scents by Aamir customer care if you need assistance.',
            buttonLabel: $active ? 'Visit Scents by Aamir' : null,
            buttonUrl: $active ? url('/') : null,
            key: 'customer-status-' . $customer->id . '-' . ($active ? 'active' : 'inactive') . '-' . now()->format('YmdHi'),
        );
    }

    private function send(
        Customer $customer,
        string $subject,
        string $headline,
        string $message,
        ?string $buttonLabel,
        ?string $buttonUrl,
        string $key,
    ): bool {
        if (!filled($customer->email)) {
            return false;
        }

        try {
            Mail::send('emails.customer-account', compact('customer','headline','message','buttonLabel','buttonUrl'), function ($mail) use ($customer, $subject) {
                $mail->to($customer->email, $customer->full_name ?: null)->subject($subject);
            });

            $this->record($key, 'success', 'Customer email sent', $customer->full_name . ' · ' . $customer->email, route('admin.customers.show', $customer));
            return true;
        } catch (Throwable $e) {
            report($e);
            $this->record($key, 'danger', 'Customer email failed', $customer->full_name . ' · ' . Str::limit($e->getMessage(), 700), route('admin.customers.show', $customer));
            return false;
        }
    }

    private function record(string $key, string $type, string $title, string $message, string $url): void
    {
        if (!Schema::hasTable('admin_notifications')) {
            return;
        }

        AdminNotification::updateOrCreate(
            ['key' => $key],
            [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $url,
                'action_label' => 'Open customer',
                'read_at' => null,
                'dismissed_at' => null,
                'resolved_at' => null,
            ]
        );
    }
}

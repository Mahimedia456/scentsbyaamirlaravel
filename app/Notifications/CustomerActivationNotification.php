<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomerActivationNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'customer.activate',
            now()->addHours(24),
            ['customer' => $notifiable->getKey()]
        );

        return (new MailMessage)
            ->subject('Activate your Scents by Aamir account')
            ->view('emails.customer-activation', [
                'customer' => $notifiable,
                'activationUrl' => $url,
            ]);
    }
}

<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TransactionalMailService
{
    public function order(Order $order, string $event, ?string $reason = null): void
    {
        $order->loadMissing('items', 'customer');

        $customerEvents = [
            'placed',
            'confirmed',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
            'refunded',
            'payment_approved',
            'payment_rejected',
            'payment_failed',
        ];

        if (in_array($event, $customerEvents, true) && filled($order->customer_email)) {
            $this->sendCustomer($order, $event, $reason);
        }

        if ($event === 'placed') {
            $this->sendAdminNewOrder($order);
        }
    }

    private function sendCustomer(Order $order, string $event, ?string $reason): void
    {
        [$subject, $headline, $message] = $this->customerCopy($order, $event, $reason);

        try {
            Mail::send('emails.order-lifecycle', [
                'order' => $order,
                'event' => $event,
                'headline' => $headline,
                'intro' => $message,
                'reason' => $reason,
            ], function ($mail) use ($order, $subject) {
                $mail->to($order->customer_email, $order->customer_name ?: null)
                    ->subject($subject);
            });

            $this->record(
                key: 'mail-order-customer-' . $order->id . '-' . $event,
                type: 'success',
                title: 'Customer order email sent',
                message: $order->order_number . ' · ' . str_replace('_', ' ', $event) . ' · ' . $order->customer_email,
                actionUrl: route('admin.orders.show', $order),
            );
        } catch (Throwable $e) {
            report($e);

            $this->record(
                key: 'mail-order-customer-' . $order->id . '-' . $event,
                type: 'danger',
                title: 'Customer order email failed',
                message: $order->order_number . ' · ' . mb_substr($e->getMessage(), 0, 800),
                actionUrl: route('admin.orders.show', $order),
            );
        }
    }

    private function sendAdminNewOrder(Order $order): void
    {
        $recipient = config('commerce.order_notification_email');

        if (!filled($recipient)) {
            return;
        }

        try {
            Mail::send('emails.admin-new-order', [
                'order' => $order,
            ], function ($mail) use ($order, $recipient) {
                $mail->to($recipient)
                    ->subject('New order ' . $order->order_number . ' — ' . $order->currency . ' ' . number_format((float) $order->grand_total, 0));
            });

            $this->record(
                key: 'mail-order-admin-' . $order->id,
                type: 'success',
                title: 'New-order admin email sent',
                message: $order->order_number . ' · ' . $recipient,
                actionUrl: route('admin.orders.show', $order),
            );
        } catch (Throwable $e) {
            report($e);

            $this->record(
                key: 'mail-order-admin-' . $order->id,
                type: 'danger',
                title: 'New-order admin email failed',
                message: $order->order_number . ' · ' . mb_substr($e->getMessage(), 0, 800),
                actionUrl: route('admin.orders.show', $order),
            );
        }
    }

    private function customerCopy(Order $order, string $event, ?string $reason): array
    {
        $copy = [
            'placed' => [
                'Order received — ' . $order->order_number,
                'We have received your order.',
                'Your order is now in our system. We will keep you updated as it moves through confirmation and fulfilment.',
            ],
            'confirmed' => [
                'Order confirmed — ' . $order->order_number,
                'Your order is confirmed.',
                'Your order has been confirmed and is moving into preparation.',
            ],
            'processing' => [
                'Order in preparation — ' . $order->order_number,
                'Your order is being prepared.',
                'Our team is preparing your Scents by Aamir order for dispatch.',
            ],
            'shipped' => [
                'Order shipped — ' . $order->order_number,
                'Your order is on the way.',
                filled($order->tracking_number)
                    ? 'Your parcel has been dispatched. Tracking reference: ' . $order->tracking_number . '.'
                    : 'Your parcel has been dispatched and is on its way.',
            ],
            'delivered' => [
                'Order delivered — ' . $order->order_number,
                'Your order has been delivered.',
                'We hope you enjoy your Scents by Aamir selection. Thank you for choosing us.',
            ],
            'cancelled' => [
                'Order cancelled — ' . $order->order_number,
                'Your order has been cancelled.',
                'This order is now cancelled. If a payment requires follow-up, our team will handle it according to the payment method and order record.',
            ],
            'refunded' => [
                'Order refunded — ' . $order->order_number,
                'Your order has been refunded.',
                'The order has been marked refunded. Bank settlement time may vary by payment provider.',
            ],
            'payment_approved' => [
                'Payment approved — ' . $order->order_number,
                'Your payment has been verified.',
                'We have verified your payment and your order can continue through fulfilment.',
            ],
            'payment_rejected' => [
                'Payment verification issue — ' . $order->order_number,
                'We could not verify your payment.',
                $reason
                    ? 'Reason: ' . $reason
                    : 'Please review your payment details or contact Scents by Aamir support for assistance.',
            ],
            'payment_failed' => [
                'Payment failed — ' . $order->order_number,
                'There is an issue with your payment.',
                'The payment status for this order is failed. Please contact support if you need assistance.',
            ],
        ];

        return $copy[$event] ?? [
            'Order update — ' . $order->order_number,
            'Your order has been updated.',
            'There is a new update on your Scents by Aamir order.',
        ];
    }

    private function record(
        string $key,
        string $type,
        string $title,
        string $message,
        string $actionUrl,
    ): void {
        if (!Schema::hasTable('admin_notifications')) {
            return;
        }

        AdminNotification::updateOrCreate(
            ['key' => $key],
            [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'action_label' => 'Open order',
                'read_at' => null,
                'dismissed_at' => null,
                'resolved_at' => null,
            ]
        );
    }
}

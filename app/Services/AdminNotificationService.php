<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\ContactInquiry;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

class AdminNotificationService
{
    public function refreshSystemAlerts(): void
    {
        if (!Schema::hasTable('admin_notifications')) {
            return;
        }

        $mailReady = config('mail.default') === 'smtp'
            && filled(config('mail.mailers.smtp.host'))
            && config('mail.mailers.smtp.host') !== '127.0.0.1'
            && filled(config('mail.from.address'));

        $this->syncAlert(
            key: 'system-mail-configuration',
            active: !$mailReady,
            type: 'warning',
            title: 'SMTP configuration required',
            message: 'Transactional email code is enabled, but production SMTP credentials are not fully configured.',
            actionUrl: route('admin.mail-diagnostics.index'),
            actionLabel: 'Open mail diagnostics',
        );

        if (Schema::hasTable('orders')) {
            $pendingOrders = Order::query()
                ->whereIn('status', ['pending', 'processing'])
                ->count();

            $this->syncAlert(
                key: 'operations-pending-orders',
                active: $pendingOrders > 0,
                type: $pendingOrders >= 10 ? 'warning' : 'info',
                title: $pendingOrders . ' order' . ($pendingOrders === 1 ? '' : 's') . ' need attention',
                message: 'Review pending and processing orders to keep fulfilment moving.',
                actionUrl: route('admin.orders.index'),
                actionLabel: 'Review orders',
                data: ['count' => $pendingOrders],
            );
        }

        if (Schema::hasTable('customers')) {
            $pendingActivation = Customer::query()
                ->whereNull('email_verified_at')
                ->count();

            $this->syncAlert(
                key: 'customers-pending-activation',
                active: $pendingActivation > 0,
                type: 'info',
                title: $pendingActivation . ' customer' . ($pendingActivation === 1 ? '' : 's') . ' awaiting activation',
                message: 'These customer accounts have registered but have not verified their email yet.',
                actionUrl: route('admin.customers.index'),
                actionLabel: 'Open customers',
                data: ['count' => $pendingActivation],
            );
        }

        if (Schema::hasTable('products')) {
            $lowStock = Product::query()
                ->where('status', 'active')
                ->where('track_inventory', true)
                ->where('stock', '<=', 5)
                ->count();

            $this->syncAlert(
                key: 'catalog-low-stock',
                active: $lowStock > 0,
                type: 'warning',
                title: $lowStock . ' tracked product' . ($lowStock === 1 ? '' : 's') . ' low on stock',
                message: 'Tracked inventory at five units or fewer should be reviewed.',
                actionUrl: route('admin.inventory.index'),
                actionLabel: 'Open inventory',
                data: ['count' => $lowStock],
            );
        }

        if (Schema::hasTable('contact_inquiries')) {
            $openSupport = ContactInquiry::query()
                ->whereIn('status', ['new', 'open', 'pending'])
                ->count();

            $this->syncAlert(
                key: 'support-open-inquiries',
                active: $openSupport > 0,
                type: 'info',
                title: $openSupport . ' support inquir' . ($openSupport === 1 ? 'y' : 'ies') . ' open',
                message: 'Customer-care requests are waiting in the support queue.',
                actionUrl: route('admin.contact-inquiries.index'),
                actionLabel: 'Open support',
                data: ['count' => $openSupport],
            );
        }
    }

    private function syncAlert(
        string $key,
        bool $active,
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        array $data = [],
    ): void {
        $existing = AdminNotification::where('key', $key)->first();

        if (!$active) {
            if ($existing && !$existing->resolved_at) {
                $existing->forceFill(['resolved_at' => now()])->save();
            }
            return;
        }

        if (!$existing) {
            AdminNotification::create([
                'key' => $key,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'action_label' => $actionLabel,
                'data' => $data,
            ]);
            return;
        }

        $existing->forceFill([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_label' => $actionLabel,
            'data' => $data,
            'resolved_at' => null,
        ])->save();
    }
}

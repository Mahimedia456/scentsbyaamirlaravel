<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\AdminNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(AdminNotificationService $notificationService): View
    {
        $databaseOnline = true;

        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $databaseOnline = false;
        }

        $stats = [
            'products' => 0,
            'customers' => 0,
            'orders' => 0,
            'revenue' => 0,
            'aov' => 0,
            'pending_orders' => 0,
            'new_customers_30d' => 0,
            'orders_30d' => 0,
            'revenue_30d' => 0,
        ];

        $recentOrders = collect();
        $salesSeries = collect();
        $statusBreakdown = collect();
        $topProducts = collect();
        $notifications = collect();
        $unreadNotifications = 0;

        if ($databaseOnline) {
            $sales = Order::query()->whereNotIn('status', ['cancelled', 'refunded']);
            $start30 = now()->subDays(29)->startOfDay();

            $stats = [
                'products' => Product::count(),
                'customers' => Customer::count(),
                'orders' => Order::count(),
                'revenue' => (float) (clone $sales)->sum('grand_total'),
                'aov' => (float) ((clone $sales)->avg('grand_total') ?? 0),
                'pending_orders' => Order::whereIn('status', ['pending', 'processing'])->count(),
                'new_customers_30d' => Customer::where('created_at', '>=', $start30)->count(),
                'orders_30d' => Order::where('created_at', '>=', $start30)->count(),
                'revenue_30d' => (float) (clone $sales)->where('created_at', '>=', $start30)->sum('grand_total'),
            ];

            $recentOrders = Order::query()->latest('id')->limit(7)->get();

            $rawDaily = Order::query()
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->where('created_at', '>=', $start30)
                ->selectRaw('DATE(created_at) as day, SUM(grand_total) as revenue, COUNT(*) as orders_count')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy('day');

            $salesSeries = collect(range(0, 29))->map(function ($offset) use ($start30, $rawDaily) {
                $day = $start30->copy()->addDays($offset)->toDateString();
                $row = $rawDaily->get($day);

                return [
                    'date' => $day,
                    'label' => Carbon::parse($day)->format('d M'),
                    'revenue' => (float) ($row->revenue ?? 0),
                    'orders' => (int) ($row->orders_count ?? 0),
                ];
            });

            $statusBreakdown = Order::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->orderByDesc('total')
                ->get();

            if (Schema::hasTable('order_items')) {
                $topProducts = DB::table('order_items')
                    ->select(
                        'product_name',
                        DB::raw('SUM(quantity) as qty'),
                        DB::raw('SUM(line_total) as revenue')
                    )
                    ->groupBy('product_name')
                    ->orderByDesc('revenue')
                    ->limit(5)
                    ->get();
            }

            $notificationService->refreshSystemAlerts();

            if (Schema::hasTable('admin_notifications')) {
                $notifications = AdminNotification::query()
                    ->visible()
                    ->latest()
                    ->limit(5)
                    ->get();

                $unreadNotifications = AdminNotification::query()
                    ->visible()
                    ->unread()
                    ->count();
            }
        }

        $mailConfigured = config('mail.default') === 'smtp'
            && filled(config('mail.mailers.smtp.host'))
            && config('mail.mailers.smtp.host') !== '127.0.0.1'
            && filled(config('mail.from.address'));

        return view('admin.dashboard.index', compact(
            'stats',
            'recentOrders',
            'databaseOnline',
            'mailConfigured',
            'salesSeries',
            'statusBreakdown',
            'topProducts',
            'notifications',
            'unreadNotifications'
        ));
    }
}

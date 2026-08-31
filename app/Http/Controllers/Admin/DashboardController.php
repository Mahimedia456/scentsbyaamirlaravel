<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $databaseOnline = true;
        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $databaseOnline = false;
        }

        $stats = [
            'products' => $databaseOnline ? Product::count() : 0,
            'customers' => $databaseOnline ? Customer::count() : 0,
            'orders' => $databaseOnline ? Order::count() : 0,
            'revenue' => $databaseOnline ? (float) Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('grand_total') : 0,
        ];

        $recentOrders = $databaseOnline
            ? Order::query()->latest('id')->limit(6)->get()
            : collect();

        return view('admin.dashboard.index', compact('stats', 'recentOrders', 'databaseOnline'));
    }
}

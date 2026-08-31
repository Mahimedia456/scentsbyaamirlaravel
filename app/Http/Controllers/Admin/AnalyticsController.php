<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    public function index()
    {
        $required = ['orders', 'customers', 'products', 'order_items'];
        $setupRequired = collect($required)->contains(fn ($table) => !Schema::hasTable($table));

        $stats = ['revenue' => 0, 'orders' => 0, 'customers' => 0, 'products' => 0, 'aov' => 0];
        $top = collect();

        if (!$setupRequired) {
            $sales = Order::whereNotIn('status', ['cancelled', 'refunded']);
            $stats = [
                'revenue' => (float) (clone $sales)->sum('grand_total'),
                'orders' => Order::count(),
                'customers' => Customer::count(),
                'products' => Product::count(),
                'aov' => (float) ((clone $sales)->avg('grand_total') ?? 0),
            ];

            $top = DB::table('order_items')
                ->select('product_name', DB::raw('SUM(quantity) qty'), DB::raw('SUM(line_total) revenue'))
                ->groupBy('product_name')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();
        }

        return view('admin.analytics.index', compact('stats', 'top', 'setupRequired'));
    }
}

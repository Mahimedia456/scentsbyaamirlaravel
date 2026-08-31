<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        Product::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            })
            ->limit(6)
            ->get()
            ->each(function ($product) use ($results) {
                $results->push([
                    'group' => 'Products',
                    'title' => $product->name,
                    'meta' => $product->sku ?: $product->size_label,
                    'url' => route('admin.products.edit', $product),
                ]);
            });

        Order::query()
            ->where(function ($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_email', 'like', "%{$q}%");
            })
            ->limit(6)
            ->get()
            ->each(function ($order) use ($results) {
                $results->push([
                    'group' => 'Orders',
                    'title' => $order->order_number,
                    'meta' => trim(($order->customer_name ?: 'Guest') . ' · ' . strtoupper($order->status)),
                    'url' => route('admin.orders.show', $order),
                ]);
            });

        Customer::query()
            ->where(function ($query) use ($q) {
                $query->where('email', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(6)
            ->get()
            ->each(function ($customer) use ($results) {
                $results->push([
                    'group' => 'Customers',
                    'title' => $customer->full_name ?: $customer->email,
                    'meta' => $customer->email,
                    'url' => route('admin.customers.edit', $customer),
                ]);
            });

        return response()->json([
            'results' => $results->take(16)->values(),
        ]);
    }
}

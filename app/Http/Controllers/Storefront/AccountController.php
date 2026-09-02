<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerNotification;
use App\Models\Order;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user()->load('addresses');
        $recentOrders = $customer->orders()->with('items')->latest('placed_at')->latest()->take(3)->get();
        $orderCount = $customer->orders()->count();
        $openOrderCount = $customer->orders()->whereNotIn('status', ['delivered', 'cancelled', 'refunded'])->count();
        $unreadNotifications = CustomerNotification::where('customer_id', $customer->id)->whereNull('read_at')->count();

        return view('store.account', compact(
            'customer',
            'recentOrders',
            'orderCount',
            'openOrderCount',
            'unreadNotifications'
        ));
    }

    public function update(Request $request)
    {
        $customer = auth('customer')->user();

        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name' => ['nullable','string','max:100'],
            'email' => ['required','email','max:190','unique:customers,email,'.$customer->id],
            'phone' => ['nullable','string','max:40','unique:customers,phone,'.$customer->id],
            'marketing_opt_in' => ['nullable','boolean'],
        ]);

        $data['marketing_opt_in'] = $request->boolean('marketing_opt_in');
        $customer->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function address(Request $request)
    {
        $customer = auth('customer')->user();

        $data = $request->validate([
            'label' => ['nullable','string','max:80'],
            'first_name' => ['required','string','max:100'],
            'last_name' => ['nullable','string','max:100'],
            'phone' => ['nullable','string','max:40'],
            'address_line_1' => ['required','string','max:255'],
            'address_line_2' => ['nullable','string','max:255'],
            'city' => ['required','string','max:120'],
            'region' => ['nullable','string','max:120'],
            'postal_code' => ['nullable','string','max:30'],
            'country_code' => ['required','string','size:2'],
        ]);

        $customer->addresses()->update(['is_default' => false]);
        $customer->addresses()->updateOrCreate(
            ['is_default' => true],
            $data + ['is_default' => true]
        );

        return back()->with('success', 'Delivery address saved.');
    }

    public function orders()
    {
        $orders = auth('customer')->user()->orders()->with('items')->latest('placed_at')->latest()->paginate(10);
        return view('store.orders', compact('orders'));
    }

    public function order(Order $order)
    {
        abort_unless((int) $order->customer_id === (int) auth('customer')->id(), 404);
        $order->load('items');
        return view('store.order-detail', compact('order'));
    }

    public function notifications()
    {
        $notifications = CustomerNotification::where('customer_id', auth('customer')->id())->latest()->paginate(20);
        return view('store.notifications', compact('notifications'));
    }

    public function readNotification(CustomerNotification $notification)
    {
        abort_unless((int) $notification->customer_id === (int) auth('customer')->id(), 404);
        $notification->update(['read_at' => now()]);
        return back();
    }
}

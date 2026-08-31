<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ShippingZone;
use App\Models\StoreSetting;
use App\Services\OrderPlacementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user()->load('addresses');
        $shipping = Schema::hasTable('shipping_zones')
            ? ShippingZone::where('active', true)->orderBy('base_rate')->get()
            : collect();
        $payments = Schema::hasTable('payment_methods')
            ? PaymentMethod::where('enabled', true)->whereIn('code', ['cod', 'bank_transfer'])->orderBy('sort_order')->get()
            : collect();

        $giftWrapFee = Schema::hasTable('store_settings') ? (float) (StoreSetting::where('key','gift_wrap_fee')->value('value') ?? 0) : 0.0;
        $checkoutToken = session('checkout_token') ?: (string) \Illuminate\Support\Str::uuid();
        session(['checkout_token' => $checkoutToken]);

        return view('store.checkout', compact('customer', 'shipping', 'payments', 'giftWrapFee', 'checkoutToken'));
    }

    public function store(Request $request, OrderPlacementService $orders)
    {
        $rawItems = json_decode((string) $request->input('items'), true);
        $request->merge(['decoded_items' => is_array($rawItems) ? $rawItems : []]);

        $data = $request->validate([
            'checkout_token' => ['required', 'string', 'max:100'],
            'decoded_items' => ['required', 'array', 'min:1'],
            'decoded_items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'decoded_items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'decoded_items.*.qty' => ['required', 'integer', 'min:1', 'max:25'],
            'shipping_zone_id' => ['required', 'integer', 'exists:shipping_zones,id'],
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
            'payment_reference' => ['nullable', 'string', 'max:190'],
            'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'coupon_code' => ['nullable', 'string', 'max:80'],
            'gift_wrap' => ['nullable', 'boolean'],
            'gift_message' => ['nullable', 'string', 'max:500'],
            'gift_sender_name' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['items'] = $data['decoded_items'];

        $existing = Order::where('customer_id', auth('customer')->id())->where('checkout_token', $data['checkout_token'])->first();
        if ($existing) return redirect()->route('checkout.success', $existing);

        $order = $orders->place(
            auth('customer')->user(),
            $data,
            $request->file('payment_receipt')
        );

        session()->forget('checkout_token');
        return redirect()->route('checkout.success', $order)->with('order_placed', true);
    }

    public function success(Order $order)
    {
        abort_unless((int) $order->customer_id === (int) auth('customer')->id(), 404);
        $order->load('items');
        return view('store.order-success', compact('order'));
    }
}

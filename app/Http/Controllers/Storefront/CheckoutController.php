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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user();

        if ($customer) {
            $customer->load('addresses');
        }

        $shipping = Schema::hasTable('shipping_zones')
            ? ShippingZone::where('active', true)->orderBy('base_rate')->get()
            : collect();

        $payments = Schema::hasTable('payment_methods')
            ? PaymentMethod::where('enabled', true)
                ->whereIn('code', ['cod', 'bank_transfer'])
                ->orderBy('sort_order')
                ->get()
            : collect();

        $giftWrapFee = Schema::hasTable('store_settings')
            ? (float) (StoreSetting::where('key', 'gift_wrap_fee')->value('value') ?? 0)
            : 0.0;

        $checkoutToken = session('checkout_token') ?: (string) Str::uuid();
        session(['checkout_token' => $checkoutToken]);

        return view('store.checkout', compact(
            'customer',
            'shipping',
            'payments',
            'giftWrapFee',
            'checkoutToken'
        ));
    }

    public function store(Request $request, OrderPlacementService $orders)
    {
        $customer = auth('customer')->user();

        $rawItems = json_decode((string) $request->input('items'), true);
        $request->merge(['decoded_items' => is_array($rawItems) ? $rawItems : []]);

        $rules = [
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
        ];

        if (!$customer) {
            $rules += [
                'guest_first_name' => ['required', 'string', 'max:100'],
                'guest_last_name' => ['nullable', 'string', 'max:100'],
                'guest_email' => ['required', 'email', 'max:190'],
                'guest_phone' => ['required', 'string', 'max:40'],
                'guest_address_line_1' => ['required', 'string', 'max:255'],
                'guest_address_line_2' => ['nullable', 'string', 'max:255'],
                'guest_city' => ['required', 'string', 'max:120'],
                'guest_region' => ['nullable', 'string', 'max:120'],
                'guest_postal_code' => ['nullable', 'string', 'max:30'],
            ];
        }

        $data = $request->validate($rules);
        $data['items'] = $data['decoded_items'];

        if (!$customer) {
            $data['guest'] = [
                'first_name' => $data['guest_first_name'],
                'last_name' => $data['guest_last_name'] ?? null,
                'email' => strtolower(trim($data['guest_email'])),
                'phone' => trim($data['guest_phone']),
                'address_line_1' => $data['guest_address_line_1'],
                'address_line_2' => $data['guest_address_line_2'] ?? null,
                'city' => $data['guest_city'],
                'region' => $data['guest_region'] ?? null,
                'postal_code' => $data['guest_postal_code'] ?? null,
                'country_code' => 'PK',
            ];
        }

        $existingQuery = Order::where('checkout_token', $data['checkout_token']);

        if ($customer) {
            $existingQuery->where('customer_id', $customer->id);
        } else {
            $existingQuery->whereNull('customer_id')
                ->where('customer_email', $data['guest']['email']);
        }

        if ($existing = $existingQuery->first()) {
            $this->rememberGuestOrder($existing);
            return redirect()->route('checkout.success', $existing);
        }

        $order = $orders->place(
            $customer,
            $data,
            $request->file('payment_receipt')
        );

        $this->rememberGuestOrder($order);
        session()->forget('checkout_token');

        return redirect()
            ->route('checkout.success', $order)
            ->with('order_placed', true);
    }

    public function success(Order $order)
    {
        $customerId = auth('customer')->id();
        $guestOrderIds = array_map('intval', session('guest_order_ids', []));

        $allowed = $customerId
            ? (int) $order->customer_id === (int) $customerId
            : in_array((int) $order->id, $guestOrderIds, true);

        abort_unless($allowed, 404);

        $order->load('items');

        return view('store.order-success', compact('order'));
    }

    private function rememberGuestOrder(Order $order): void
    {
        if ($order->customer_id) {
            return;
        }

        $ids = array_map('intval', session('guest_order_ids', []));
        $ids[] = (int) $order->id;

        session(['guest_order_ids' => array_values(array_unique(array_slice($ids, -10)))]);
    }
}

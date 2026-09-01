<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\CouponUsage;
use App\Models\StoreSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderPlacementService
{
    public function __construct(
        private readonly CouponService $coupons,
        private readonly CustomerNotificationService $notifications,
        private readonly TransactionalMailService $mail,
    ) {}
    public function place(Customer $customer, array $payload, ?UploadedFile $receipt = null): Order
    {
        $address = $customer->addresses()->where('is_default', true)->first() ?: $customer->addresses()->first();
        if (!$address) {
            throw ValidationException::withMessages(['address' => 'Please save a delivery address before placing your order.']);
        }

        $items = $payload['items'] ?? [];
        if (!is_array($items) || count($items) === 0) {
            throw ValidationException::withMessages(['items' => 'Your bag is empty.']);
        }

        $payment = PaymentMethod::query()
            ->where('code', $payload['payment_method'])
            ->where('enabled', true)
            ->whereIn('code', ['cod', 'bank_transfer'])
            ->first();
        if (!$payment) {
            throw ValidationException::withMessages(['payment_method' => 'The selected payment method is unavailable.']);
        }

        if ($payment->code === 'bank_transfer' && blank($payload['payment_reference'] ?? null)) {
            throw ValidationException::withMessages(['payment_reference' => 'Enter your bank transaction/reference number.']);
        }

        $receiptPath = null;
        if ($payment->code === 'bank_transfer' && $receipt) {
            $receiptPath = $receipt->storeAs(
                'payment-receipts/' . now()->format('Y/m'),
                Str::uuid() . '.' . $receipt->getClientOriginalExtension(),
                'local'
            );
        }

        try {
            $order = DB::transaction(function () use ($customer, $payload, $address, $payment, $receiptPath) {
                $shipping = ShippingZone::query()
                    ->whereKey($payload['shipping_zone_id'])
                    ->where('active', true)
                    ->lockForUpdate()
                    ->first();
                if (!$shipping) {
                    throw ValidationException::withMessages(['shipping_zone_id' => 'The selected delivery method is unavailable.']);
                }

                $prepared = [];
                $subtotal = 0.0;

                foreach ($payload['items'] as $index => $line) {
                    $qty = max(1, (int) ($line['qty'] ?? 1));
                    $productId = (int) ($line['product_id'] ?? 0);
                    if (!$productId) {
                        throw ValidationException::withMessages(["items.$index" => 'A product in your bag is no longer available from the live catalog.']);
                    }

                    $product = Product::query()->whereKey($productId)->where('status', 'active')->lockForUpdate()->first();
                    if (!$product) {
                        throw ValidationException::withMessages(["items.$index" => 'A product in your bag is no longer available.']);
                    }

                    $variant = null;
                    if (!empty($line['variant_id'])) {
                        $variant = ProductVariant::query()
                            ->whereKey((int) $line['variant_id'])
                            ->where('product_id', $product->id)
                            ->where('is_active', true)
                            ->lockForUpdate()
                            ->first();
                        if (!$variant) {
                            throw ValidationException::withMessages(["items.$index" => "{$product->name}: selected size is unavailable."]);
                        }
                    } elseif ($product->variants()->where('is_active', true)->exists()) {
                        throw ValidationException::withMessages(["items.$index" => "{$product->name}: please select a size again."]);
                    }

                    $tracked = $variant
                        ? true
                        : (bool) ($product->track_inventory ?? false);

                    $stock = $variant
                        ? max((int) ($variant->stock ?? 0), (int) ($variant->stock_quantity ?? 0))
                        : max((int) ($product->stock ?? 0), (int) ($product->stock_quantity ?? 0));

                    if ($tracked && $stock < $qty) {
                        throw ValidationException::withMessages([
                            "items.$index" => "{$product->name}: only {$stock} item(s) remain in stock."
                        ]);
                    }

                    if (!$variant && !$tracked) {
                        $identity = strtolower(trim(($product->name ?? '') . ' ' . ($product->slug ?? '')));
                        $isTester = str_contains($identity, 'tester');

                        $simpleAvailable = !$isTester
                            ? true
                            : (bool) ($product->is_in_stock ?? false);

                        if (!$simpleAvailable) {
                            throw ValidationException::withMessages([
                                "items.$index" => "{$product->name}: this product is currently out of stock."
                            ]);
                        }
                    }

                    $price = (float) ($variant?->price ?? $product->base_price ?? 0);
                    $lineTotal = round($price * $qty, 2);
                    $subtotal += $lineTotal;
                    $prepared[] = compact(
                        'product',
                        'variant',
                        'qty',
                        'price',
                        'lineTotal',
                        'stock',
                        'tracked'
                    );
                }

                $couponResult = $this->coupons->resolve($payload['coupon_code'] ?? null, $customer, $subtotal);
                $coupon = $couponResult['coupon'];
                $discountTotal = (float) $couponResult['discount'];

                $giftWrap = !empty($payload['gift_wrap']);
                $giftWrapFee = 0.0;
                if ($giftWrap) {
                    $giftWrapFee = (float) (StoreSetting::query()->where('key','gift_wrap_fee')->value('value') ?? 0);
                    $giftWrapFee = max(0, $giftWrapFee);
                }

                $shippingTotal = (float) $shipping->base_rate;
                if ($shipping->free_shipping_over !== null && $subtotal >= (float) $shipping->free_shipping_over) {
                    $shippingTotal = 0.0;
                }

                $order = Order::create([
                    'order_number' => $this->orderNumber(),
                    'checkout_token' => $payload['checkout_token'] ?? null,
                    'customer_id' => $customer->id,
                    'shipping_zone_id' => $shipping->id,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_verification_status' => $payment->code === 'bank_transfer' ? 'pending' : 'not_required',
                    'currency' => 'PKR',
                    'subtotal' => round($subtotal, 2),
                    'discount_total' => round($discountTotal, 2),
                    'shipping_total' => round($shippingTotal, 2),
                    'gift_wrap_total' => round($giftWrapFee, 2),
                    'grand_total' => round(max(0, $subtotal - $discountTotal) + $shippingTotal + $giftWrapFee, 2),
                    'coupon_code' => $coupon?->code,
                    'gift_wrap' => $giftWrap,
                    'gift_message' => $giftWrap ? ($payload['gift_message'] ?? null) : null,
                    'gift_sender_name' => $giftWrap ? ($payload['gift_sender_name'] ?? null) : null,
                    'customer_name' => $customer->full_name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $address->phone ?: $customer->phone,
                    'shipping_address' => $this->addressSnapshot($address),
                    'billing_address' => $this->addressSnapshot($address),
                    'payment_method' => $payment->code,
                    'payment_reference' => $payment->code === 'bank_transfer' ? ($payload['payment_reference'] ?? null) : null,
                    'payment_receipt_path' => $payment->code === 'bank_transfer' ? $receiptPath : null,
                    'shipping_method' => $shipping->name,
                    'notes' => $payload['notes'] ?? null,
                    'placed_at' => now(),
                ]);

                foreach ($prepared as $line) {
                    $product = $line['product'];
                    $variant = $line['variant'];
                    $qty = $line['qty'];
                    $after = $line['tracked']
                        ? ($line['stock'] - $qty)
                        : $line['stock'];

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'product_name' => $product->name . ($variant?->size_label ? ' — ' . $variant->size_label : ''),
                        'sku' => $variant?->sku ?: $product->sku,
                        'quantity' => $qty,
                        'unit_price' => $line['price'],
                        'line_total' => $line['lineTotal'],
                    ]);

                    if ($line['tracked']) {
                        if ($variant) {
                            $variant->update(['stock' => $after]);
                        } else {
                            $product->update([
                                'stock' => $after,
                                'stock_quantity' => $after,
                                'is_in_stock' => $after > 0,
                            ]);
                        }

                        InventoryAdjustment::create([
                            'product_id' => $product->id,
                            'product_variant_id' => $variant?->id,
                            'user_id' => null,
                            'quantity_change' => -$qty,
                            'quantity_after' => $after,
                            'reason' => 'order',
                            'reference' => $order->order_number,
                            'note' => 'Storefront order placed.',
                        ]);
                    }
                }

                if ($coupon && $discountTotal > 0) {
                    CouponUsage::create([
                        'coupon_id' => $coupon->id,
                        'customer_id' => $customer->id,
                        'order_id' => $order->id,
                        'discount_amount' => $discountTotal,
                    ]);
                    $coupon->increment('used_count');
                }

                $customer->update(['last_order_at' => now()]);
                $this->notifications->orderPlaced($order);
                return $order->load('items');
            }, 3);
            $this->mail->order($order, 'placed');
            return $order;
        } catch (Throwable $e) {
            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }
            throw $e;
        }
    }

    private function addressSnapshot($address): array
    {
        return [
            'first_name' => $address->first_name,
            'last_name' => $address->last_name,
            'phone' => $address->phone,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'city' => $address->city,
            'region' => $address->region,
            'postal_code' => $address->postal_code,
            'country_code' => $address->country_code,
        ];
    }

    private function orderNumber(): string
    {
        do {
            $number = 'SBA-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}

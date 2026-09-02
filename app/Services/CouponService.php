<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function resolve(?string $code, ?Customer $customer, float $subtotal): array
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return ['coupon' => null, 'discount' => 0.0];
        }

        $coupon = Coupon::query()->where('code', $code)->lockForUpdate()->first();

        if (!$coupon || !$coupon->isCurrentlyValid()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This promo code is invalid, inactive or expired.',
            ]);
        }

        if ($coupon->minimum_order !== null && $subtotal < (float) $coupon->minimum_order) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This promo code requires a minimum order of PKR '.number_format((float) $coupon->minimum_order).'.',
            ]);
        }

        if ($coupon->usage_limit_per_customer) {
            if (!$customer) {
                throw ValidationException::withMessages([
                    'coupon_code' => 'Please sign in to use this customer-limited promo code.',
                ]);
            }

            $used = $coupon->usages()->where('customer_id', $customer->id)->count();

            if ($used >= (int) $coupon->usage_limit_per_customer) {
                throw ValidationException::withMessages([
                    'coupon_code' => 'You have already used this promo code the maximum number of times.',
                ]);
            }
        }

        $discount = $coupon->type === 'percentage'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->maximum_discount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }

        $discount = round(min(max($discount, 0), $subtotal), 2);

        return ['coupon' => $coupon, 'discount' => $discount];
    }
}

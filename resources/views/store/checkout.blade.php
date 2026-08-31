@extends('layouts.store')

@section('title','Checkout — Scents by Aamir')
@section('description','Secure checkout for your Scents by Aamir order.')

@section('content')
@php
    $address = $customer->addresses->firstWhere('is_default', true) ?? $customer->addresses->first();
    $firstShipping = $shipping->first();
    $firstPayment = $payments->first();
@endphp

<div
    x-data="{
        step: 1,
        shippingId: @js((string) old('shipping_zone_id', $firstShipping?->id)),
        shippingRate: {{ (float) ($firstShipping?->base_rate ?? 0) }},
        payment: @js(old('payment_method', $firstPayment?->code ?? 'cod')),
        placing: false,
        couponCode: @js(old('coupon_code', '')),
        couponDiscount: 0,
        couponMessage: '',
        couponValid: false,
        couponBusy: false,
        giftWrap: {{ old('gift_wrap') ? 'true' : 'false' }},
        giftWrapFee: {{ (float) $giftWrapFee }},

        go(step) {
            this.step = step;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        async applyCoupon() {
            this.couponMessage = '';
            this.couponValid = false;
            this.couponDiscount = 0;

            if (!this.couponCode.trim()) return;

            this.couponBusy = true;

            try {
                const response = await fetch('/api/v1/promotions/validate', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','Accept':'application/json'},
                    body: JSON.stringify({
                        code: this.couponCode,
                        subtotal: this.$store.commerce.subtotal
                    })
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || (payload.errors ? Object.values(payload.errors).flat()[0] : 'Promo code could not be applied.'));
                }

                this.couponValid = true;
                this.couponDiscount = Number(payload.discount || 0);
                this.couponCode = payload.code;
                this.couponMessage = 'Promo code applied.';
            } catch (error) {
                this.couponMessage = error.message || 'Promo code could not be applied.';
            } finally {
                this.couponBusy = false;
            }
        },

        total() {
            return Math.max(0, this.$store.commerce.subtotal - this.couponDiscount)
                + Number(this.shippingRate)
                + (this.giftWrap ? Number(this.giftWrapFee) : 0);
        }
    }"
    x-init="$store.commerce.validateCart(); if(couponCode) applyCoupon()"
    class="min-h-screen bg-[#f7f6f2] pt-[100px] text-black"
>
    <section class="border-b border-black/10 bg-white">
        <div class="house-container py-9 sm:py-12">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="ui-label text-black/35">Secure Checkout</p>
                    <h1 class="mt-3 display-serif text-[48px] leading-[.94] sm:text-[62px]">Complete your order.</h1>
                </div>
                <a href="{{ route('cart') }}" class="text-link">← Return to bag</a>
            </div>

            <div class="mt-8 grid max-w-3xl grid-cols-3 border-t border-black/10 pt-5">
                @foreach([[1,'Details'],[2,'Delivery'],[3,'Payment']] as [$number,$label])
                    <button
                        type="button"
                        @click="step >= {{ $number }} ? go({{ $number }}) : null"
                        class="text-left"
                        :class="step === {{ $number }} ? 'text-black' : 'text-black/28'"
                    >
                        <span class="ui-label">0{{ $number }}</span>
                        <span class="mt-1 block text-xs">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="house-container grid gap-8 py-10 lg:grid-cols-[1.12fr_.88fr] lg:py-14">
        <div class="bg-white p-6 sm:p-9 lg:p-10">
            @if($errors->any())
                <div class="mb-8 border border-red-200 bg-red-50 p-5 text-sm text-red-800">
                    <p class="font-medium">Please review your checkout.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div x-show="$store.commerce.cart.length === 0" class="border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                Your shopping bag is empty. <a href="{{ route('shop') }}" class="underline">Return to fragrances.</a>
            </div>

            <div x-show="step===1">
                <p class="ui-label text-black/35">01 — Contact & Address</p>
                <h2 class="mt-3 display-serif text-[42px] leading-[.96] sm:text-[50px]">Where should we send it?</h2>
                <p class="mt-4 max-w-xl text-sm leading-7 text-black/48">Checkout uses the default delivery address saved to your private account.</p>

                @if($address)
                    <div class="mt-8 grid gap-px bg-black/10 sm:grid-cols-2">
                        <div class="bg-[#faf9f5] p-5">
                            <p class="ui-label text-black/30">Recipient</p>
                            <p class="mt-3 text-sm">{{ trim(($address->first_name ?: $customer->first_name).' '.($address->last_name ?: $customer->last_name)) }}</p>
                            <p class="mt-1 text-sm text-black/48">{{ $address->phone ?: $customer->phone }}</p>
                            <p class="mt-1 text-sm text-black/48">{{ $customer->email }}</p>
                        </div>

                        <div class="bg-[#faf9f5] p-5">
                            <p class="ui-label text-black/30">Delivery Address</p>
                            <p class="mt-3 text-sm leading-6">
                                {{ $address->address_line_1 }}
                                @if($address->address_line_2)<br>{{ $address->address_line_2 }}@endif
                                <br>{{ $address->city }}{{ $address->region ? ', '.$address->region : '' }}
                                @if($address->postal_code)<br>{{ $address->postal_code }}@endif
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button type="button" @click="go(2)" class="btn-solid" :disabled="$store.commerce.count===0">Continue to delivery</button>
                        <a href="{{ route('account') }}#delivery-address" class="btn-outline">Edit address</a>
                    </div>
                @else
                    <div class="mt-8 border border-amber-200 bg-amber-50 p-6">
                        <p class="display-serif text-[30px]">A delivery address is required.</p>
                        <p class="mt-3 text-sm leading-6 text-black/55">Save your delivery details in My Account, then return to checkout. Your bag will remain in this browser.</p>
                        <a href="{{ route('account') }}#delivery-address" class="btn-solid mt-5">Add delivery address</a>
                    </div>
                @endif
            </div>

            <div x-cloak x-show="step===2">
                <p class="ui-label text-black/35">02 — Delivery</p>
                <h2 class="mt-3 display-serif text-[42px] leading-[.96] sm:text-[50px]">Choose delivery.</h2>

                <div class="mt-8 divide-y divide-black/10 border-y border-black/10">
                    @forelse($shipping as $zone)
                        <label
                            class="grid cursor-pointer grid-cols-[auto_1fr_auto] items-center gap-4 py-6"
                            :class="shippingId === @js((string)$zone->id) ? 'bg-[#faf9f5] -mx-4 px-4' : ''"
                        >
                            <input
                                type="radio"
                                x-model="shippingId"
                                value="{{ $zone->id }}"
                                @change="shippingRate={{ (float) $zone->base_rate }}"
                            >
                            <div>
                                <p class="text-sm font-medium">{{ $zone->name }}</p>
                                <p class="mt-1 text-xs leading-5 text-black/45">
                                    {{ $zone->regions ?: 'Pakistan delivery' }}
                                    @if($zone->free_shipping_over) · Free over PKR {{ number_format((float)$zone->free_shipping_over) }} @endif
                                </p>
                            </div>
                            <span class="text-sm">{{ (float)$zone->base_rate > 0 ? 'PKR '.number_format((float)$zone->base_rate) : 'Complimentary' }}</span>
                        </label>
                    @empty
                        <div class="py-8 text-sm text-black/50">No active shipping method is configured. Enable one in Admin → Shipping.</div>
                    @endforelse
                </div>

                <div class="mt-7 flex gap-3">
                    <button type="button" @click="go(1)" class="btn-outline">Back</button>
                    <button type="button" @click="go(3)" @disabled($shipping->isEmpty()) class="btn-solid disabled:opacity-40">Continue to payment</button>
                </div>
            </div>

            <div x-cloak x-show="step===3">
                <p class="ui-label text-black/35">03 — Payment & Finishing</p>
                <h2 class="mt-3 display-serif text-[42px] leading-[.96] sm:text-[50px]">Final details.</h2>

                <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" @submit="placing=true">
                    @csrf
                    <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
                    <input type="hidden" name="items" :value="JSON.stringify($store.commerce.checkoutItems)">
                    <input type="hidden" name="shipping_zone_id" :value="shippingId">
                    <input type="hidden" name="payment_method" :value="payment">
                    <input type="hidden" name="gift_wrap" :value="giftWrap ? 1 : 0">
                    <input type="hidden" name="coupon_code" :value="couponValid ? couponCode : ''">

                    <div class="mt-8 border border-black/10 bg-[#faf9f5] p-5">
                        <p class="ui-label text-black/40">Promo Code</p>
                        <div class="mt-3 flex gap-2">
                            <input x-model="couponCode" @input="couponValid=false;couponDiscount=0;couponMessage=''" class="min-h-[48px] min-w-0 flex-1 border border-black/15 bg-white px-4 uppercase" placeholder="ENTER CODE">
                            <button type="button" @click="applyCoupon()" class="btn-outline" :disabled="couponBusy">
                                <span x-text="couponBusy ? 'Checking…' : 'Apply'"></span>
                            </button>
                        </div>
                        <p x-show="couponMessage" class="mt-2 text-xs" :class="couponValid ? 'text-emerald-700' : 'text-red-700'" x-text="couponMessage"></p>
                    </div>

                    <div class="mt-5 border border-black/10 p-5">
                        <label class="flex cursor-pointer items-start gap-4">
                            <input type="checkbox" x-model="giftWrap" class="mt-1">
                            <span>
                                <strong class="block text-sm font-medium">Signature gift presentation</strong>
                                <span class="mt-1 block text-xs leading-5 text-black/45">
                                    House gift wrapping{{ $giftWrapFee > 0 ? ' · PKR '.number_format($giftWrapFee) : ' · Complimentary' }}.
                                </span>
                            </span>
                        </label>

                        <div x-cloak x-show="giftWrap" class="mt-5 grid gap-3">
                            <input name="gift_sender_name" value="{{ old('gift_sender_name') }}" class="min-h-[48px] border border-black/15 px-4 text-sm" placeholder="From (optional)">
                            <textarea name="gift_message" rows="3" maxlength="500" class="border border-black/15 p-4 text-sm" placeholder="Private gift message">{{ old('gift_message') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3">
                        @forelse($payments as $method)
                            <label class="cursor-pointer border border-black/15 p-5 transition" :class="payment===@js($method->code) ? 'border-black bg-[#faf9f5] ring-1 ring-black' : ''">
                                <div class="flex items-start gap-4">
                                    <input type="radio" x-model="payment" value="{{ $method->code }}" class="mt-1">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium">{{ $method->name }}</p>
                                        <p class="mt-1 text-xs leading-5 text-black/45">
                                            {{ $method->code === 'cod' ? 'Pay in cash when your order is delivered.' : 'Transfer to our bank account, then provide the transaction/reference number.' }}
                                        </p>

                                        @if($method->code === 'bank_transfer' && is_array($method->config))
                                            <div class="mt-4 border-t border-black/10 pt-4 text-xs leading-6 text-black/60">
                                                @if(!empty($method->config['bank_name']))<div><b>Bank:</b> {{ $method->config['bank_name'] }}</div>@endif
                                                @if(!empty($method->config['account_title']))<div><b>Account title:</b> {{ $method->config['account_title'] }}</div>@endif
                                                @if(!empty($method->config['account_number']))<div><b>Account:</b> {{ $method->config['account_number'] }}</div>@endif
                                                @if(!empty($method->config['iban']))<div><b>IBAN:</b> {{ $method->config['iban'] }}</div>@endif
                                                @if(!empty($method->config['instructions']))<p class="mt-2">{{ $method->config['instructions'] }}</p>@endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="border border-amber-200 bg-amber-50 p-5 text-sm">No payment method is enabled. Enable COD or Bank Transfer in Admin → Payments.</div>
                        @endforelse
                    </div>

                    <div x-cloak x-show="payment==='bank_transfer'" class="mt-5 border border-black/10 bg-[#faf9f5] p-5">
                        <label class="ui-label text-black/40">Transaction / Reference Number *</label>
                        <input name="payment_reference" value="{{ old('payment_reference') }}" :required="payment==='bank_transfer'" class="mt-2 min-h-[50px] w-full border border-black/15 bg-white px-4" placeholder="e.g. TXN-123456">

                        <label class="mt-5 block ui-label text-black/40">Payment receipt — optional</label>
                        <input type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.webp,.pdf" class="mt-2 block w-full border border-black/15 bg-white p-3 text-sm">
                        <p class="mt-2 text-xs text-black/40">JPG, PNG, WebP or PDF · maximum 5 MB.</p>
                    </div>

                    <label class="mt-5 block ui-label text-black/40">Order note — optional</label>
                    <textarea name="notes" rows="3" class="mt-2 w-full border border-black/15 p-4" placeholder="Delivery note or special instruction">{{ old('notes') }}</textarea>

                    <div class="mt-7 flex gap-3">
                        <button type="button" @click="go(2)" class="btn-outline">Back</button>
                        <button
                            type="submit"
                            class="btn-solid"
                            :disabled="placing || !$store.commerce.count || !shippingId || !payment"
                            :class="{'opacity-50': placing || !$store.commerce.count || !shippingId || !payment}"
                        >
                            <span x-text="placing ? 'Placing order…' : 'Place order'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="h-fit bg-black p-6 text-white lg:sticky lg:top-[120px] sm:p-7">
            <div class="flex items-end justify-between">
                <div>
                    <p class="ui-label text-white/40">Order Summary</p>
                    <h2 class="mt-3 display-serif text-[36px]">Your bag.</h2>
                </div>
                <span class="ui-label text-white/35"><span x-text="$store.commerce.count"></span> items</span>
            </div>

            <div class="mt-6 max-h-[330px] space-y-4 overflow-y-auto border-y border-white/15 py-5">
                <template x-for="item in $store.commerce.cart.filter(item => item.available !== false)" :key="item.line_key">
                    <div class="grid grid-cols-[58px_1fr_auto] gap-3">
                        <div class="relative aspect-[4/5] overflow-hidden bg-white/8">
                            <img x-show="item.image" :src="item.image" :alt="item.name || ''" class="absolute inset-0 h-full w-full object-cover">
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-xs" x-text="item.name"></p>
                            <p class="mt-1 text-[10px] text-white/38">Qty <span x-text="item.qty"></span><span x-show="item.size"> · <span x-text="item.size"></span></span></p>
                        </div>
                        <p class="text-xs">PKR <span x-text="(Number(item.price_value || item.price || 0)*Number(item.qty || 1)).toLocaleString()"></span></p>
                    </div>
                </template>
            </div>

            <div class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-white/50">Subtotal</span><span>PKR <span x-text="$store.commerce.subtotal.toLocaleString()"></span></span></div>
                <div x-show="couponDiscount>0" class="flex justify-between text-emerald-300"><span>Promo <span x-text="couponCode"></span></span><span>− PKR <span x-text="couponDiscount.toLocaleString()"></span></span></div>
                <div class="flex justify-between"><span class="text-white/50">Shipping</span><span>PKR <span x-text="Number(shippingRate).toLocaleString()"></span></span></div>
                <div x-show="giftWrap" class="flex justify-between"><span class="text-white/50">Gift presentation</span><span>PKR <span x-text="Number(giftWrapFee).toLocaleString()"></span></span></div>
                <div class="flex justify-between border-t border-white/15 pt-4 text-base"><span>Estimated total</span><span>PKR <span x-text="total().toLocaleString()"></span></span></div>
            </div>

            <p class="mt-6 text-xs leading-5 text-white/38">Final stock, price, promotion eligibility and delivery are validated by Laravel before order creation.</p>
        </aside>
    </section>
</div>
@endsection

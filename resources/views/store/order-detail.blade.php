@extends('layouts.store')
@section('title','Order '.$order->order_number.' — Scents by Aamir')
@section('content')
@php
    $status = strtolower((string) $order->status);
    $steps = ['pending','confirmed','processing','shipped','delivered'];
    $current = array_search($status, $steps, true);
    if ($current === false) $current = in_array($status, ['cancelled','refunded'], true) ? -1 : 0;
    $address = is_array($order->shipping_address ?? null) ? $order->shipping_address : [];
@endphp

<section class="min-h-screen overflow-x-hidden bg-[#f7f6f2] pt-[100px] text-black">
    <div class="border-b border-black/10 bg-white">
        <div class="house-container py-10 sm:py-14">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="ui-label text-black/35">My Account · Order</p>
                    <h1 class="mt-3 break-all display-serif text-[44px] leading-[.94] sm:text-[62px]">{{ $order->order_number }}</h1>
                    <p class="mt-4 text-sm text-black/45">Placed {{ optional($order->placed_at ?? $order->created_at)->format('d M Y, h:i A') }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('orders') }}" class="btn-outline">All orders</a>
                    <a href="{{ route('contact') }}?order={{ urlencode($order->order_number) }}" class="btn-solid">Order support</a>
                </div>
            </div>
        </div>
    </div>

    <div class="house-container py-8 sm:py-10 lg:py-14">
        @if(in_array($status,['cancelled','refunded'],true))
            <div class="mb-8 border border-red-200 bg-red-50 p-5 text-sm text-red-800">
                This order is {{ $status }}. Contact customer care if you need assistance.
            </div>
        @else
            <section class="mb-8 overflow-hidden bg-black p-5 text-white sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0">
                        <p class="ui-label text-white/35">Order Journey</p>
                        <h2 class="mt-3 display-serif text-[34px] sm:text-[38px]">Where your order is now.</h2>
                    </div>
                    <span class="ui-label text-[#d6c19a]">{{ ucfirst(str_replace('_',' ',$status)) }}</span>
                </div>

                <div class="mt-7 grid gap-3 sm:grid-cols-5 sm:gap-2">
                    @foreach([['Order received','pending'],['Confirmed','confirmed'],['Preparing','processing'],['Dispatched','shipped'],['Delivered','delivered']] as $i=>[$label,$key])
                        <div class="grid grid-cols-[20px_1fr] items-center gap-3 sm:block">
                            <div class="h-2 w-2 rounded-full sm:h-[2px] sm:w-full sm:rounded-none {{ $current >= $i ? 'bg-[#d6c19a]' : 'bg-white/15' }}"></div>
                            <p class="text-[9px] uppercase tracking-[.12em] sm:mt-3 {{ $current >= $i ? 'text-white' : 'text-white/30' }}">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>

                @if($order->tracking_number || $order->shipping_partner)
                    <div class="mt-7 grid gap-3 border-t border-white/15 pt-5 sm:grid-cols-2">
                        @if($order->shipping_partner)
                            <div>
                                <p class="ui-label text-white/35">Shipping partner</p>
                                <p class="mt-2 text-sm">{{ $order->shipping_partner }}</p>
                            </div>
                        @endif
                        @if($order->tracking_number)
                            <div class="min-w-0">
                                <p class="ui-label text-white/35">Tracking ID</p>
                                <p class="mt-2 break-all text-sm">{{ $order->tracking_number }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        @endif

        <div class="grid gap-8 lg:grid-cols-[1.2fr_.8fr]">
            <div class="min-w-0 space-y-8">
                <section class="bg-white p-5 sm:p-8">
                    <div class="flex flex-col gap-3 border-b border-black/10 pb-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="ui-label text-black/35">Items</p>
                            <h2 class="mt-2 display-serif text-[36px] sm:text-[38px]">Your fragrances.</h2>
                        </div>
                        <span class="ui-label text-black/30">{{ $order->items->sum('quantity') }} item(s)</span>
                    </div>

                    <div class="divide-y divide-black/10">
                        @foreach($order->items as $item)
                            <div class="grid min-w-0 gap-3 py-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                                <div class="min-w-0">
                                    <p class="break-words text-sm font-medium">{{ $item->product_name }}</p>
                                    <p class="mt-1 break-words text-xs text-black/42">{{ $item->sku ?: '—' }} · Qty {{ $item->quantity }}</p>
                                </div>
                                <p class="whitespace-nowrap text-sm">{{ $order->currency }} {{ number_format((float)$item->line_total) }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if($order->gift_wrap || $order->gift_message || (float)$order->discount_total > 0)
                    <section class="bg-white p-5 sm:p-8">
                        <p class="ui-label text-black/35">Order Details</p>
                        @if((float)$order->discount_total > 0)
                            <p class="mt-4 break-words text-sm">Promotion {{ $order->coupon_code ?: 'applied' }} · − {{ $order->currency }} {{ number_format((float)$order->discount_total) }}</p>
                        @endif
                        @if($order->gift_wrap)<p class="mt-3 text-sm">Signature gift presentation</p>@endif
                        @if($order->gift_message)<blockquote class="mt-5 break-words border-l border-black/20 pl-5 text-sm italic leading-7 text-black/55">{{ $order->gift_message }}</blockquote>@endif
                    </section>
                @endif
            </div>

            <aside class="min-w-0 space-y-5">
                <section class="bg-white p-6">
                    <p class="ui-label text-black/35">Payment</p>
                    <p class="mt-3 text-lg">{{ $order->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Cash on Delivery' }}</p>
                    <p class="mt-1 text-xs text-black/42">{{ ucfirst(str_replace('_',' ', $order->payment_status ?? 'pending')) }}</p>
                    @if($order->payment_rejection_reason)<p class="mt-4 break-words border border-red-200 bg-red-50 p-3 text-xs text-red-700">{{ $order->payment_rejection_reason }}</p>@endif
                </section>

                @if($address)
                    <section class="bg-white p-6">
                        <p class="ui-label text-black/35">Delivery</p>
                        <p class="mt-3 break-words text-sm leading-6">
                            {{ $address['first_name'] ?? '' }} {{ $address['last_name'] ?? '' }}<br>
                            {{ $address['address_line_1'] ?? '' }}
                            @if(!empty($address['address_line_2']))<br>{{ $address['address_line_2'] }}@endif
                            <br>{{ $address['city'] ?? '' }}@if(!empty($address['region'])), {{ $address['region'] }}@endif
                        </p>
                    </section>
                @endif

                <section class="bg-black p-6 text-white">
                    <p class="ui-label text-white/35">Order Total</p>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><span class="text-white/45">Subtotal</span><span class="whitespace-nowrap">{{ $order->currency }} {{ number_format((float)$order->subtotal) }}</span></div>
                        @if((float)$order->discount_total>0)<div class="flex justify-between gap-4"><span class="text-white/45">Discount</span><span class="whitespace-nowrap">− {{ $order->currency }} {{ number_format((float)$order->discount_total) }}</span></div>@endif
                        <div class="flex justify-between gap-4"><span class="text-white/45">Shipping</span><span class="whitespace-nowrap">{{ $order->currency }} {{ number_format((float)$order->shipping_total) }}</span></div>
                        <div class="flex justify-between gap-4 border-t border-white/15 pt-4 text-base"><span>Total</span><span class="whitespace-nowrap">{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</span></div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</section>
@endsection

@extends('layouts.store')
@section('title','Track My Order — Scents by Aamir')
@section('content')
<section class="min-h-[78vh] overflow-x-hidden bg-[#f7f6f2] pt-[108px] text-black">
    <div class="house-container grid gap-10 py-12 lg:grid-cols-[.85fr_1.15fr] lg:py-24">
        <div class="min-w-0">
            <p class="ui-label text-black/35">Customer Care</p>
            <h1 class="mt-4 break-words display-serif text-[52px] leading-[.9] sm:text-8xl">Track My Order</h1>
            <p class="mt-6 max-w-xl text-sm leading-7 text-black/50">
                Enter your order number or courier tracking ID, then add the email or phone used at checkout.
            </p>
        </div>

        <div class="min-w-0 bg-white p-5 sm:p-8 lg:p-10">
            <form method="POST" action="{{ route('track-order.store') }}" class="grid gap-4">
                @csrf
                <label>
                    <span class="ui-label text-black/35">Order number / Tracking ID</span>
                    <input name="order_number" value="{{ old('order_number') }}" required class="mt-2 min-h-[54px] w-full min-w-0 border border-black/15 px-4 text-sm" placeholder="SBA-... or courier tracking ID">
                </label>

                <label>
                    <span class="ui-label text-black/35">Email or phone</span>
                    <input name="identity" value="{{ old('identity') }}" required class="mt-2 min-h-[54px] w-full min-w-0 border border-black/15 px-4 text-sm" placeholder="Email / phone used at checkout">
                </label>

                @if($errors->any())
                    <p class="break-words text-sm text-red-700">{{ $errors->first() }}</p>
                @endif

                <button class="btn-solid mt-2">Check status</button>
            </form>

            @if(isset($searched))
                <div class="mt-8 min-w-0 border-t border-black/10 pt-7">
                    @if($order)
                        <p class="break-all ui-label text-black/35">{{ $order->order_number }}</p>
                        <h2 class="mt-3 display-serif text-4xl">{{ ucfirst(str_replace('_',' ', $order->status)) }}</h2>

                        <div class="mt-6 grid min-w-0 gap-3 text-sm text-black/55">
                            <p>Payment: <span class="text-black">{{ ucfirst(str_replace('_',' ', $order->payment_status)) }}</span></p>
                            <p>Placed: <span class="text-black">{{ optional($order->placed_at ?? $order->created_at)->format('d M Y, h:i A') }}</span></p>
                            @if($order->shipping_partner)<p>Shipping partner: <span class="text-black">{{ $order->shipping_partner }}</span></p>@endif
                            @if($order->shipping_method)<p class="break-words">Shipping: <span class="text-black">{{ $order->shipping_method }}</span></p>@endif
                            @if($order->tracking_number)<p class="break-all">Tracking ID: <span class="text-black">{{ $order->tracking_number }}</span></p>@endif
                            <p>Total: <span class="text-black">PKR {{ number_format((float)$order->grand_total,0) }}</span></p>
                        </div>
                    @else
                        <h2 class="display-serif text-4xl">Order not found.</h2>
                        <p class="mt-3 break-words text-sm text-black/50">Check the order/tracking ID and email or phone, then try again.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

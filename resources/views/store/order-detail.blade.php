@extends('layouts.store')
@section('title','Order '.$order->order_number.' — Scents by Aamir')
@section('content')
@php
$status=strtolower((string)$order->status);
$steps=['pending','confirmed','processing','shipped','completed'];
$current=array_search($status,$steps,true); if($current===false)$current=in_array($status,['cancelled','refunded'],true)?-1:1;
$address=is_array($order->shipping_address??null)?$order->shipping_address:[];
@endphp
<section class="min-h-screen bg-[#f7f6f2] pt-[100px] text-black">
<div class="border-b border-black/10 bg-white"><div class="house-container py-10 sm:py-14"><div class="flex flex-wrap items-end justify-between gap-6">
<div><p class="ui-label text-black/35">My Account · Order</p><h1 class="mt-3 display-serif text-[48px] leading-[.94] sm:text-[62px]">{{ $order->order_number }}</h1><p class="mt-4 text-sm text-black/45">Placed {{ optional($order->placed_at??$order->created_at)->format('d M Y, h:i A') }}</p></div>
<div class="flex gap-3"><a href="{{ route('orders') }}" class="btn-outline">All orders</a><a href="{{ route('contact') }}?order={{ urlencode($order->order_number) }}" class="btn-solid">Order support</a></div>
</div></div></div>
<div class="house-container py-10 lg:py-14">
@if(in_array($status,['cancelled','refunded'],true))
<div class="mb-8 border border-red-200 bg-red-50 p-5 text-sm text-red-800">This order is {{ $status }}. Contact customer care if you need assistance.</div>
@else
<section class="mb-8 bg-black p-6 text-white sm:p-8"><div class="flex flex-wrap items-end justify-between gap-5"><div><p class="ui-label text-white/35">Order Journey</p><h2 class="mt-3 display-serif text-[38px]">Where your order is now.</h2></div><span class="ui-label text-[#d6c19a]">{{ ucfirst(str_replace('_',' ',$status)) }}</span></div>
<div class="mt-8 grid grid-cols-5 gap-2">@foreach([['Order received','pending'],['Confirmed','confirmed'],['Preparing','processing'],['Dispatched','shipped'],['Delivered','completed']] as $i=>[$label,$key])<div><div class="h-[2px] {{ $current >= $i ? 'bg-[#d6c19a]' : 'bg-white/15' }}"></div><p class="mt-3 text-[9px] uppercase tracking-[.12em] {{ $current >= $i ? 'text-white' : 'text-white/30' }}">{{ $label }}</p></div>@endforeach</div></section>
@endif
<div class="grid gap-8 lg:grid-cols-[1.2fr_.8fr]">
<div class="space-y-8"><section class="bg-white p-6 sm:p-8"><div class="flex items-end justify-between border-b border-black/10 pb-5"><div><p class="ui-label text-black/35">Items</p><h2 class="mt-2 display-serif text-[38px]">Your fragrances.</h2></div><span class="ui-label text-black/30">{{ $order->items->sum('quantity') }} item(s)</span></div>
<div class="divide-y divide-black/10">@foreach($order->items as $item)<div class="grid gap-3 py-5 sm:grid-cols-[1fr_auto] sm:items-center"><div><p class="text-sm font-medium">{{ $item->product_name }}</p><p class="mt-1 text-xs text-black/42">{{ $item->sku }} · Qty {{ $item->quantity }}</p></div><p class="text-sm">{{ $order->currency }} {{ number_format((float)$item->line_total) }}</p></div>@endforeach</div></section>
@if($order->gift_wrap||$order->gift_message||(float)$order->discount_total>0)<section class="bg-white p-6 sm:p-8"><p class="ui-label text-black/35">Order Details</p>@if((float)$order->discount_total>0)<p class="mt-4 text-sm">Promotion {{ $order->coupon_code?:'applied' }} · − {{ $order->currency }} {{ number_format((float)$order->discount_total) }}</p>@endif @if($order->gift_wrap)<p class="mt-3 text-sm">Signature gift presentation</p>@endif @if($order->gift_message)<blockquote class="mt-5 border-l border-black/20 pl-5 text-sm italic leading-7 text-black/55">{{ $order->gift_message }}</blockquote>@endif</section>@endif</div>
<aside class="space-y-5"><section class="bg-white p-6"><p class="ui-label text-black/35">Payment</p><p class="mt-3 text-lg">{{ $order->payment_method==='bank_transfer'?'Bank Transfer':'Cash on Delivery' }}</p><p class="mt-1 text-xs text-black/42">{{ ucfirst(str_replace('_',' ',$order->payment_status)) }}</p>@if($order->payment_rejection_reason)<p class="mt-4 border border-red-200 bg-red-50 p-3 text-xs text-red-700">{{ $order->payment_rejection_reason }}</p>@endif</section>
@if($address)<section class="bg-white p-6"><p class="ui-label text-black/35">Delivery</p><p class="mt-3 text-sm leading-6">{{ $address['first_name']??'' }} {{ $address['last_name']??'' }}<br>{{ $address['address_line_1']??'' }}@if(!empty($address['address_line_2']))<br>{{ $address['address_line_2'] }}@endif<br>{{ $address['city']??'' }}@if(!empty($address['region'])), {{ $address['region'] }}@endif</p></section>@endif
<section class="bg-black p-6 text-white"><p class="ui-label text-white/35">Order Total</p><div class="mt-5 space-y-3 text-sm"><div class="flex justify-between"><span class="text-white/45">Subtotal</span><span>{{ $order->currency }} {{ number_format((float)$order->subtotal) }}</span></div>@if((float)$order->discount_total>0)<div class="flex justify-between"><span class="text-white/45">Discount</span><span>− {{ $order->currency }} {{ number_format((float)$order->discount_total) }}</span></div>@endif<div class="flex justify-between"><span class="text-white/45">Shipping</span><span>{{ $order->currency }} {{ number_format((float)$order->shipping_total) }}</span></div><div class="flex justify-between border-t border-white/15 pt-4 text-base"><span>Total</span><span>{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</span></div></div></section></aside>
</div></div></section>
@endsection

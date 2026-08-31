@extends('layouts.store')
@section('title','Order received — Scents by Aamir')
@section('content')
<script>
document.addEventListener('alpine:initialized', () => {
    const store = window.Alpine?.store('commerce');
    if (store) {
        store.cart = [];
        store.persist();
        store.cartOpen = false;
    }
});
localStorage.removeItem('sba_cart');
</script>
<section x-data x-init="$store.commerce.cart=[]; $store.commerce.persist(); $store.commerce.cartOpen=false" class="min-h-screen bg-[#f7f6f2] pt-[100px] text-black">
  <div class="house-container py-16 lg:py-24">
    <div class="mx-auto max-w-3xl bg-white p-8 sm:p-12">
      <p class="ui-label text-black/35">Order received</p>
      <h1 class="mt-4 display-serif text-5xl sm:text-7xl">Thank you.</h1>
      <p class="mt-5 max-w-xl text-sm leading-6 text-black/55">Your order <strong>{{ $order->order_number }}</strong> has been saved and is now visible to our team.</p>

      @if($order->payment_method === 'bank_transfer')
        <div class="mt-8 border border-amber-200 bg-amber-50 p-5 text-sm leading-6">
          <strong>Bank payment verification pending.</strong><br>
          Reference: {{ $order->payment_reference ?: '—' }}. Our admin team will verify your transfer before confirming the order.
        </div>
      @else
        <div class="mt-8 border border-black/10 p-5 text-sm leading-6"><strong>Cash on Delivery selected.</strong> Payment will be collected when the order is delivered.</div>
      @endif

      <div class="mt-8 divide-y divide-black/10 border-y border-black/10">
        @foreach($order->items as $item)
          <div class="flex justify-between gap-5 py-4 text-sm"><span>{{ $item->product_name }} × {{ $item->quantity }}</span><span>{{ $order->currency }} {{ number_format((float)$item->line_total) }}</span></div>
        @endforeach
      </div>
      <div class="mt-5 space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-black/50">Subtotal</span><span>PKR {{ number_format((float)$order->subtotal) }}</span></div>
        @if((float)$order->discount_total>0)<div class="flex justify-between text-emerald-700"><span>Promo{{ $order->coupon_code ? ' · '.$order->coupon_code : '' }}</span><span>− PKR {{ number_format((float)$order->discount_total) }}</span></div>@endif
        <div class="flex justify-between"><span class="text-black/50">Delivery</span><span>PKR {{ number_format((float)$order->shipping_total) }}</span></div>
        @if($order->gift_wrap)<div class="flex justify-between"><span class="text-black/50">Gift presentation</span><span>PKR {{ number_format((float)$order->gift_wrap_total) }}</span></div>@endif
        <div class="flex justify-between border-t border-black/10 pt-3 text-base"><span>Total</span><span>PKR {{ number_format((float)$order->grand_total) }}</span></div>
      </div>
      @if($order->gift_wrap)<div class="mt-6 border border-black/10 bg-[#f7f6f2] p-5 text-sm"><strong>Gift presentation included.</strong>@if($order->gift_message)<p class="mt-2 whitespace-pre-line text-black/55">{{ $order->gift_message }}</p>@endif</div>@endif
      <div class="mt-9 flex flex-wrap gap-3"><a href="{{ route('orders') }}" class="btn-solid">View my orders</a><a href="{{ route('shop') }}" class="btn-outline">Continue shopping</a></div>
    </div>
  </div>
</section>
@endsection

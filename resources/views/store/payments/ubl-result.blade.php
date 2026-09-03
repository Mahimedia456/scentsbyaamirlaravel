@extends('layouts.store')
@section('title', $transaction->status === 'paid' ? 'Payment confirmed — Scents by Aamir' : 'Payment status — Scents by Aamir')
@section('content')
@if($transaction->status === 'paid')
<script>
document.addEventListener('alpine:initialized', () => {
    const store = window.Alpine?.store('commerce');
    if (store) { store.cart = []; store.persist(); store.cartOpen = false; }
});
localStorage.removeItem('sba_cart');
</script>
@endif
<section class="min-h-screen bg-[#f7f6f2] pt-[100px] text-black">
  <div class="house-container py-16 lg:py-24">
    <div class="mx-auto max-w-3xl bg-white p-8 sm:p-12">
      @if($transaction->status === 'paid')
        <p class="ui-label text-emerald-700">Payment confirmed</p>
        <h1 class="mt-4 display-serif text-5xl sm:text-7xl">Thank you.</h1>
        <p class="mt-5 max-w-xl text-sm leading-7 text-black/55">UBL confirmed your payment for order <strong>{{ $transaction->order->order_number }}</strong>. Your order is now confirmed for fulfilment.</p>
        <div class="mt-8 border border-emerald-200 bg-emerald-50 p-5 text-sm leading-6">
            <strong>Card payment successful.</strong><br>
            @if($transaction->card_brand){{ $transaction->card_brand }} · @endif{{ $transaction->masked_card_number ?: 'Secure card payment' }}<br>
            Reference: {{ $transaction->gateway_transaction_id }}
        </div>
      @else
        <p class="ui-label text-red-700">Payment not completed</p>
        <h1 class="mt-4 display-serif text-5xl sm:text-7xl">Try again.</h1>
        <p class="mt-5 max-w-xl text-sm leading-7 text-black/55">Your order <strong>{{ $transaction->order->order_number }}</strong> remains unpaid. No successful UBL finalization has been recorded.</p>
        <div class="mt-8 border border-red-200 bg-red-50 p-5 text-sm leading-6 text-red-900">
            <strong>{{ $transaction->response_description ?: 'Payment could not be completed.' }}</strong>
            @if($transaction->last_error)<div class="mt-1 text-xs">{{ $transaction->last_error }}</div>@endif
            @if($transaction->response_code)<div class="mt-1 text-xs">Gateway code: {{ $transaction->response_code }}</div>@endif
        </div>
      @endif

      <div class="mt-8 border-y border-black/10 py-5 text-sm">
        <div class="flex justify-between gap-4"><span class="text-black/50">Order</span><span>{{ $transaction->order->order_number }}</span></div>
        <div class="mt-2 flex justify-between gap-4"><span class="text-black/50">Total</span><strong>{{ $transaction->currency }} {{ number_format((float)$transaction->amount, 2) }}</strong></div>
      </div>

      <div class="mt-8 flex flex-wrap gap-3">
        @if($transaction->status !== 'paid')
          <form method="POST" action="{{ route('payments.ubl.retry', ['token'=>$transaction->public_token]) }}">@csrf<button class="btn-solid">Try card payment again</button></form>
        @else
          @if(auth('customer')->check())<a href="{{ route('orders') }}" class="btn-solid">View my orders</a>@endif
        @endif
        <a href="{{ route('shop') }}" class="btn-outline">Continue shopping</a>
      </div>
    </div>
  </div>
</section>
@endsection

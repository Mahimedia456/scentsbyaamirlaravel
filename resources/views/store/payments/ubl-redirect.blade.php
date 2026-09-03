@extends('layouts.store')
@section('title','Secure card payment — Scents by Aamir')
@section('description','Redirecting to UBL secure card payment.')
@section('content')
<section class="min-h-screen bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="mx-auto max-w-2xl bg-white p-8 text-center sm:p-12">
            <p class="ui-label text-black/35">Secure Payment</p>
            <h1 class="mt-4 display-serif text-[46px] leading-[.95] sm:text-[62px]">Taking you to UBL.</h1>
            <p class="mx-auto mt-5 max-w-lg text-sm leading-7 text-black/55">Your order <strong>{{ $transaction->order->order_number }}</strong> is reserved. Card details are entered on the hosted payment page, not on Scents by Aamir.</p>
            <div class="mx-auto mt-8 max-w-sm border-y border-black/10 py-5 text-sm">
                <div class="flex justify-between gap-4"><span class="text-black/45">Amount</span><strong>{{ $transaction->currency }} {{ number_format((float)$transaction->amount, 2) }}</strong></div>
                <div class="mt-2 flex justify-between gap-4"><span class="text-black/45">Attempt</span><span>#{{ $transaction->attempt }}</span></div>
            </div>

            <form id="ubl-payment-form" method="POST" action="{{ $transaction->payment_portal_url }}" class="mt-8">
                <input type="hidden" name="TransactionID" value="{{ $transaction->gateway_transaction_id }}">
                <button type="submit" class="btn-solid">Continue to secure payment</button>
            </form>
            <p class="mt-4 text-xs leading-5 text-black/40">If you are not redirected automatically, use the button above.</p>
        </div>
    </div>
</section>
<script>
window.addEventListener('load', () => {
    window.setTimeout(() => document.getElementById('ubl-payment-form')?.submit(), 450);
});
</script>
@endsection

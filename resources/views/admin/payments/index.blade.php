@extends('admin.layouts.app')
@section('title','Payments') @section('header','Payment Methods')
@section('content')
@if(session('success'))<div class="mb-5 border border-emerald-200 bg-emerald-50 p-4 text-sm">{{ session('success') }}</div>@endif

<div class="space-y-4">
@foreach($methods as $m)
<form method="POST" action="{{ route('admin.payments.update',$m) }}" class="border border-black/10 bg-white p-5">
@csrf @method('PUT')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div><b>{{ $m->name }}</b><p class="text-xs text-black/45">{{ $m->code }} @if($m->code==='ubl_card') · {{ strtoupper($ubl['mode']) }}@endif</p></div>
    <label class="text-sm"><input type="checkbox" name="enabled" value="1" @checked($m->enabled)> Enabled at checkout</label>
</div>

@if($m->code==='ubl_card')
<div class="mt-5 grid gap-3 border-t border-black/10 pt-5 md:grid-cols-2">
    <div class="border border-black/10 bg-[#faf9f5] p-4 text-xs leading-6"><b>Environment</b><br>{{ strtoupper($ubl['mode']) }}<br><span class="text-black/45">{{ $ubl['base_url'] }}</span></div>
    <div class="border border-black/10 bg-[#faf9f5] p-4 text-xs leading-6"><b>Merchant / Currency</b><br>{{ $ubl['customer'] }} · {{ $ubl['currency'] }}<br><span class="text-black/45">Callback: {{ $ubl['public_url'] }}</span></div>
    <textarea name="customer_note" rows="3" placeholder="Checkout note" class="border border-black/15 p-3 md:col-span-2">{{ old('customer_note',$m->config['customer_note'] ?? 'Secure Visa / Mastercard payment via UBL hosted checkout.') }}</textarea>
    <p class="text-xs leading-5 text-amber-800 md:col-span-2">Gateway credentials are intentionally read from server .env, never stored in Admin or database. Do not switch UBL_MODE to production until UBL issues your live merchant credentials.</p>
</div>
@endif

@if($m->code==='bank_transfer')
<div class="mt-5 grid gap-3 border-t border-black/10 pt-5 md:grid-cols-2">
<input name="bank_name" value="{{ old('bank_name',$m->config['bank_name'] ?? '') }}" placeholder="Bank name" class="border border-black/15 p-3">
<input name="account_title" value="{{ old('account_title',$m->config['account_title'] ?? '') }}" placeholder="Account title" class="border border-black/15 p-3">
<input name="account_number" value="{{ old('account_number',$m->config['account_number'] ?? '') }}" placeholder="Account number" class="border border-black/15 p-3">
<input name="iban" value="{{ old('iban',$m->config['iban'] ?? '') }}" placeholder="IBAN" class="border border-black/15 p-3">
<textarea name="instructions" rows="3" placeholder="Customer payment instructions" class="border border-black/15 p-3 md:col-span-2">{{ old('instructions',$m->config['instructions'] ?? '') }}</textarea>
</div>
@endif
<button class="mt-5 bg-black px-5 py-3 text-xs uppercase tracking-[.14em] text-white">Save payment method</button>
</form>
@endforeach
</div>

@if($transactions->isNotEmpty())
<section class="mt-6 border border-black/10 bg-white">
    <div class="border-b border-black/10 p-5"><b>Recent UBL transactions</b><p class="mt-1 text-xs text-black/45">Last 20 gateway attempts.</p></div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-xs">
            <thead class="border-b border-black/10 bg-[#faf9f5]"><tr><th class="p-3">Order</th><th class="p-3">Attempt</th><th class="p-3">Status</th><th class="p-3">Amount</th><th class="p-3">Gateway ref</th><th class="p-3">Response</th><th class="p-3">Created</th></tr></thead>
            <tbody class="divide-y divide-black/10">
            @foreach($transactions as $tx)
                <tr><td class="p-3">{{ $tx->order?->order_number ?: '#'.$tx->order_id }}</td><td class="p-3">#{{ $tx->attempt }}</td><td class="p-3">{{ ucfirst($tx->status) }}</td><td class="p-3">{{ $tx->currency }} {{ number_format((float)$tx->amount,2) }}</td><td class="p-3">{{ $tx->gateway_transaction_id ?: '—' }}</td><td class="p-3">{{ $tx->response_code ?: '—' }} {{ $tx->response_description ? '· '.$tx->response_description : '' }}</td><td class="p-3">{{ $tx->created_at?->format('d M Y H:i') }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
@endsection

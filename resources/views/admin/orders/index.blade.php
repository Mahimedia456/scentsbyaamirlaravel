@extends('admin.layouts.app')
@section('title','Orders')
@section('header','Orders')
@section('eyebrow','Commerce operations')

@section('content')
<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px">
    <div><h2 style="margin:0;font-size:27px;letter-spacing:-.035em">Order operations</h2><p class="admin-muted" style="margin:7px 0 0;font-size:12px">Fulfilment, payments, customer communication and delivery workflow.</p></div>
</div>

<div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:14px">
@foreach([['All',$summary['all']],['Open',$summary['open']],['Shipped',$summary['shipped']],['Delivered',$summary['delivered']],['Payment pending',$summary['payment_pending']]] as [$label,$value])
<div class="admin-card" style="padding:15px"><div class="admin-eyebrow">{{ $label }}</div><div style="margin-top:7px;font-size:21px;font-weight:720">{{ number_format($value) }}</div></div>
@endforeach
</div>

<section class="admin-card">
<form method="GET" style="display:grid;grid-template-columns:minmax(210px,1.4fr) repeat(5,minmax(125px,.6fr)) auto;gap:8px;padding:14px;border-bottom:1px solid #e4e7ec">
<input class="admin-field" name="q" value="{{ request('q') }}" placeholder="Order, customer, email, tracking">
<select class="admin-field" name="status"><option value="">All statuses</option>@foreach(['pending','confirmed','processing','shipped','delivered','cancelled','refunded'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
<select class="admin-field" name="payment_status"><option value="">All payments</option>@foreach(['pending','paid','failed','refunded'] as $s)<option value="{{ $s }}" @selected(request('payment_status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
<select class="admin-field" name="payment_method"><option value="">All methods</option><option value="cod" @selected(request('payment_method')==='cod')>Cash on delivery</option><option value="bank_transfer" @selected(request('payment_method')==='bank_transfer')>Bank transfer</option></select>
<input class="admin-field" type="date" name="date_from" value="{{ request('date_from') }}">
<input class="admin-field" type="date" name="date_to" value="{{ request('date_to') }}">
<button class="admin-btn">Filter</button>
</form>

<form method="POST" action="{{ route('admin.orders.bulk') }}">@csrf
<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:12px 14px;border-bottom:1px solid #e4e7ec;background:#fafbfc">
<select class="admin-field" name="action" style="width:220px" required><option value="">Bulk workflow…</option><option value="confirm">Confirm orders</option><option value="processing">Move to processing</option><option value="shipped">Mark shipped</option><option value="delivered">Mark delivered</option><option value="cancelled">Cancel orders</option></select>
<button class="admin-btn" data-admin-confirm="Apply this status change to selected orders? Customer emails will be processed.">Apply</button>
<span class="admin-muted" style="font-size:10px">Bulk status actions also process customer notifications.</span>
</div>
<div class="admin-table-wrap"><table class="admin-table">
<thead><tr><th style="width:34px"><input type="checkbox" data-order-all></th><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th>Items</th><th>Placed</th><th style="text-align:right">Total</th></tr></thead>
<tbody>
@forelse($orders as $order)
<tr>
<td><input type="checkbox" name="ids[]" value="{{ $order->id }}" data-order-one></td>
<td><a href="{{ route('admin.orders.show',$order) }}" style="font-weight:700">{{ $order->order_number }}</a>@if($order->tracking_number)<div class="admin-muted" style="font-size:9px;margin-top:4px">Track: {{ $order->tracking_number }}</div>@endif</td>
<td>{{ $order->customer_name ?: optional($order->customer)->full_name }}<div class="admin-muted" style="font-size:9px;margin-top:4px">{{ $order->customer_email }}</div></td>
<td><span class="admin-status {{ in_array($order->status,['delivered','confirmed']) ? 'success' : (in_array($order->status,['cancelled','refunded']) ? 'warning' : '') }}">{{ ucfirst($order->status) }}</span></td>
<td><span class="admin-status {{ $order->payment_status==='paid' ? 'success' : ($order->payment_status==='failed' ? 'warning' : '') }}">{{ ucfirst($order->payment_status) }}</span><div class="admin-muted" style="font-size:9px;margin-top:4px">{{ str_replace('_',' ',ucfirst($order->payment_method ?: '—')) }}</div></td>
<td>{{ $order->items_count }}</td>
<td>{{ optional($order->placed_at)->format('d M Y H:i') ?: $order->created_at->format('d M Y') }}</td>
<td style="text-align:right;font-weight:700">{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</td>
</tr>
@empty<tr><td colspan="8" style="padding:48px;text-align:center" class="admin-muted">No orders match the current filters.</td></tr>@endforelse
</tbody></table></div>
</form>
</section>
@if($orders->hasPages())<div style="margin-top:18px">{{ $orders->links() }}</div>@endif
<script>document.addEventListener('DOMContentLoaded',()=>{const a=document.querySelector('[data-order-all]'),b=[...document.querySelectorAll('[data-order-one]')];a?.addEventListener('change',()=>b.forEach(x=>x.checked=a.checked));});</script>
<style>@media(max-width:1100px){.admin-page>div:nth-child(2){grid-template-columns:repeat(2,minmax(0,1fr))!important}.admin-card>form:first-child{grid-template-columns:1fr 1fr!important}}@media(max-width:650px){.admin-page>div:nth-child(2){grid-template-columns:1fr!important}.admin-card>form:first-child{grid-template-columns:1fr!important}}</style>
@endsection

@extends('admin.layouts.app')
@section('title',$customer->full_name)
@section('header',$customer->full_name)
@section('eyebrow','CRM / customer profile')
@section('content')
<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px">
<div><a href="{{ route('admin.customers.index') }}" class="admin-muted" style="font-size:10px">← Back to customers</a><h2 style="margin:7px 0 0;font-size:27px;letter-spacing:-.035em">{{ $customer->full_name }}</h2><div style="display:flex;gap:7px;margin-top:10px">@if($customer->admin_archived_at)<span class="admin-status">Archived</span>@else<span class="admin-status {{ $customer->is_active?'success':'warning' }}">{{ $customer->is_active?'Active':'Inactive' }}</span>@endif<span class="admin-status {{ $customer->email_verified_at?'success':'warning' }}">{{ $customer->email_verified_at?'Email verified':'Email unverified' }}</span></div></div>
<a href="{{ route('admin.customers.edit',$customer) }}" class="admin-btn admin-btn-primary">Edit customer</a>
</div>

<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px">
<div class="admin-card" style="padding:16px"><div class="admin-eyebrow">Orders</div><div style="font-size:22px;font-weight:720;margin-top:7px">{{ number_format($metrics['orders']) }}</div></div>
<div class="admin-card" style="padding:16px"><div class="admin-eyebrow">Lifetime order value</div><div style="font-size:22px;font-weight:720;margin-top:7px">PKR {{ number_format($metrics['spent']) }}</div></div>
<div class="admin-card" style="padding:16px"><div class="admin-eyebrow">Last order</div><div style="font-size:14px;font-weight:700;margin-top:9px">{{ $metrics['last_order'] ? (optional($metrics['last_order']->placed_at)->format('d M Y') ?: $metrics['last_order']->created_at->format('d M Y')) : 'No orders' }}</div></div>
</div>

<div style="display:grid;grid-template-columns:330px minmax(0,1fr);gap:14px;align-items:start">
<div style="display:grid;gap:14px">
<section class="admin-card" style="padding:20px"><div class="admin-eyebrow">Customer</div><div style="font-size:15px;font-weight:700;margin-top:13px">{{ $customer->full_name }}</div><div class="admin-muted" style="font-size:11px;margin-top:6px">{{ $customer->email ?: 'No email' }}</div><div class="admin-muted" style="font-size:11px;margin-top:4px">{{ $customer->phone ?: 'No phone' }}</div>@if($customer->company)<div class="admin-muted" style="font-size:11px;margin-top:4px">{{ $customer->company }}</div>@endif<div class="admin-muted" style="font-size:10px;margin-top:12px">Marketing: {{ $customer->marketing_opt_in?'Opted in':'Not opted in' }}</div></section>

<section class="admin-card" style="padding:20px"><div class="admin-eyebrow">Account actions</div><div style="display:grid;gap:8px;margin-top:14px">
@if(!$customer->email_verified_at && $customer->email)<form method="POST" action="{{ route('admin.customers.resend-activation',$customer) }}">@csrf<button class="admin-btn" style="width:100%">Resend activation email</button></form>@endif
@if($customer->admin_archived_at)<form method="POST" action="{{ route('admin.customers.restore',$customer) }}">@csrf<button class="admin-btn admin-btn-primary" style="width:100%">Restore customer</button></form>
@elseif($customer->is_active)<form method="POST" action="{{ route('admin.customers.deactivate',$customer) }}">@csrf<button class="admin-btn" style="width:100%" data-admin-confirm="Deactivate this customer account?">Deactivate account</button></form>
@else<form method="POST" action="{{ route('admin.customers.activate',$customer) }}">@csrf<button class="admin-btn admin-btn-primary" style="width:100%">Activate account</button></form>@endif
@if(!$customer->admin_archived_at)<form method="POST" action="{{ route('admin.customers.destroy',$customer) }}">@csrf @method('DELETE')<button class="admin-btn" style="width:100%;color:#9d2018" data-admin-confirm="Archive this customer? Historical orders will be preserved.">Archive customer</button></form>@endif
</div></section>

@if($customer->addresses->isNotEmpty())<section class="admin-card" style="padding:20px"><div class="admin-eyebrow">Addresses</div>@foreach($customer->addresses as $address)<div style="margin-top:13px;padding-top:13px;border-top:1px solid #eceef1;font-size:10px;line-height:1.6"><strong>{{ $address->label }}{{ $address->is_default?' · Default':'' }}</strong><br><span class="admin-muted">{{ $address->address_line_1 }}@if($address->address_line_2), {{ $address->address_line_2 }}@endif<br>{{ collect([$address->city,$address->region,$address->postal_code])->filter()->implode(', ') }}</span></div>@endforeach</section>@endif
@if($customer->notes)<section class="admin-card" style="padding:20px"><div class="admin-eyebrow">Internal notes</div><div class="admin-muted" style="font-size:11px;line-height:1.65;margin-top:12px">{{ $customer->notes }}</div></section>@endif
</div>

<section class="admin-card"><div class="admin-card-header"><div><div class="admin-eyebrow">Order timeline</div><div style="font-size:14px;font-weight:700;margin-top:4px">Recent customer orders</div></div></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Payment</th><th style="text-align:right">Total</th></tr></thead><tbody>
@forelse($customer->orders as $order)<tr><td><a href="{{ route('admin.orders.show',$order) }}" style="font-weight:700">{{ $order->order_number }}</a></td><td>{{ optional($order->placed_at)->format('d M Y H:i') ?: $order->created_at->format('d M Y') }}</td><td><span class="admin-status">{{ ucfirst($order->status) }}</span></td><td>{{ ucfirst($order->payment_status) }}</td><td style="text-align:right;font-weight:700">{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</td></tr>@empty<tr><td colspan="5" class="admin-muted" style="padding:44px;text-align:center">No orders yet.</td></tr>@endforelse
</tbody></table></div></section>
</div>
<style>@media(max-width:900px){.admin-page>div:nth-child(4){grid-template-columns:1fr!important}}@media(max-width:650px){.admin-page>div:nth-child(3){grid-template-columns:1fr!important}}</style>
@endsection

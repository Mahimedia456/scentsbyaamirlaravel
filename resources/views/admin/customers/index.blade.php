@extends('admin.layouts.app')
@section('title','Customers')
@section('header','Customers')
@section('eyebrow','CRM / customer operations')
@section('content')
<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px"><div><h2 style="margin:0;font-size:27px;letter-spacing:-.035em">Customer directory</h2><p class="admin-muted" style="margin:7px 0 0;font-size:12px">Accounts, verification, lifecycle, marketing consent and order value.</p></div><a class="admin-btn admin-btn-primary" href="{{ route('admin.customers.create') }}">+ Create customer</a></div>
<div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:14px">
@foreach([['All',$summary['all']],['Active',$summary['active']],['Inactive',$summary['inactive']],['Unverified',$summary['unverified']],['Archived',$summary['archived']]] as [$label,$value])<div class="admin-card" style="padding:15px"><div class="admin-eyebrow">{{ $label }}</div><div style="margin-top:7px;font-size:21px;font-weight:720">{{ number_format($value) }}</div></div>@endforeach
</div>
<section class="admin-card">
<form method="GET" style="display:grid;grid-template-columns:minmax(230px,1.4fr) repeat(3,minmax(140px,.6fr)) auto;gap:8px;padding:14px;border-bottom:1px solid #e4e7ec">
<input class="admin-field" name="q" value="{{ request('q') }}" placeholder="Name, email or phone">
<select class="admin-field" name="status"><option value="">All account states</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option><option value="archived" @selected(request('status')==='archived')>Archived</option></select>
<select class="admin-field" name="verification"><option value="">All verification</option><option value="verified" @selected(request('verification')==='verified')>Verified</option><option value="unverified" @selected(request('verification')==='unverified')>Unverified</option></select>
<select class="admin-field" name="marketing"><option value="">All marketing</option><option value="yes" @selected(request('marketing')==='yes')>Opted in</option><option value="no" @selected(request('marketing')==='no')>Not opted in</option></select>
<button class="admin-btn">Filter</button>
</form>
<form method="POST" action="{{ route('admin.customers.bulk') }}">@csrf
<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:12px 14px;border-bottom:1px solid #e4e7ec;background:#fafbfc">
<select class="admin-field" name="action" style="width:220px" required><option value="">Bulk action…</option><option value="activate">Activate</option><option value="deactivate">Deactivate</option><option value="archive">Archive</option><option value="marketing_on">Marketing opt-in</option><option value="marketing_off">Marketing opt-out</option></select>
<button class="admin-btn" data-admin-confirm="Apply this customer bulk action?">Apply</button>
</div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th style="width:34px"><input type="checkbox" data-customer-all></th><th>Customer</th><th>Contact</th><th>Verification</th><th>Orders</th><th>Lifetime value</th><th>Marketing</th><th>Status</th></tr></thead><tbody>
@forelse($customers as $customer)
<tr>
<td><input type="checkbox" name="ids[]" value="{{ $customer->id }}" data-customer-one></td>
<td><a href="{{ route('admin.customers.show',$customer) }}" style="font-weight:700">{{ $customer->full_name }}</a>@if($customer->company)<div class="admin-muted" style="font-size:9px;margin-top:4px">{{ $customer->company }}</div>@endif</td>
<td>{{ $customer->email ?: '—' }}<div class="admin-muted" style="font-size:9px;margin-top:4px">{{ $customer->phone ?: '—' }}</div></td>
<td><span class="admin-status {{ $customer->email_verified_at ? 'success' : 'warning' }}">{{ $customer->email_verified_at ? 'Verified' : 'Unverified' }}</span></td>
<td>{{ $customer->orders_count }}</td><td style="font-weight:700">PKR {{ number_format((float)($customer->orders_sum_grand_total ?? 0)) }}</td>
<td>{{ $customer->marketing_opt_in ? 'Opted in' : 'No' }}</td>
<td>@if($customer->admin_archived_at)<span class="admin-status">Archived</span>@else<span class="admin-status {{ $customer->is_active ? 'success':'warning' }}">{{ $customer->is_active?'Active':'Inactive' }}</span>@endif</td>
</tr>
@empty<tr><td colspan="8" style="padding:48px;text-align:center" class="admin-muted">No customers match the current filters.</td></tr>@endforelse
</tbody></table></div></form></section>
@if($customers->hasPages())<div style="margin-top:18px">{{ $customers->links() }}</div>@endif
<script>document.addEventListener('DOMContentLoaded',()=>{const a=document.querySelector('[data-customer-all]'),b=[...document.querySelectorAll('[data-customer-one]')];a?.addEventListener('change',()=>b.forEach(x=>x.checked=a.checked));});</script>
<style>@media(max-width:1100px){.admin-page>div:nth-child(2){grid-template-columns:repeat(2,minmax(0,1fr))!important}.admin-card>form:first-child{grid-template-columns:1fr 1fr!important}}@media(max-width:650px){.admin-page>div:nth-child(2){grid-template-columns:1fr!important}.admin-card>form:first-child{grid-template-columns:1fr!important}}</style>
@endsection

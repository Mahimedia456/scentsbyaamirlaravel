@extends('admin.layouts.app')
@section('title',$customer->exists?'Edit Customer':'Create Customer')
@section('header',$customer->exists?'Edit Customer':'Create Customer')
@section('eyebrow','CRM / customer account')
@section('content')
<form method="POST" action="{{ $customer->exists ? route('admin.customers.update',$customer) : route('admin.customers.store') }}">@csrf @if($customer->exists)@method('PUT')@endif
<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:18px"><div><a href="{{ $customer->exists ? route('admin.customers.show',$customer) : route('admin.customers.index') }}" class="admin-muted" style="font-size:10px">← Back</a><h2 style="font-size:27px;margin:7px 0 0">{{ $customer->exists ? $customer->full_name : 'New customer' }}</h2></div><button class="admin-btn admin-btn-primary">{{ $customer->exists?'Save changes':'Create customer' }}</button></div>
<div style="display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:14px;align-items:start">
<section class="admin-card" style="padding:20px"><div class="admin-eyebrow">Profile</div><div style="display:grid;grid-template-columns:1fr 1fr;gap:13px;margin-top:16px">
<label style="font-size:11px;font-weight:680">First name<input class="admin-field" style="margin-top:7px" name="first_name" required value="{{ old('first_name',$customer->first_name) }}"></label>
<label style="font-size:11px;font-weight:680">Last name<input class="admin-field" style="margin-top:7px" name="last_name" value="{{ old('last_name',$customer->last_name) }}"></label>
<label style="font-size:11px;font-weight:680">Email<input class="admin-field" style="margin-top:7px" type="email" name="email" value="{{ old('email',$customer->email) }}"></label>
<label style="font-size:11px;font-weight:680">Phone<input class="admin-field" style="margin-top:7px" name="phone" value="{{ old('phone',$customer->phone) }}"></label>
<label style="font-size:11px;font-weight:680;grid-column:1/-1">Company<input class="admin-field" style="margin-top:7px" name="company" value="{{ old('company',$customer->company) }}"></label>
<label style="font-size:11px;font-weight:680;grid-column:1/-1">Internal notes<textarea class="admin-field" style="margin-top:7px;padding-top:10px;min-height:150px" name="notes">{{ old('notes',$customer->notes) }}</textarea></label>
</div></section>
<aside style="display:grid;gap:14px;position:sticky;top:96px"><section class="admin-card" style="padding:20px"><div class="admin-eyebrow">Account controls</div><div style="display:grid;gap:12px;margin-top:15px">
<label style="display:flex;gap:8px;font-size:11px"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$customer->exists?$customer->is_active:true))><span><strong>Active account</strong><br><span class="admin-muted" style="font-size:9px">Inactive accounts cannot use normal customer access.</span></span></label>
<label style="display:flex;gap:8px;font-size:11px"><input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in',$customer->marketing_opt_in))><span><strong>Marketing opt-in</strong><br><span class="admin-muted" style="font-size:9px">Only enable with appropriate customer consent.</span></span></label>
@if(!$customer->exists)<label style="display:flex;gap:8px;font-size:11px"><input type="checkbox" name="send_activation" value="1"><span><strong>Send activation email</strong><br><span class="admin-muted" style="font-size:9px">Requires a valid email and configured SMTP.</span></span></label>@endif
</div></section></aside>
</div></form>
<style>@media(max-width:850px){form>div:nth-child(2){grid-template-columns:1fr!important}form aside{position:static!important}}@media(max-width:600px){.admin-card>div[style*="grid-template-columns"]{grid-template-columns:1fr!important}}</style>
@endsection

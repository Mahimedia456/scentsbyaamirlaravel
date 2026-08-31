@extends('admin.layouts.app')
@section('title','Create Order')
@section('header','Create Order')
@section('eyebrow','Commerce / manual order')

@section('content')
@php($rows=old('items',[['product_id'=>'','qty'=>1],['product_id'=>'','qty'=>1],['product_id'=>'','qty'=>1],['product_id'=>'','qty'=>1],['product_id'=>'','qty'=>1]]))
<form method="POST" action="{{ route('admin.orders.store') }}">
@csrf
<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:18px">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="admin-muted" style="font-size:10px">← Back to orders</a>
        <h2 style="font-size:27px;margin:7px 0 0">Manual / assisted order</h2>
        <p class="admin-muted" style="font-size:11px;margin:6px 0 0">Create an order for an existing customer. Tracked stock is deducted automatically.</p>
    </div>
    <button class="admin-btn admin-btn-primary">Create order</button>
</div>

@if($errors->any())<div class="admin-alert admin-alert-danger" style="margin-bottom:14px">{{ $errors->first() }}</div>@endif

<div style="display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:14px;align-items:start">
    <div style="display:grid;gap:14px">
        <section class="admin-card" style="padding:20px">
            <div class="admin-eyebrow">Customer</div>
            <select class="admin-field" style="margin-top:14px" name="customer_id" required>
                <option value="">Select customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((string)old('customer_id')===(string)$customer->id)>
                        {{ $customer->full_name }} · {{ $customer->email ?: $customer->phone }}
                    </option>
                @endforeach
            </select>
            <p class="admin-muted" style="font-size:9px;margin:8px 0 0">The customer's default saved address is copied into the order.</p>
        </section>

        <section class="admin-card">
            <div class="admin-card-header"><div><div class="admin-eyebrow">Items</div><div style="font-size:14px;font-weight:700;margin-top:4px">Add products</div></div></div>
            <div style="padding:14px 20px;display:grid;gap:9px">
                @foreach($rows as $i=>$row)
                    <div style="display:grid;grid-template-columns:minmax(0,1fr) 110px;gap:8px">
                        <select class="admin-field" name="items[{{ $i }}][product_id]">
                            <option value="">Unused row</option>
                            @foreach($products as $product)
                                @php($available=$product->track_inventory ? max((int)$product->stock,(int)$product->stock_quantity)>0 : (bool)$product->is_in_stock)
                                <option value="{{ $product->id }}" @selected((string)($row['product_id']??'')===(string)$product->id) @disabled(!$available)>
                                    {{ $product->name }} · {{ $product->size_label ?: '50 ML' }} · PKR {{ number_format((float)$product->base_price) }}{{ !$available?' · OUT':'' }}
                                </option>
                            @endforeach
                        </select>
                        <input class="admin-field" type="number" min="1" max="99" name="items[{{ $i }}][qty]" value="{{ $row['qty']??1 }}" placeholder="Qty">
                    </div>
                @endforeach
                <p class="admin-muted" style="font-size:9px;margin:4px 0 0">Leave unused rows blank.</p>
            </div>
        </section>
    </div>

    <aside style="display:grid;gap:14px;position:sticky;top:96px">
        <section class="admin-card" style="padding:20px">
            <div class="admin-eyebrow">Order setup</div>
            <div style="display:grid;gap:10px;margin-top:14px">
                <label style="font-size:10px">Order status
                    <select class="admin-field" style="margin-top:6px" name="status">
                        @foreach(['pending','confirmed','processing'] as $s)<option value="{{ $s }}" @selected(old('status','pending')===$s)>{{ ucfirst($s) }}</option>@endforeach
                    </select>
                </label>
                <label style="font-size:10px">Payment status
                    <select class="admin-field" style="margin-top:6px" name="payment_status">
                        @foreach(['pending','paid'] as $s)<option value="{{ $s }}" @selected(old('payment_status','pending')===$s)>{{ ucfirst($s) }}</option>@endforeach
                    </select>
                </label>
                <label style="font-size:10px">Payment method
                    <select class="admin-field" style="margin-top:6px" name="payment_method">
                        <option value="cod" @selected(old('payment_method')==='cod')>Cash on delivery</option>
                        <option value="bank_transfer" @selected(old('payment_method')==='bank_transfer')>Bank transfer</option>
                        <option value="manual" @selected(old('payment_method')==='manual')>Manual / other</option>
                    </select>
                </label>
                <input class="admin-field" name="shipping_method" value="{{ old('shipping_method','Admin order') }}" placeholder="Shipping method">
                <input class="admin-field" type="number" step=".01" min="0" name="shipping_total" value="{{ old('shipping_total',0) }}" placeholder="Shipping PKR">
                <input class="admin-field" type="number" step=".01" min="0" name="discount_total" value="{{ old('discount_total',0) }}" placeholder="Manual discount PKR">
                <textarea class="admin-field" style="padding-top:10px;min-height:80px" name="notes" placeholder="Customer/order note">{{ old('notes') }}</textarea>
                <textarea class="admin-field" style="padding-top:10px;min-height:80px" name="admin_notes" placeholder="Private admin note">{{ old('admin_notes') }}</textarea>
            </div>
        </section>
    </aside>
</div>
</form>
<style>@media(max-width:850px){.admin-page form>div:last-of-type{grid-template-columns:1fr!important}.admin-page aside{position:static!important}}</style>
@endsection

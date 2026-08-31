@extends('admin.layouts.app')
@section('title',$order->order_number)
@section('header','Order '.$order->order_number)
@section('eyebrow','Commerce / orders')

@section('content')
@php
    $shipping = is_array($order->shipping_address) ? $order->shipping_address : [];
    $address1 = $shipping['address_line_1'] ?? $shipping['address_1'] ?? null;
    $address2 = $shipping['address_line_2'] ?? $shipping['address_2'] ?? null;
    $region = $shipping['region'] ?? $shipping['state'] ?? null;
    $postal = $shipping['postal_code'] ?? $shipping['postcode'] ?? null;
@endphp

@if(session('success'))
    <div class="admin-alert admin-alert-success" style="margin-bottom:14px">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="admin-alert admin-alert-danger" style="margin-bottom:14px">{{ $errors->first() }}</div>
@endif

<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="admin-muted" style="font-size:10px">← Back to orders</a>
        <h2 style="margin:7px 0 0;font-size:27px;letter-spacing:-.035em">{{ $order->order_number }}</h2>
        <div style="display:flex;gap:7px;flex-wrap:wrap;margin-top:10px">
            <span class="admin-status">{{ ucfirst($order->status) }}</span>
            <span class="admin-status {{ $order->payment_status==='paid' ? 'success' : ($order->payment_status==='failed' ? 'warning' : '') }}">
                Payment {{ ucfirst($order->payment_status) }}
            </span>
        </div>
    </div>
    <div style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap">
        <a href="{{ route('admin.orders.invoice',$order) }}" target="_blank" class="admin-btn">Invoice / Print PDF</a>
        <div style="text-align:right">
            <div class="admin-muted" style="font-size:10px">Order total</div>
            <div style="margin-top:5px;font-size:24px;font-weight:720">{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1.35fr) minmax(330px,.65fr);gap:14px;align-items:start">
    <div style="display:grid;gap:14px">
        <section class="admin-card">
            <div class="admin-card-header">
                <div>
                    <div class="admin-eyebrow">Order items</div>
                    <div style="margin-top:4px;font-size:14px;font-weight:700">{{ $order->items->sum('quantity') }} unit(s)</div>
                </div>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th style="text-align:right">Qty</th>
                        <th style="text-align:right">Unit</th>
                        <th style="text-align:right">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td style="font-weight:650">{{ $item->product_name }}</td>
                            <td>{{ $item->sku ?: '—' }}</td>
                            <td style="text-align:right">{{ $item->quantity }}</td>
                            <td style="text-align:right">{{ $order->currency }} {{ number_format((float)$item->unit_price) }}</td>
                            <td style="text-align:right">{{ $order->currency }} {{ number_format((float)$item->line_total) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding:16px 20px;border-top:1px solid #e4e7ec;display:grid;gap:8px;font-size:11px">
                <div style="display:flex;justify-content:space-between">
                    <span class="admin-muted">Subtotal</span>
                    <span>{{ $order->currency }} {{ number_format((float)$order->subtotal) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between">
                    <span class="admin-muted">Discount</span>
                    <span>- {{ $order->currency }} {{ number_format((float)$order->discount_total) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between">
                    <span class="admin-muted">Shipping</span>
                    <span>{{ $order->currency }} {{ number_format((float)$order->shipping_total) }}</span>
                </div>
                @if($order->gift_wrap)
                    <div style="display:flex;justify-content:space-between">
                        <span class="admin-muted">Gift presentation</span>
                        <span>{{ $order->currency }} {{ number_format((float)$order->gift_wrap_total) }}</span>
                    </div>
                @endif
                <div style="display:flex;justify-content:space-between;padding-top:9px;border-top:1px solid #eceef1;font-size:13px;font-weight:700">
                    <span>Total</span>
                    <span>{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</span>
                </div>
            </div>
        </section>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Customer</div>
                <div style="margin-top:14px;font-size:13px;font-weight:700">{{ $order->customer_name ?: 'Guest' }}</div>
                <div class="admin-muted" style="margin-top:5px;font-size:11px">{{ $order->customer_email ?: '—' }}</div>
                <div class="admin-muted" style="margin-top:3px;font-size:11px">{{ $order->customer_phone ?: '—' }}</div>
            </section>

            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Shipping address</div>
                <div class="admin-muted" style="margin-top:14px;font-size:11px;line-height:1.65">
                    @if($shipping)
                        <strong>{{ trim(($shipping['first_name'] ?? '').' '.($shipping['last_name'] ?? '')) }}</strong><br>
                        @if($address1){{ $address1 }}<br>@endif
                        @if($address2){{ $address2 }}<br>@endif
                        {{ collect([$shipping['city'] ?? null,$region,$postal])->filter()->implode(', ') }}
                        @if(!empty($shipping['phone']))<br>{{ $shipping['phone'] }}@endif
                    @else
                        No shipping address snapshot.
                    @endif
                </div>
            </section>
        </div>

        <section class="admin-card" style="padding:20px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap">
                <div>
                    <div class="admin-eyebrow">Payment</div>
                    <div style="margin-top:12px;font-size:12px;font-weight:680">{{ str_replace('_',' ',ucfirst($order->payment_method ?: '—')) }}</div>
                    <div class="admin-muted" style="margin-top:5px;font-size:10px">Status: {{ ucfirst($order->payment_status) }}</div>
                    @if($order->payment_reference)
                        <div class="admin-muted" style="margin-top:3px;font-size:10px">Reference: {{ $order->payment_reference }}</div>
                    @endif
                    @if($order->payment_receipt_path)
                        <a href="{{ route('admin.orders.payment.receipt',$order) }}" class="admin-btn" style="margin-top:12px">Download receipt</a>
                    @endif
                </div>

                @if($order->payment_method==='bank_transfer' && ($order->payment_verification_status ?? 'pending')==='pending')
                    <div style="width:min(100%,340px);display:grid;gap:9px">
                        <form method="POST" action="{{ route('admin.orders.payment.approve',$order) }}">
                            @csrf
                            <button class="admin-btn admin-btn-primary" style="width:100%">Approve bank payment</button>
                        </form>

                        <form method="POST" action="{{ route('admin.orders.payment.reject',$order) }}">
                            @csrf
                            <textarea class="admin-field" style="padding-top:10px;min-height:76px" name="reason" required placeholder="Reason for rejection"></textarea>
                            <button class="admin-btn" style="width:100%;margin-top:8px;color:#9d2018">Reject payment</button>
                        </form>
                    </div>
                @endif
            </div>

            @if($order->payment_rejection_reason)
                <div class="admin-alert admin-alert-danger" style="margin:16px 0 0">Rejected: {{ $order->payment_rejection_reason }}</div>
            @endif
        </section>

        @if($order->coupon_code || $order->gift_wrap || $order->gift_message)
            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Promotion & gifting</div>
                @if($order->coupon_code)
                    <p style="font-size:11px">
                        Coupon <strong>{{ $order->coupon_code }}</strong>
                        · discount {{ $order->currency }} {{ number_format((float)$order->discount_total) }}
                    </p>
                @endif

                @if($order->gift_wrap)
                    <p style="font-size:11px">
                        Gift presentation enabled
                        @if($order->gift_sender_name)
                            · From {{ $order->gift_sender_name }}
                        @endif
                    </p>
                @endif

                @if($order->gift_message)
                    <div class="admin-muted" style="border-left:2px solid #d7dce3;padding-left:12px;font-size:11px;line-height:1.6">
                        {{ $order->gift_message }}
                    </div>
                @endif
            </section>
        @endif
    </div>

    <aside class="admin-card" style="padding:20px;position:sticky;top:96px">
        <div class="admin-eyebrow">Fulfilment workflow</div>
        <p class="admin-muted" style="font-size:10px;line-height:1.6">Status changes remain active and send matching customer transactional emails when SMTP is configured.</p>

        <form method="POST" action="{{ route('admin.orders.update',$order) }}" style="display:grid;gap:13px;margin-top:17px">
            @csrf
            @method('PUT')

            <label style="font-size:11px;font-weight:680">Order status
                <select class="admin-field" style="margin-top:7px" name="status">
                    @foreach(['pending','confirmed','processing','shipped','delivered','cancelled','refunded'] as $status)
                        <option value="{{ $status }}" @selected(old('status',$order->status)===$status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </label>

            <label style="font-size:11px;font-weight:680">Payment status
                <select class="admin-field" style="margin-top:7px" name="payment_status">
                    @foreach(['pending','paid','failed','refunded'] as $paymentStatus)
                        <option value="{{ $paymentStatus }}" @selected(old('payment_status',$order->payment_status)===$paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                    @endforeach
                </select>
            </label>

            <label style="font-size:11px;font-weight:680">Payment method
                <input class="admin-field" style="margin-top:7px" name="payment_method" value="{{ old('payment_method',$order->payment_method) }}">
            </label>

            <label style="font-size:11px;font-weight:680">Shipping method
                <input class="admin-field" style="margin-top:7px" name="shipping_method" value="{{ old('shipping_method',$order->shipping_method) }}">
            </label>

            <label style="font-size:11px;font-weight:680">Tracking number
                <input class="admin-field" style="margin-top:7px" name="tracking_number" value="{{ old('tracking_number',$order->tracking_number) }}">
            </label>

            <label style="font-size:11px;font-weight:680">Internal notes
                <textarea class="admin-field" style="margin-top:7px;padding-top:10px;min-height:110px" name="admin_notes">{{ old('admin_notes',$order->admin_notes) }}</textarea>
            </label>

            <button class="admin-btn admin-btn-primary">Save order & notify</button>
        </form>
    </aside>
</div>

<style>
@media(max-width:1000px){
    .admin-page>div[style*="grid-template-columns:minmax(0,1.35fr)"]{grid-template-columns:1fr!important}
    .admin-page aside{position:static!important}
}
@media(max-width:680px){
    .admin-page div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}
}
</style>
@endsection

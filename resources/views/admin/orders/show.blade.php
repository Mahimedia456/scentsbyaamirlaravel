@extends('admin.layouts.app')
@section('title',$order->order_number)
@section('header','Order '.$order->order_number)
@section('eyebrow','Commerce / orders')

@section('content')
<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="admin-muted" style="font-size:10px">← Back to orders</a>
        <h2 style="margin:7px 0 0;font-size:27px;letter-spacing:-.035em">{{ $order->order_number }}</h2>
        <div style="display:flex;gap:7px;flex-wrap:wrap;margin-top:10px">
            <span class="admin-status">{{ ucfirst($order->status) }}</span>
            <span class="admin-status {{ $order->payment_status==='paid' ? 'success' : ($order->payment_status==='failed' ? 'warning' : '') }}">Payment {{ ucfirst($order->payment_status) }}</span>
        </div>
    </div>
    <div style="text-align:right">
        <div class="admin-muted" style="font-size:10px">Order total</div>
        <div style="margin-top:5px;font-size:24px;font-weight:720">{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1.35fr) minmax(330px,.65fr);gap:14px;align-items:start">
    <div style="display:grid;gap:14px">
        <section class="admin-card">
            <div class="admin-card-header"><div><div class="admin-eyebrow">Order items</div><div style="margin-top:4px;font-size:14px;font-weight:700">{{ $order->items->sum('quantity') }} unit(s)</div></div></div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Product</th><th>SKU</th><th style="text-align:right">Qty</th><th style="text-align:right">Total</th></tr></thead>
                    <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td style="font-weight:650">{{ $item->product_name }}</td>
                            <td>{{ $item->sku ?: '—' }}</td>
                            <td style="text-align:right">{{ $item->quantity }}</td>
                            <td style="text-align:right">{{ $order->currency }} {{ number_format((float)$item->line_total) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px;border-top:1px solid #e4e7ec;display:grid;gap:8px;font-size:11px">
                @foreach([
                    ['Subtotal',$order->subtotal],
                    ['Discount',-$order->discount_total],
                    ['Shipping',$order->shipping_total],
                    ['Gift presentation',$order->gift_wrap ? $order->gift_wrap_total : null],
                ] as [$label,$amount])
                    @if($amount !== null)
                        <div style="display:flex;justify-content:space-between"><span class="admin-muted">{{ $label }}</span><span>{{ $order->currency }} {{ number_format((float)$amount) }}</span></div>
                    @endif
                @endforeach
                <div style="display:flex;justify-content:space-between;padding-top:9px;border-top:1px solid #eceef1;font-size:13px;font-weight:700"><span>Total</span><span>{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</span></div>
            </div>
        </section>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Customer</div>
                <div style="margin-top:14px;font-size:13px;font-weight:700">{{ $order->customer_name ?: 'Guest' }}</div>
                <div class="admin-muted" style="margin-top:5px;font-size:11px">{{ $order->customer_email }}</div>
                <div class="admin-muted" style="margin-top:3px;font-size:11px">{{ $order->customer_phone }}</div>
            </section>
            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Shipping address</div>
                <div class="admin-muted" style="margin-top:14px;font-size:11px;line-height:1.65">
                    @if(is_array($order->shipping_address))
                        {{ trim(($order->shipping_address['first_name'] ?? '').' '.($order->shipping_address['last_name'] ?? '')) }}<br>
                        {{ $order->shipping_address['address_line_1'] ?? '' }}<br>
                        @if(!empty($order->shipping_address['address_line_2'])){{ $order->shipping_address['address_line_2'] }}<br>@endif
                        {{ collect([$order->shipping_address['city'] ?? null,$order->shipping_address['region'] ?? null,$order->shipping_address['postal_code'] ?? null])->filter()->implode(', ') }}
                    @else
                        {{ $order->shipping_address }}
                    @endif
                </div>
            </section>
        </div>

        <section class="admin-card" style="padding:20px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap">
                <div>
                    <div class="admin-eyebrow">Payment</div>
                    <div style="margin-top:12px;font-size:12px;font-weight:680">{{ $order->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Cash on Delivery' }}</div>
                    <div class="admin-muted" style="margin-top:5px;font-size:10px">Status: {{ ucfirst($order->payment_status) }}</div>
                    @if($order->payment_reference)<div class="admin-muted" style="margin-top:3px;font-size:10px">Reference: {{ $order->payment_reference }}</div>@endif
                    @if($order->payment_receipt_path)<a href="{{ route('admin.orders.payment.receipt',$order) }}" class="admin-btn" style="margin-top:12px">Download receipt</a>@endif
                </div>

                @if($order->payment_method==='bank_transfer' && ($order->payment_verification_status ?? 'pending')==='pending')
                    <div style="width:min(100%,340px);display:grid;gap:9px">
                        <form method="POST" action="{{ route('admin.orders.payment.approve',$order) }}">@csrf<button class="admin-btn admin-btn-primary" style="width:100%">Approve bank payment</button></form>
                        <form method="POST" action="{{ route('admin.orders.payment.reject',$order) }}">@csrf
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

        @if($order->coupon_code || $order->gift_wrap)
            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Promotion & gifting</div>
                @if($order->coupon_code)<p style="font-size:11px">Coupon <strong>{{ $order->coupon_code }}</strong> · discount {{ $order->currency }} {{ number_format((float)$order->discount_total) }}</p>@endif
                @if($order->gift_wrap)<p style="font-size:11px">Gift presentation enabled@if($order->gift_sender_name) · From {{ $order->gift_sender_name }}@endif</p>@endif
                @if($order->gift_message)<div class="admin-muted" style="border-left:2px solid #d7dce3;padding-left:12px;font-size:11px;line-height:1.6">{{ $order->gift_message }}</div>@endif
            </section>
        @endif
    </div>

    <aside class="admin-card" style="padding:20px;position:sticky;top:96px">
        <div class="admin-eyebrow">Fulfilment workflow</div>
        <p class="admin-muted" style="font-size:10px;line-height:1.6">Status changes send the matching customer transactional email when SMTP is configured.</p>

        <form method="POST" action="{{ route('admin.orders.update',$order) }}" style="display:grid;gap:13px;margin-top:17px">
            @csrf @method('PUT')
            <label style="font-size:11px;font-weight:680">Order status
                <select class="admin-field" style="margin-top:7px" name="status">
                    @foreach(['pending','confirmed','processing','shipped','delivered','cancelled','refunded'] as $s)
                        <option value="{{ $s }}" @selected(old('status',$order->status)===$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </label>
            <label style="font-size:11px;font-weight:680">Payment status
                <select class="admin-field" style="margin-top:7px" name="payment_status">
                    @foreach(['pending','paid','failed','refunded'] as $s)
                        <option value="{{ $s }}" @selected(old('payment_status',$order->payment_status)===$s)>{{ ucfirst($s) }}</option>
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
@media(max-width:1000px){.admin-page>div:nth-child(2){grid-template-columns:1fr!important}.admin-page aside{position:static!important}}
@media(max-width:680px){.admin-page div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}}
</style>
@endsection

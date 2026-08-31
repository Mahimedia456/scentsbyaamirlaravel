<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Invoice {{ $order->order_number }} — Scents by Aamir</title>
<style>
*{box-sizing:border-box} body{margin:0;background:#eef0f2;color:#111;font-family:Arial,Helvetica,sans-serif}
.page{width:210mm;min-height:297mm;margin:20px auto;background:#fff;padding:18mm 16mm;box-shadow:0 8px 35px rgba(0,0,0,.08)}
.top{display:flex;justify-content:space-between;gap:24px;border-bottom:2px solid #111;padding-bottom:22px}
.brand{font-size:23px;font-weight:800;letter-spacing:.02em}.muted{color:#666;font-size:11px;line-height:1.65}.title{text-align:right}.title h1{margin:0;font-size:30px}.title p{margin:7px 0 0}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:26px 0}.label{font-size:9px;text-transform:uppercase;letter-spacing:.16em;color:#777;margin-bottom:8px}
table{width:100%;border-collapse:collapse;font-size:11px}th{text-align:left;border-bottom:1px solid #111;padding:10px 7px;font-size:9px;text-transform:uppercase;letter-spacing:.1em}td{padding:12px 7px;border-bottom:1px solid #e8e8e8}.right{text-align:right}
.totals{width:45%;margin-left:auto;margin-top:18px}.totals div{display:flex;justify-content:space-between;padding:6px 0;font-size:11px}.totals .grand{border-top:1px solid #111;margin-top:7px;padding-top:12px;font-size:15px;font-weight:700}
.footer{margin-top:42px;padding-top:18px;border-top:1px solid #ddd;font-size:9px;color:#777;line-height:1.7}
.actions{position:fixed;right:22px;top:22px;display:flex;gap:8px}.btn{border:1px solid #111;background:#fff;padding:10px 14px;text-decoration:none;color:#111;font-size:11px;cursor:pointer}.btn.dark{background:#111;color:#fff}
@media print{body{background:#fff}.page{margin:0;box-shadow:none;width:auto;min-height:auto}.actions{display:none}@page{size:A4;margin:0}}
</style>
</head>
<body>
<div class="actions">
    <a class="btn" href="{{ route('admin.orders.show',$order) }}">Back</a>
    <button class="btn dark" onclick="window.print()">Print / Save PDF</button>
</div>

@php
    $a=is_array($order->shipping_address)?$order->shipping_address:[];
    $address1=$a['address_line_1']??$a['address_1']??null;
    $address2=$a['address_line_2']??$a['address_2']??null;
    $region=$a['region']??$a['state']??null;
    $postal=$a['postal_code']??$a['postcode']??null;
@endphp

<main class="page">
    <header class="top">
        <div>
            <div class="brand">SCENTS BY AAMIR</div>
            <div class="muted" style="margin-top:8px">Luxury fragrance · Pakistan<br>{{ config('mail.from.address','orders@scentsbyaamir.com') }}</div>
        </div>
        <div class="title">
            <h1>INVOICE</h1>
            <p class="muted"><strong>{{ $order->order_number }}</strong><br>Issued {{ ($order->placed_at?:$order->created_at)->format('d M Y') }}</p>
        </div>
    </header>

    <section class="grid">
        <div>
            <div class="label">Bill to</div>
            <strong style="font-size:12px">{{ $order->customer_name ?: 'Customer' }}</strong>
            <div class="muted" style="margin-top:5px">{{ $order->customer_email }}<br>{{ $order->customer_phone }}</div>
        </div>
        <div>
            <div class="label">Ship to</div>
            <strong style="font-size:12px">{{ trim(($a['first_name']??'').' '.($a['last_name']??'')) ?: $order->customer_name }}</strong>
            <div class="muted" style="margin-top:5px">
                @if($address1){{ $address1 }}<br>@endif
                @if($address2){{ $address2 }}<br>@endif
                {{ collect([$a['city']??null,$region,$postal])->filter()->implode(', ') }}
            </div>
        </div>
    </section>

    <table>
        <thead>
        <tr><th>Item</th><th>SKU</th><th class="right">Qty</th><th class="right">Unit price</th><th class="right">Amount</th></tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td><strong>{{ $item->product_name }}</strong></td>
                <td>{{ $item->sku ?: '—' }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">{{ $order->currency }} {{ number_format((float)$item->unit_price,2) }}</td>
                <td class="right">{{ $order->currency }} {{ number_format((float)$item->line_total,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>Subtotal</span><span>{{ $order->currency }} {{ number_format((float)$order->subtotal,2) }}</span></div>
        @if((float)$order->discount_total>0)<div><span>Discount</span><span>- {{ $order->currency }} {{ number_format((float)$order->discount_total,2) }}</span></div>@endif
        <div><span>Shipping</span><span>{{ $order->currency }} {{ number_format((float)$order->shipping_total,2) }}</span></div>
        @if((float)$order->gift_wrap_total>0)<div><span>Gift presentation</span><span>{{ $order->currency }} {{ number_format((float)$order->gift_wrap_total,2) }}</span></div>@endif
        <div class="grand"><span>Total</span><span>{{ $order->currency }} {{ number_format((float)$order->grand_total,2) }}</span></div>
    </div>

    <section class="grid" style="margin-top:38px">
        <div><div class="label">Payment</div><div class="muted">{{ str_replace('_',' ',ucfirst($order->payment_method ?: '—')) }}<br>Status: {{ ucfirst($order->payment_status) }}</div></div>
        <div><div class="label">Fulfilment</div><div class="muted">Order: {{ ucfirst($order->status) }}<br>Shipping: {{ $order->shipping_method ?: '—' }}@if($order->tracking_number)<br>Tracking: {{ $order->tracking_number }}@endif</div></div>
    </section>

    <footer class="footer">
        Thank you for choosing Scents by Aamir. This invoice was generated from the live Laravel order record.
        @if($order->notes)<br><strong>Order note:</strong> {{ $order->notes }}@endif
    </footer>
</main>
</body>
</html>

<!doctype html>
<html>
<body style="margin:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 14px">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:660px;background:#fff;border:1px solid #e1e4e8">
<tr><td style="background:#17191d;color:#fff;padding:26px 32px">
<div style="font-size:18px;font-weight:700">SCENTS BY AAMIR</div>
<div style="margin-top:5px;font-size:10px;letter-spacing:.16em;color:#9fa5af">NEW ORDER</div>
</td></tr>
<tr><td style="padding:34px 32px">
<h1 style="margin:0;font-size:27px;font-weight:600">{{ $order->order_number }}</h1>
<p style="margin:8px 0 24px;font-size:13px;color:#656d78">{{ $order->customer_name }} · {{ $order->customer_email }}</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0">
@foreach($order->items as $item)
<tr><td style="padding:12px 0;border-top:1px solid #eceef1;font-size:12px"><strong>{{ $item->product_name }}</strong><br><span style="color:#7a818c">Qty {{ $item->quantity }}</span></td><td align="right" style="padding:12px 0;border-top:1px solid #eceef1;font-size:12px">{{ $order->currency }} {{ number_format((float)$item->line_total,0) }}</td></tr>
@endforeach
<tr><td style="padding-top:16px;font-size:13px;font-weight:700">Order total</td><td align="right" style="padding-top:16px;font-size:16px;font-weight:700">{{ $order->currency }} {{ number_format((float)$order->grand_total,0) }}</td></tr>
</table>
<p style="margin:24px 0 0;font-size:11px;color:#717985">Payment: {{ str_replace('_',' ',ucfirst($order->payment_method ?: '—')) }} · Status: {{ ucfirst($order->status) }}</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>

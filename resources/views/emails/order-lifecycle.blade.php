<!doctype html>
<html>
<body style="margin:0;background:#f3f2ee;font-family:Arial,Helvetica,sans-serif;color:#111">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f2ee;padding:34px 14px">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#fff;border:1px solid #dedbd4">
<tr><td style="background:#111;padding:27px 34px;color:#fff">
<div style="font-size:18px;font-weight:700;letter-spacing:.02em">SCENTS BY AAMIR</div>
<div style="margin-top:5px;font-size:10px;letter-spacing:.18em;color:#aaa">ORDER UPDATE</div>
</td></tr>
<tr><td style="padding:36px 34px">
<div style="font-size:11px;color:#8a857d;letter-spacing:.08em">ORDER {{ $order->order_number }}</div>
<h1 style="margin:12px 0 16px;font-size:28px;line-height:1.2;font-weight:600">{{ $headline }}</h1>
<p style="margin:0;font-size:14px;line-height:1.75;color:#55514a">{{ $intro }}</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:28px;border-top:1px solid #ece9e3">
@foreach($order->items as $item)
<tr>
<td style="padding:15px 0;border-bottom:1px solid #ece9e3;font-size:12px">
<strong>{{ $item->product_name }}</strong><br>
<span style="color:#827d75">Qty {{ $item->quantity }}@if($item->sku) · {{ $item->sku }}@endif</span>
</td>
<td align="right" style="padding:15px 0;border-bottom:1px solid #ece9e3;font-size:12px">{{ $order->currency }} {{ number_format((float)$item->line_total,0) }}</td>
</tr>
@endforeach
</table>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:18px">
<tr><td style="font-size:12px;color:#817b72">Total</td><td align="right" style="font-size:15px;font-weight:700">{{ $order->currency }} {{ number_format((float)$order->grand_total,0) }}</td></tr>
@if($order->tracking_number)
<tr><td style="padding-top:10px;font-size:12px;color:#817b72">Tracking</td><td align="right" style="padding-top:10px;font-size:12px">{{ $order->tracking_number }}</td></tr>
@endif
</table>

@if($event === 'payment_rejected' && $reason)
<div style="margin-top:24px;padding:14px;background:#fff1f0;border:1px solid #f0b7b3;font-size:12px;line-height:1.6;color:#8d231c">
<strong>Payment verification note</strong><br>{{ $reason }}
</div>
@endif

<p style="margin:30px 0 0;font-size:11px;line-height:1.65;color:#87827a">Need help? Reply to this email or contact Scents by Aamir customer care.</p>
</td></tr>
<tr><td style="border-top:1px solid #ece9e3;padding:20px 34px;font-size:10px;color:#8a857d">© {{ date('Y') }} Scents by Aamir · Pakistan</td></tr>
</table>
</td></tr>
</table>
</body>
</html>

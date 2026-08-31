<!doctype html>
<html>
<body style="margin:0;background:#f3f2ee;font-family:Arial,Helvetica,sans-serif;color:#111">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:34px 14px">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#fff;border:1px solid #dedbd4">
<tr><td style="background:#111;padding:27px 34px;color:#fff"><div style="font-size:18px;font-weight:700">SCENTS BY AAMIR</div><div style="margin-top:5px;font-size:10px;letter-spacing:.18em;color:#aaa">ACCOUNT</div></td></tr>
<tr><td style="padding:38px 34px">
<div style="font-size:11px;color:#8a857d">Hello {{ $customer->first_name ?: $customer->full_name }},</div>
<h1 style="margin:12px 0 16px;font-size:28px;line-height:1.2;font-weight:600">{{ $headline }}</h1>
<p style="margin:0;font-size:14px;line-height:1.75;color:#55514a">{{ $message }}</p>
@if($buttonLabel && $buttonUrl)
<table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:26px"><tr><td style="background:#111"><a href="{{ $buttonUrl }}" style="display:inline-block;padding:14px 20px;color:#fff;text-decoration:none;font-size:11px;font-weight:700;letter-spacing:.08em">{{ $buttonLabel }}</a></td></tr></table>
@endif
<p style="margin:30px 0 0;font-size:11px;line-height:1.65;color:#87827a">If you did not expect this message, contact Scents by Aamir customer care.</p>
</td></tr>
<tr><td style="border-top:1px solid #ece9e3;padding:20px 34px;font-size:10px;color:#8a857d">© {{ date('Y') }} Scents by Aamir · Pakistan</td></tr>
</table>
</td></tr></table>
</body></html>

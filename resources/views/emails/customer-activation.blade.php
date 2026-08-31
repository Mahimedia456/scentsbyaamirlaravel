<!doctype html>
<html>
<body style="margin:0;background:#f3f2ee;font-family:Arial,Helvetica,sans-serif;color:#111">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f2ee;padding:34px 14px">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#fff;border:1px solid #dedbd4">
<tr><td style="background:#111;padding:27px 34px;color:#fff">
    <div style="font-size:18px;font-weight:700;letter-spacing:.02em">SCENTS BY AAMIR</div>
    <div style="margin-top:5px;font-size:10px;letter-spacing:.18em;color:#aaa">ACCOUNT ACTIVATION</div>
</td></tr>
<tr><td style="padding:38px 34px">
    <p style="margin:0;font-size:13px;color:#6d6a64">Welcome, {{ $customer->first_name }}.</p>
    <h1 style="margin:12px 0 18px;font-size:28px;line-height:1.2;font-weight:600">Activate your account</h1>
    <p style="margin:0 0 24px;font-size:14px;line-height:1.7;color:#55514a">Confirm your email address to activate your Scents by Aamir account. After activation you can sign in, manage your profile and review your orders.</p>
    <a href="{{ $activationUrl }}" style="display:inline-block;background:#111;color:#fff;text-decoration:none;padding:14px 22px;font-size:11px;font-weight:700;letter-spacing:.12em">ACTIVATE ACCOUNT</a>
    <p style="margin:26px 0 0;font-size:11px;line-height:1.6;color:#87827a">This secure activation link expires in 24 hours. If you did not create this account, no action is required.</p>
</td></tr>
<tr><td style="border-top:1px solid #ece9e3;padding:20px 34px;font-size:10px;color:#8a857d">© {{ date('Y') }} Scents by Aamir · Pakistan</td></tr>
</table>
</td></tr>
</table>
</body>
</html>

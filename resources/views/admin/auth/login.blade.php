<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Sign In — Scents by Aamir</title>
    @vite(['resources/css/admin.css','resources/js/admin.js'])
</head>
<body style="background:#eef0f3">
<div style="min-height:100vh;display:grid;grid-template-columns:minmax(0,1.1fr) minmax(420px,.9fr)">
    <section style="background:#16181c;color:#fff;padding:48px;display:flex;flex-direction:column;justify-content:space-between">
        <img src="{{ asset('logo.png') }}" alt="Scents by Aamir" style="height:36px;width:auto;align-self:flex-start;filter:brightness(0) invert(1)">
        <div style="max-width:620px">
            <div style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.42)">Enterprise Commerce</div>
            <h1 style="margin:18px 0 0;font-size:48px;line-height:1.04;letter-spacing:-.04em;font-weight:670">Operate the brand from one secure workspace.</h1>
            <p style="margin:20px 0 0;max-width:520px;color:rgba(255,255,255,.56);font-size:14px;line-height:1.7">Catalog, customers, orders, content, inventory, operations and reporting remain inside the same Laravel platform.</p>
        </div>
        <div style="font-size:11px;color:rgba(255,255,255,.3)">Scents by Aamir · Authorized staff only</div>
    </section>
    <main style="display:grid;place-items:center;padding:32px;background:#f6f7f9">
        <form method="POST" action="{{ route('admin.login.store') }}" class="admin-card" style="width:min(100%,430px);padding:30px">
            @csrf
            <div class="admin-eyebrow">Administration</div>
            <h2 style="margin:8px 0 5px;font-size:28px;letter-spacing:-.035em">Sign in</h2>
            <p class="admin-muted" style="margin:0 0 26px;font-size:12px">Use your authorized Scents by Aamir admin account.</p>

            @if($errors->any())
                <div class="admin-alert admin-alert-danger">{{ $errors->first() }}</div>
            @endif

            <label style="display:block;font-size:12px;font-weight:650">Email address
                <input name="email" type="email" value="{{ old('email') }}" required autofocus style="display:block;width:100%;box-sizing:border-box;margin-top:8px;height:44px;border:1px solid #d7dce3;border-radius:10px;padding:0 12px;background:#fff">
            </label>
            <label style="display:block;margin-top:16px;font-size:12px;font-weight:650">Password
                <input name="password" type="password" required style="display:block;width:100%;box-sizing:border-box;margin-top:8px;height:44px;border:1px solid #d7dce3;border-radius:10px;padding:0 12px;background:#fff">
            </label>
            <label style="display:flex;align-items:center;gap:8px;margin-top:15px;font-size:12px;color:#69707d">
                <input type="checkbox" name="remember" value="1"> Keep me signed in
            </label>
            <button class="admin-btn admin-btn-primary" style="width:100%;margin-top:22px;height:44px">Sign in securely</button>
        </form>
    </main>
</div>
<style>@media(max-width:800px){body>div{grid-template-columns:1fr!important} body>div>section{display:none!important}}</style>
</body>
</html>

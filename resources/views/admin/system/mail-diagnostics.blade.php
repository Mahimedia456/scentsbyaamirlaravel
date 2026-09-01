@extends('admin.layouts.app')
@section('title','Mail Diagnostics')
@section('header','Mail Diagnostics')
@section('eyebrow','System / transactional email')

@section('content')
<div style="display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:14px">
    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <div class="admin-eyebrow">Configuration</div>
                <div style="margin-top:4px;font-size:14px;font-weight:700">Production mail transport</div>
            </div>
            <span class="admin-status {{ $configured ? 'success' : 'warning' }}">{{ $configured ? 'Configured' : 'Needs configuration' }}</span>
        </div>
        <div style="padding:20px">
            @foreach([
                ['Mailer',$mailer ?: '—'],
                ['SMTP host',$host ?: '—'],
                ['SMTP port',$port ?: '—'],
                ['SMTP scheme',$scheme ?: '—'],
                ['SMTP username',$username ?: '—'],
                ['SMTP password',$passwordSet ? 'SET' : 'MISSING'],
                ['From address',$from ?: '—'],
                ['From name',$fromName ?: '—'],
            ] as [$label,$value])
                <div class="admin-mini-stat">
                    <span class="admin-muted" style="font-size:11px">{{ $label }}</span>
                    <strong style="font-size:11px;word-break:break-all">{{ $value }}</strong>
                </div>
            @endforeach

            <div style="margin-top:18px;padding:14px;border-radius:11px;background:#faf7ef;border:1px solid #eee2c8;font-size:11px;line-height:1.65;color:#6f5b34">
                Passwords and SMTP secrets are never rendered in this page. Configure them only in the production <code>.env</code>.
            </div>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <div class="admin-eyebrow">Delivery test</div>
                <div style="margin-top:4px;font-size:14px;font-weight:700">Send a real test message</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.mail-diagnostics.test') }}" style="padding:20px">
            @csrf
            <label style="display:block;font-size:11px;font-weight:680">Destination email
                <input class="admin-field" style="margin-top:8px" type="email" name="email" required value="{{ old('email',auth()->user()->email) }}">
            </label>
            <p class="admin-muted" style="margin:10px 0 0;font-size:10px;line-height:1.55">This sends the branded diagnostics template through Laravel's currently configured mail transport.</p>
            <button class="admin-btn admin-btn-primary" style="width:100%;margin-top:18px">Send test email</button>
        </form>
    </section>
</div>

<section class="admin-card" style="margin-top:14px;padding:20px">
    <div class="admin-eyebrow">Email roadmap status</div>
    <h3 style="margin:7px 0 18px;font-size:17px">Transactional system</h3>
    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px">
        @foreach([
            ['Customer activation','Wired','success'],
            ['Activation resend','Wired','success'],
            ['Forgot / reset password','Wired','success'],
            ['Order lifecycle emails','Wired','success'],
            ['Admin new-order alert','Wired','success'],
            ['SMTP transport',$configured ? 'Ready' : 'Check settings',$configured ? 'success' : 'warning'],
        ] as [$name,$state,$kind])
            <div style="padding:15px;border:1px solid #e4e7ec;border-radius:11px">
                <div style="font-size:11px;font-weight:680">{{ $name }}</div>
                <span class="admin-status {{ $kind }}" style="margin-top:10px">{{ $state }}</span>
            </div>
        @endforeach
    </div>
</section>

<style>
@media(max-width:900px){.admin-page>div:first-child{grid-template-columns:1fr!important}}
@media(max-width:700px){.admin-page section:last-child>div:last-child{grid-template-columns:1fr!important}}
</style>
@endsection

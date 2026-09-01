@extends('layouts.store')

@section('title', 'Forgot Password — Scents by Aamir')

@section('content')
<section class="min-h-[78vh] bg-[#f6f4ef] pt-[120px] text-black">
    <div class="house-container py-14">
        <div class="mx-auto max-w-[560px] border border-black/10 bg-white p-7 sm:p-10">
            <p class="ui-label text-black/35">Account recovery</p>
            <h1 class="mt-4 display-serif text-[48px] leading-[.95]">Reset your password.</h1>
            <p class="mt-5 text-sm leading-7 text-black/50">Enter the email used for your Scents by Aamir account. We will send a secure reset link.</p>

            @if(session('success'))
                <div class="mt-6 border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="mt-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('customer.password.email') }}" class="mt-7 space-y-4">
                @csrf
                <label class="block">
                    <span class="ui-label text-black/40">Email</span>
                    <input class="admin-field mt-2 w-full" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                </label>
                <button class="btn-solid w-full" type="submit">Send reset link</button>
            </form>

            <a href="{{ route('customer.login') }}" class="mt-6 inline-block text-link">← Back to sign in</a>
        </div>
    </div>
</section>
@endsection

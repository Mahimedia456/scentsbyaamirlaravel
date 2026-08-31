@extends('layouts.store')
@section('title','Sign In — Scents by Aamir')
@section('content')
<section class="min-h-screen bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container grid min-h-[calc(100vh-100px)] lg:grid-cols-2">
        <div class="flex items-center border-b border-black/10 py-12 lg:border-r lg:border-b-0 lg:pr-14">
            <div class="max-w-xl">
                <p class="ui-label text-black/35">Private Account</p>
                <h1 class="mt-4 display-serif text-[52px] leading-[.94] sm:text-[66px]">Welcome back.</h1>
                <p class="mt-6 max-w-md text-sm leading-7 text-black/50">Sign in to manage delivery details, checkout, orders and your saved fragrance wardrobe.</p>
                <div class="mt-10 grid gap-px bg-black/10 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                    @foreach([['01','Faster checkout'],['02','Order history'],['03','Saved details']] as [$i,$label])
                        <div class="bg-white p-5"><span class="ui-label text-black/25">{{ $i }}</span><p class="mt-5 text-sm">{{ $label }}</p></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex items-center py-12 lg:pl-14">
            <form method="POST" action="{{ route('customer.login.store') }}" class="w-full max-w-lg bg-white p-7 sm:p-10">
                @csrf
                <p class="ui-label text-black/35">Sign In</p>
                <h2 class="mt-3 display-serif text-[42px]">Your account.</h2>

                @if(session('error'))<div class="mt-5 border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="mt-5 border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif

                <div class="mt-7 grid gap-5">
                    <label><span class="ui-label text-black/35">Email</span><input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 min-h-[54px] w-full border border-black/15 px-4"></label>
                    <label><span class="ui-label text-black/35">Password</span><input name="password" type="password" required autocomplete="current-password" class="mt-2 min-h-[54px] w-full border border-black/15 px-4"></label>
                    <label class="flex gap-3 text-xs text-black/50"><input type="checkbox" name="remember" value="1"> Remember me on this device</label>
                    <button class="btn-solid">Sign in</button>
                </div>

                <p class="mt-6 border-t border-black/10 pt-6 text-sm text-black/50">New to the house? <a class="text-link" href="{{ route('customer.register') }}">Create account</a></p>
            </form>
        </div>
    </div>
</section>

@if(session('activation_email'))
    <div class="mt-6 border border-black/10 bg-white p-5">
        <p class="text-xs leading-6 text-black/60">Your account is waiting for email activation.</p>
        <form method="POST" action="{{ route('customer.activation.resend') }}" class="mt-3">
            @csrf
            <input type="hidden" name="email" value="{{ session('activation_email') }}">
            <button class="text-link">Resend activation email</button>
        </form>
    </div>
@endif

@endsection

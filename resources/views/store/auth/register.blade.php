@extends('layouts.store')
@section('title','Create Account — Scents by Aamir')
@section('content')
<section class="min-h-screen bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container grid min-h-[calc(100vh-100px)] lg:grid-cols-2">
        <div class="order-2 flex items-center border-b border-black/10 py-12 lg:order-1 lg:border-r lg:border-b-0 lg:pr-14">
            <div class="max-w-xl">
                <p class="ui-label text-black/35">Join the House</p>
                <h1 class="mt-4 display-serif text-[52px] leading-[.94] sm:text-[66px]">Your private account.</h1>
                <p class="mt-6 max-w-md text-sm leading-7 text-black/50">Save your delivery details, keep your order history together and move through checkout with less friction.</p>
            </div>
        </div>

        <div class="order-1 flex items-center py-12 lg:order-2 lg:pl-14">
            <form method="POST" action="{{ route('customer.register.store') }}" class="w-full max-w-xl bg-white p-7 sm:p-10">
                @csrf
                <p class="ui-label text-black/35">Create Account</p>
                <h2 class="mt-3 display-serif text-[42px]">Join Scents by Aamir.</h2>

                @if($errors->any())<div class="mt-5 border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif

                <div class="mt-7 grid gap-4 sm:grid-cols-2">
                    <label><span class="ui-label text-black/35">First name</span><input name="first_name" value="{{ old('first_name') }}" required class="mt-2 min-h-[54px] w-full border border-black/15 px-4"></label>
                    <label><span class="ui-label text-black/35">Last name</span><input name="last_name" value="{{ old('last_name') }}" class="mt-2 min-h-[54px] w-full border border-black/15 px-4"></label>
                    <label class="sm:col-span-2"><span class="ui-label text-black/35">Email</span><input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 min-h-[54px] w-full border border-black/15 px-4"></label>
                    <label class="sm:col-span-2"><span class="ui-label text-black/35">Phone</span><input name="phone" value="{{ old('phone') }}" autocomplete="tel" class="mt-2 min-h-[54px] w-full border border-black/15 px-4"></label>
                    <label x-data="{show:false}">
                        <span class="ui-label text-black/35">Password</span>
                        <div class="relative mt-2">
                            <input name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password" class="min-h-[54px] w-full border border-black/15 px-4 pr-16">
                            <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 px-4 text-[9px] uppercase tracking-[.12em] text-black/50" x-text="show ? 'Hide' : 'Show'"></button>
                        </div>
                    </label>
                    <label x-data="{show:false}">
                        <span class="ui-label text-black/35">Confirm password</span>
                        <div class="relative mt-2">
                            <input name="password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password" class="min-h-[54px] w-full border border-black/15 px-4 pr-16">
                            <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 px-4 text-[9px] uppercase tracking-[.12em] text-black/50" x-text="show ? 'Hide' : 'Show'"></button>
                        </div>
                    </label>
                    <button class="btn-solid sm:col-span-2">Create account</button>
                </div>

                <p class="mt-6 border-t border-black/10 pt-6 text-sm text-black/50">Already registered? <a class="text-link" href="{{ route('customer.login') }}">Sign in</a></p>
            </form>
        </div>
    </div>
</section>
@endsection

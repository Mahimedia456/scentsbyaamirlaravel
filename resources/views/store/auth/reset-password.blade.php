@extends('layouts.store')

@section('title', 'Reset Password — Scents by Aamir')

@section('content')
<section class="min-h-[78vh] bg-[#f6f4ef] pt-[120px] text-black">
    <div class="house-container py-14">
        <div class="mx-auto max-w-[560px] border border-black/10 bg-white p-7 sm:p-10">
            <p class="ui-label text-black/35">Account security</p>
            <h1 class="mt-4 display-serif text-[48px] leading-[.95]">Choose a new password.</h1>

            @if($errors->any())
                <div class="mt-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('customer.password.update') }}" class="mt-7 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label class="block">
                    <span class="ui-label text-black/40">Email</span>
                    <input class="admin-field mt-2 w-full" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
                </label>

                <label class="block">
                    <span class="ui-label text-black/40">New password</span>
                    <input class="admin-field mt-2 w-full" type="password" name="password" required autocomplete="new-password">
                </label>

                <label class="block">
                    <span class="ui-label text-black/40">Confirm password</span>
                    <input class="admin-field mt-2 w-full" type="password" name="password_confirmation" required autocomplete="new-password">
                </label>

                <button class="btn-solid w-full" type="submit">Reset password</button>
            </form>
        </div>
    </div>
</section>
@endsection

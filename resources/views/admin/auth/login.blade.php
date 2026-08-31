<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login — Scents by Aamir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f3f1ec] text-black antialiased">
<x-house.page-loader />
<div class="grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
    <section class="relative hidden overflow-hidden bg-black p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute inset-0 opacity-40" style="background:radial-gradient(circle at 35% 30%,rgba(180,154,112,.34),transparent 28%),radial-gradient(circle at 65% 72%,rgba(255,255,255,.10),transparent 30%);"></div>
        <img src="{{ asset('logo.png') }}" alt="Scents by Aamir" class="relative z-10 h-12 w-auto self-start brightness-0 invert">
        <div class="relative z-10 max-w-xl pb-10">
            <p class="text-[10px] uppercase tracking-[.3em] text-white/50">Private administration</p>
            <h1 class="mt-6 text-5xl font-light leading-[1.05] xl:text-6xl">The house,<br>managed precisely.</h1>
            <p class="mt-6 max-w-md text-sm leading-6 text-white/55">Catalog, commerce, customers and editorial operations for Scents by Aamir.</p>
        </div>
    </section>
    <section class="flex items-center justify-center px-6 py-12 sm:px-10">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="lg:hidden"><img src="{{ asset('logo-02.png') }}" class="mb-12 h-10 w-auto" alt="Scents by Aamir"></a>
            <p class="text-[10px] uppercase tracking-[.25em] text-black/45">Administration</p>
            <h2 class="mt-4 text-3xl font-medium tracking-tight">Welcome back.</h2>
            <p class="mt-2 text-sm text-black/50">Use your authorized administrator account.</p>
            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-10 space-y-6">@csrf
                <div>
                    <label for="email" class="mb-2 block text-[10px] uppercase tracking-[.18em]">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full border-0 border-b border-black/30 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                </div>
                <div>
                    <label for="password" class="mb-2 block text-[10px] uppercase tracking-[.18em]">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full border-0 border-b border-black/30 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                </div>
                @if($errors->any())<div class="border border-red-900/20 bg-red-50 px-4 py-3 text-xs text-red-900">{{ $errors->first() }}</div>@endif
                <label class="flex items-center gap-2 text-xs text-black/60"><input type="checkbox" name="remember" value="1" class="rounded border-black/30 text-black focus:ring-black"> Keep me signed in</label>
                <button type="submit" class="w-full bg-black px-5 py-4 text-[11px] font-medium uppercase tracking-[.2em] text-white hover:bg-[#242424]">Enter admin</button>
            </form>
            <p class="mt-8 text-[11px] leading-5 text-black/40">Protected area. Unauthorized access is prohibited.</p>
        </div>
    </section>
</div>
</body>
</html>

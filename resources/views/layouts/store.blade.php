<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080808">
    @php
    $seoTitle = trim($__env->yieldContent('title')) ?: config('storefront-seo.default_title');
    $seoDescription = trim($__env->yieldContent('description')) ?: config('storefront-seo.default_description');
@endphp
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="@yield('robots', config('storefront-seo.default_robots'))">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:site_name" content="{{ config('storefront-seo.site_name') }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta name="twitter:card" content="{{ config('storefront-seo.twitter_card') }}">
    <meta name="description" content="@yield('description', 'Scents by Aamir — modern fine fragrance.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-house.page-loader />
    <x-house.a11y />
    <x-house.header />
    <main id="main-content">@yield('content')</main>
    <x-house.trust-strip />
    <x-house.footer />
    <x-house.cart-drawer />
</body>
</html>

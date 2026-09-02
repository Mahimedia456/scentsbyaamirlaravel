<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080808">
    @php
        $seoTitle = trim($__env->yieldContent('title')) ?: config('storefront-seo.default_title');
        $seoDescription = trim($__env->yieldContent('description')) ?: config('storefront-seo.default_description');
        $seoCanonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $seoOgTitle = trim($__env->yieldContent('og_title')) ?: $seoTitle;
        $seoOgDescription = trim($__env->yieldContent('og_description')) ?: $seoDescription;
        $seoOgImage = trim($__env->yieldContent('og_image'));
        $seoOgType = trim($__env->yieldContent('og_type')) ?: 'website';
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="@yield('robots', config('storefront-seo.default_robots'))">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta property="og:site_name" content="{{ config('storefront-seo.site_name') }}">
    <meta property="og:title" content="{{ $seoOgTitle }}">
    <meta property="og:description" content="{{ $seoOgDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:type" content="{{ $seoOgType }}">
    @if($seoOgImage)<meta property="og:image" content="{{ $seoOgImage }}">@endif
    <meta name="twitter:card" content="{{ config('storefront-seo.twitter_card') }}">
    <meta name="twitter:title" content="{{ $seoOgTitle }}">
    <meta name="twitter:description" content="{{ $seoOgDescription }}">
    @if($seoOgImage)<meta name="twitter:image" content="{{ $seoOgImage }}">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-house.page-loader />
    <x-house.a11y />
    <x-house.header />
    <main id="main-content" class="pb-[62px] lg:pb-0">@yield('content')</main>
    <x-house.trust-strip />
    <x-house.footer />
    <x-house.cart-drawer />
    <x-house.cart-toast />
    <x-house.mobile-bottom-nav />
</body>
</html>

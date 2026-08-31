<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080808">
    <title>@yield('title', 'Scents by Aamir')</title>
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

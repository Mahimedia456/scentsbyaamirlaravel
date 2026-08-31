@extends('layouts.store')

@php
    $slug = $slug ?? request()->route('slug');

    $fallbackMeta = [
        'signature-worlds' => [
            'eyebrow' => 'Collection 01',
            'subtitle' => 'The defining fragrance wardrobe.',
            'copy' => 'Signature Worlds brings together compositions that express the house through contrast, polish and memorable materials.',
            'tone' => 'night',
            'image' => 'images/collections/signature-worlds.webp',
        ],
        'nocturnal' => [
            'eyebrow' => 'Collection 02',
            'subtitle' => 'Fragrance after daylight.',
            'copy' => 'A darker edit built around smoke, oud, woods, resinous warmth and the intimacy of evening wear.',
            'tone' => 'night',
            'image' => 'images/collections/nocturnal.webp',
        ],
        'light-studies' => [
            'eyebrow' => 'Collection 03',
            'subtitle' => 'Air, skin and brightness.',
            'copy' => 'A lighter register of citrus, musk, fresh florals and clean woods, shaped with clarity and restraint.',
            'tone' => 'sand',
            'image' => 'images/collections/light-studies.webp',
        ],
    ];

    $meta = $fallbackMeta[$slug] ?? [
        'eyebrow' => 'House Collection',
        'subtitle' => $collectionData['description'] ?? 'A curated fragrance world.',
        'copy' => $collectionData['description'] ?? 'A collection shaped through material, mood and atmosphere.',
        'tone' => 'night',
        'image' => "images/collections/{$slug}.webp",
    ];

    $campaigns = config('storefront.campaigns');
    $heroImage = file_exists(public_path($meta['image']))
        ? asset($meta['image'])
        : (
            $slug === 'nocturnal'
                ? ($campaigns['nocturnal']['image'] ?? null)
                : (
                    $slug === 'light-studies'
                        ? ($campaigns['light_studies']['image'] ?? null)
                        : ($campaigns['signature']['image'] ?? null)
                )
        );

    $collection = [
        'title' => $collectionData['name'] ?? 'Collection',
        'eyebrow' => $meta['eyebrow'],
        'subtitle' => $meta['subtitle'],
        'copy' => $collectionData['description'] ?: $meta['copy'],
        'tone' => $meta['tone'],
        'products' => $collectionData['products'] ?? collect(),
    ];
@endphp

@section('title', $collection['title'] . ' — Scents by Aamir')
@section('description', $collection['copy'])

@section('content')

{{-- COLLECTION HERO --}}
<section class="relative overflow-hidden bg-black pt-[100px] text-white">
    <div class="absolute inset-x-0 bottom-0 top-[100px]">
        @if($heroImage)
            <img src="{{ $heroImage }}" alt="{{ $collection['title'] }} collection" class="h-full w-full object-cover object-center" fetchpriority="high">
        @endif
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.92)_0%,rgba(0,0,0,.72)_36%,rgba(0,0,0,.18)_68%,rgba(0,0,0,.48)_100%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/12"></div>
    </div>

    <div class="house-container relative flex min-h-[620px] items-end py-16 sm:min-h-[680px] lg:min-h-[720px] lg:items-center">
        <div class="max-w-[720px]" data-reveal>
            <div class="flex items-center gap-4">
                <span class="h-px w-10 bg-[#c9ad7a]"></span>
                <p class="ui-label text-white/58">{{ $collection['eyebrow'] }}</p>
            </div>

            <h1 class="mt-6 max-w-[720px] display-serif text-[48px] leading-[.92] tracking-[-.035em] sm:text-[64px] lg:text-[76px]">
                {{ $collection['title'] }}
            </h1>

            <p class="mt-5 max-w-[600px] display-serif text-[22px] leading-tight italic text-[#d6c19a] sm:text-[28px]">
                {{ $collection['subtitle'] }}
            </p>

            <p class="mt-6 max-w-xl text-sm leading-7 text-white/64">
                {{ $collection['copy'] }}
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#collection-fragrances" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Explore the edit</a>
                <a href="{{ route('collections') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">All collections</a>
            </div>
        </div>
    </div>
</section>

{{-- PRODUCT EDIT --}}
<section id="collection-fragrances" class="bg-[#f7f6f2] text-black">
    <div class="house-container py-14 lg:py-20">
        <div class="mb-10 flex flex-col gap-5 border-b border-black/10 pb-7 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="ui-label text-black/40">The Edit</p>
                <h2 class="mt-3 display-serif text-[44px] leading-[.94] sm:text-[56px]">Fragrances in this world</h2>
            </div>
            <span class="ui-label text-black/40">{{ count($collection['products']) }} Fragrances</span>
        </div>

        <div class="grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($collection['products'] as $product)
                <x-house.product-card
                    :product="$product"
                    :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                    :tone="in_array($product['slug'] ?? '', ['velvet-oud','after-dark','oud-noir','midnight-resin']) ? 'dark' : 'light'"
                />
            @empty
                <div class="col-span-full border border-black/10 bg-white px-7 py-16 text-center">
                    <p class="display-serif text-4xl">This collection is being composed.</p>
                    <p class="mx-auto mt-4 max-w-lg text-sm leading-7 text-black/45">
                        No active fragrances are assigned to this collection yet. Explore the full fragrance wardrobe in the meantime.
                    </p>
                    <a href="{{ route('shop') }}" class="btn-solid mt-7">Shop all fragrances</a>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- COLLECTION STORY --}}
<section class="border-y border-black/10 bg-white text-black">
    <div class="house-container grid gap-10 py-16 lg:grid-cols-[.72fr_1.28fr] lg:py-24">
        <div>
            <p class="ui-label text-black/40">Collection Story</p>
            <p class="mt-4 max-w-xs text-sm leading-7 text-black/42">
                A shared atmosphere can connect fragrances that otherwise smell entirely different.
            </p>
        </div>

        <div>
            <h2 class="max-w-4xl display-serif text-[44px] leading-[.95] sm:text-[58px]">
                A collection is not just a category. It is a way of wearing fragrance.
            </h2>
            <p class="mt-8 max-w-2xl text-sm leading-7 text-black/55">{{ $collection['copy'] }}</p>
        </div>
    </div>
</section>

{{-- CROSS DISCOVERY --}}
<section class="bg-[#efebe3] text-black">
    <div class="house-container py-16 lg:py-20">
        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('shop', ['audience' => 'men']) }}" class="group border border-black/10 bg-[#f7f6f2] p-7 transition-colors hover:bg-white">
                <p class="ui-label text-black/38">Wear by identity</p>
                <h3 class="mt-4 display-serif text-4xl">For Men</h3>
                <span class="mt-7 inline-block text-sm transition-transform group-hover:translate-x-1">Explore →</span>
            </a>

            <a href="{{ route('shop', ['audience' => 'women']) }}" class="group border border-black/10 bg-[#f7f6f2] p-7 transition-colors hover:bg-white">
                <p class="ui-label text-black/38">Wear by identity</p>
                <h3 class="mt-4 display-serif text-4xl">For Women</h3>
                <span class="mt-7 inline-block text-sm transition-transform group-hover:translate-x-1">Explore →</span>
            </a>

            <a href="{{ route('shop', ['audience' => 'unisex']) }}" class="group border border-black/10 bg-[#f7f6f2] p-7 transition-colors hover:bg-white">
                <p class="ui-label text-black/38">Beyond category</p>
                <h3 class="mt-4 display-serif text-4xl">Unisex</h3>
                <span class="mt-7 inline-block text-sm transition-transform group-hover:translate-x-1">Explore →</span>
            </a>
        </div>
    </div>
</section>

{{-- CONTINUE --}}
<section class="bg-black text-white">
    <div class="house-container grid gap-10 py-18 lg:grid-cols-[1fr_auto] lg:items-center lg:py-24">
        <div>
            <p class="ui-label text-white/40">Continue exploring</p>
            <h2 class="mt-4 max-w-3xl display-serif text-[46px] leading-[.94] sm:text-[60px]">The complete fragrance wardrobe.</h2>
        </div>
        <a href="{{ route('shop') }}" class="line-button border-white text-white hover:bg-white hover:text-black">Shop all fragrances</a>
    </div>
</section>

<x-house.quick-view />
@endsection

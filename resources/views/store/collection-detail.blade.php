@extends('layouts.store')

@php
    $slug = $slug ?? request()->route('slug');
    $fallbackMeta = [
        'signature-worlds' => ['eyebrow'=>'Collection 01','subtitle'=>'The essential fragrance wardrobe.','copy'=>'Four distinct atmospheres that define the house: memory, depth, light and texture.','tone'=>'sage'],
        'nocturnal' => ['eyebrow'=>'Collection 02','subtitle'=>'Fragrance after daylight.','copy'=>'A darker edit built around oud, smoke, leather, woods and resinous warmth.','tone'=>'night'],
        'light-studies' => ['eyebrow'=>'Collection 03','subtitle'=>'Air, skin and brightness.','copy'=>'A lighter register of citrus, musk, neroli and clean woods shaped for clarity.','tone'=>'sand'],
    ];
    $meta = $fallbackMeta[$slug] ?? ['eyebrow'=>'House Collection','subtitle'=>$collectionData['description'] ?? 'A curated fragrance world.','copy'=>$collectionData['description'] ?? 'A collection shaped through material, mood and atmosphere.','tone'=>'sage'];
    $collection = [
        'title' => $collectionData['name'] ?? 'Collection',
        'eyebrow' => $meta['eyebrow'],
        'subtitle' => $meta['subtitle'],
        'copy' => $collectionData['description'] ?: $meta['copy'],
        'tone' => $meta['tone'],
        'products' => $collectionData['products'] ?? collect(),
    ];
    $tones = [
        'sage' => 'bg-[radial-gradient(circle_at_25%_20%,rgba(235,193,111,.80),transparent_20%),radial-gradient(circle_at_74%_25%,rgba(102,124,126,.82),transparent_25%),linear-gradient(135deg,#99aea0,#76928f)]',
        'night' => 'bg-[radial-gradient(circle_at_28%_24%,rgba(112,91,65,.55),transparent_22%),radial-gradient(circle_at_72%_68%,rgba(55,62,83,.74),transparent_28%),linear-gradient(135deg,#1a1a1a,#050505)]',
        'sand' => 'bg-[radial-gradient(circle_at_28%_22%,rgba(244,223,170,.88),transparent_22%),radial-gradient(circle_at_74%_65%,rgba(173,143,110,.52),transparent_28%),linear-gradient(135deg,#d7c7ab,#b6a086)]',
    ];
@endphp

@section('title', $collection['title'] . ' — Scents by Aamir')

@section('content')
<section class="relative min-h-[82vh] overflow-hidden pt-[110px] text-white {{ $tones[$collection['tone']] }}">
    <div class="absolute inset-0 bg-black/10"></div>
    <div class="house-container relative flex min-h-[calc(82vh-110px)] items-end py-10 lg:py-14">
        <div class="grid w-full gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
            <div>
                <p class="ui-label text-white/60">{{ $collection['eyebrow'] }}</p>
                <h1 class="mt-4 display-serif text-6xl leading-[.86] sm:text-8xl lg:text-[8.5rem]">{{ $collection['title'] }}</h1>
                <p class="mt-5 text-xl sm:text-2xl">{{ $collection['subtitle'] }}</p>
            </div>
            <div class="lg:justify-self-end"><p class="max-w-md text-sm leading-7 text-white/68">{{ $collection['copy'] }}</p></div>
        </div>
    </div>
</section>

<section class="bg-[#f7f6f2] text-black">
    <div class="house-container py-14 lg:py-20">
        <div class="mb-10 flex flex-col gap-5 border-b border-black/10 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="ui-label text-black/40">The Edit</p><h2 class="mt-3 display-serif text-5xl sm:text-6xl">Fragrances in this world</h2></div>
            <span class="ui-label text-black/40">{{ count($collection['products']) }} Fragrances</span>
        </div>
        <div class="grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($collection['products'] as $product)
                <x-house.product-card :product="$product" :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)" :tone="in_array($product['slug'], ['velvet-oud','after-dark','oud-noir','midnight-resin']) ? 'dark' : 'light'" />
            @empty
                <div class="col-span-full border border-black/10 bg-white p-10 text-center text-sm text-black/45">No active fragrances are assigned to this collection yet.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="border-y border-black/10 bg-white text-black">
    <div class="house-container grid gap-10 py-16 lg:grid-cols-[.8fr_1.2fr] lg:py-24">
        <div><p class="ui-label text-black/40">Collection Story</p></div>
        <div><h2 class="display-serif max-w-4xl text-5xl leading-[.95] sm:text-6xl">A collection is not just a category. It is a shared atmosphere.</h2><p class="mt-8 max-w-2xl text-sm leading-7 text-black/55">{{ $collection['copy'] }}</p></div>
    </div>
</section>

<section class="bg-black text-white">
    <div class="house-container grid gap-10 py-18 lg:grid-cols-[1fr_auto] lg:items-center lg:py-24">
        <div><p class="ui-label text-white/40">Continue exploring</p><h2 class="mt-4 display-serif text-6xl leading-[.9]">The complete fragrance wardrobe.</h2></div>
        <a href="{{ route('shop') }}" class="line-button border-white text-white hover:bg-white hover:text-black">Shop all fragrances</a>
    </div>
</section>

<x-house.quick-view />
@endsection

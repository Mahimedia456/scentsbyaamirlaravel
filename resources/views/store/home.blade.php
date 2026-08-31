@extends('layouts.store')

@php
    $campaigns = config('storefront.campaigns');
    $products = ($homeProducts ?? $featuredProducts ?? collect())->values();
    if ($products->isEmpty()) {
        $products = collect(config('storefront.products'))->map(fn($p,$slug)=>array_merge($p,['slug'=>$slug]))->values();
    }
    $imageProducts = $products->filter(fn($p) => !empty($p['image']) || !empty($p['world_image']))->values();
    $heroProduct = $imageProducts->first() ?? $products->first();
    $signatureProduct = $imageProducts->get(1) ?? $heroProduct;
    $nocturnalProduct = $imageProducts->get(2) ?? $heroProduct;
    $finderProduct = $imageProducts->get(3) ?? $heroProduct;
    $roseProduct = $imageProducts->get(4) ?? $signatureProduct ?? $heroProduct;
    $heroImage = $heroProduct['image'] ?? $heroProduct['world_image'] ?? null;
    $signatureImage = $signatureProduct['world_image'] ?? $signatureProduct['image'] ?? $campaigns['signature']['image'];
    $nocturnalImage = $nocturnalProduct['world_image'] ?? $nocturnalProduct['image'] ?? $campaigns['nocturnal']['image'];
    $finderImage = $finderProduct['world_image'] ?? $finderProduct['image'] ?? $campaigns['finder']['image'];
    $roseImage = $roseProduct['world_image'] ?? $roseProduct['image'] ?? $campaigns['rose_material']['image'];
@endphp

@section('title', 'Scents by Aamir — Fine Fragrance')
@section('description', 'Modern fine fragrance shaped through atmosphere, material and memory.')

@section('content')
<section class="home-editorial-hero relative min-h-screen overflow-hidden text-white">
    <div class="absolute inset-0 opacity-[.035]" style="background-image:linear-gradient(rgba(255,255,255,.8) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.8) 1px,transparent 1px);background-size:64px 64px"></div>
    <div class="absolute -left-24 top-1/3 h-72 w-72 rounded-full border border-white/10"></div>
    <div class="absolute right-[8%] top-[16%] h-40 w-40 rounded-full border border-white/10"></div>

    <div class="house-container relative grid min-h-screen items-center gap-12 pb-14 pt-[128px] lg:grid-cols-[.9fr_1.1fr] lg:gap-16 lg:pb-16 lg:pt-[138px]">
        <div class="relative z-10 max-w-3xl" data-reveal>
            <div class="flex items-center gap-4">
                <span class="h-px w-10 bg-white/35"></span>
                <p class="ui-label text-white/55">The House / New Chapter</p>
            </div>
            <h1 class="mt-7 display-serif text-[4.4rem] leading-[.82] tracking-[-.055em] sm:text-[7rem] lg:text-[8.6rem]">
                Scent,<br><span class="italic text-[#d8c5a3]">remembered.</span>
            </h1>
            <p class="mt-8 max-w-xl text-[15px] leading-7 text-white/62">
                Modern fine fragrance composed through atmosphere, material and memory. Discover the latest edit from Scents by Aamir.
            </p>
            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ route('shop') }}" class="btn-solid bg-white text-black hover:bg-[#d8c5a3]">Explore fragrances</a>
                <a href="{{ route('finder') }}" class="btn-outline border-white/45 text-white hover:bg-white hover:text-black">Find your scent</a>
            </div>
            <div class="mt-12 grid max-w-xl grid-cols-3 border-t border-white/15 pt-5 text-white/45">
                <div><p class="ui-label">01</p><p class="mt-2 text-xs">Fine fragrance</p></div>
                <div><p class="ui-label">02</p><p class="mt-2 text-xs">Small-batch edit</p></div>
                <div><p class="ui-label">03</p><p class="mt-2 text-xs">Made to linger</p></div>
            </div>
        </div>

        <div class="relative flex min-h-[52vh] items-center justify-center lg:min-h-[70vh]" data-reveal>
            <div class="home-editorial-hero__image-shell relative flex h-full min-h-[52vh] w-full max-w-[640px] items-center justify-center overflow-hidden rounded-[2px] p-8 sm:p-14 lg:min-h-[70vh]">
                <div class="absolute inset-x-8 top-8 flex items-center justify-between text-[9px] uppercase tracking-[.28em] text-white/38">
                    <span>House Selection</span><span>{{ str_pad((string) max(1,$products->count()),2,'0',STR_PAD_LEFT) }} fragrances</span>
                </div>
                @if($heroImage)
                    <img src="{{ $heroImage }}" alt="{{ $heroProduct['name'] ?? 'Scents by Aamir fragrance' }}" class="home-editorial-hero__image max-h-[58vh] w-full object-contain" fetchpriority="high">
                @else
                    <div class="flex aspect-[3/4] w-[52%] items-center justify-center border border-white/15 bg-white/[.035]">
                        <div class="text-center"><p class="display-serif text-5xl">SBA</p><p class="mt-3 ui-label text-white/35">Fragrance image</p></div>
                    </div>
                @endif
                <div class="absolute inset-x-8 bottom-8 flex items-end justify-between gap-6 border-t border-white/12 pt-5">
                    <div><p class="ui-label text-white/38">Featured fragrance</p><p class="mt-2 display-serif text-3xl">{{ $heroProduct['name'] ?? 'House Selection' }}</p></div>
                    @if(!empty($heroProduct['slug']))<a href="{{ route('product.show',$heroProduct['slug']) }}" class="ui-label whitespace-nowrap">Discover →</a>@endif
                </div>
            </div>
        </div>
    </div>
</section>

<x-house.three-atmosphere />

<section class="bg-white text-black">
    <div class="house-container grid gap-10 py-20 lg:grid-cols-[.62fr_1.38fr] lg:py-28">
        <p class="ui-label text-black/38">The House</p>
        <div data-reveal>
            <h2 class="display-serif max-w-6xl text-5xl leading-[.96] sm:text-7xl lg:text-[6.5rem]">Fragrance presented as image, memory and material.</h2>
            <div class="mt-10 grid gap-7 border-t border-black/10 pt-7 md:grid-cols-3">
                <p class="text-sm leading-6 text-black/50">Distinct visual identities for every perfume.</p>
                <p class="text-sm leading-6 text-black/50">Editorial storytelling with restrained interaction.</p>
                <p class="text-sm leading-6 text-black/50">A modern commerce experience built around discovery.</p>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#f7f6f2]">
    <div class="house-container py-16 lg:py-24">
        <div class="flex items-end justify-between gap-6">
            <div><p class="ui-label text-black/38">House Selection</p><h2 class="mt-3 display-serif text-5xl sm:text-6xl">Fragrances</h2></div>
            <a href="{{ route('shop') }}" class="text-link">Shop all →</a>
        </div>
        @if($products->isNotEmpty())
            <div class="mt-10 grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($products->take(8) as $i => $p)
                    <x-house.product-card :product="$p" :index="str_pad($i + 1, 2, '0', STR_PAD_LEFT)" :tone="in_array($p['slug'] ?? '', ['velvet-oud','after-dark','oud-noir','midnight-resin']) ? 'dark' : 'light'" />
                @endforeach
            </div>
        @else
            <div class="mt-10 border border-black/10 px-6 py-16 text-center"><p class="display-serif text-4xl">The next fragrance edit is being prepared.</p></div>
        @endif
    </div>
</section>

<section class="bg-[#efebe3]">
    <div class="house-container py-12 lg:py-16">
        <div class="grid gap-4 lg:grid-cols-[1.2fr_.8fr]">
            <a href="{{ route('shop') }}" class="relative min-h-[720px] overflow-hidden bg-[#cbc4b7] text-white">
                @if($signatureImage)<img src="{{ $signatureImage }}" alt="House selection" class="absolute inset-0 h-full w-full object-cover" loading="lazy">@endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/72 via-black/12 to-black/10"></div>
                <div class="absolute inset-x-0 bottom-0 p-7 sm:p-10"><p class="ui-label text-white/60">House Edit</p><h2 class="mt-3 display-serif text-6xl leading-[.9] sm:text-7xl">Signature Worlds</h2><p class="mt-4 max-w-lg text-sm leading-6 text-white/72">An evolving wardrobe of fragrances, each with its own atmosphere.</p><span class="mt-7 inline-block ui-label">Explore fragrances →</span></div>
            </a>
            <div class="grid gap-4">
                <a href="{{ route('shop') }}" class="relative min-h-[345px] overflow-hidden bg-[#171717] text-white">@if($nocturnalImage)<img src="{{ $nocturnalImage }}" alt="Nocturnal edit" class="absolute inset-0 h-full w-full object-cover" loading="lazy">@endif<div class="absolute inset-0 bg-black/42"></div><div class="absolute inset-x-0 bottom-0 p-7"><p class="ui-label text-white/45">After dark</p><h3 class="mt-2 display-serif text-5xl">Nocturnal</h3></div></a>
                <a href="{{ route('finder') }}" class="relative flex min-h-[345px] flex-col justify-between overflow-hidden bg-[#8d806e] p-7 text-white">@if($finderImage)<img src="{{ $finderImage }}" alt="Fragrance finder" class="absolute inset-0 h-full w-full object-cover" loading="lazy">@endif<div class="absolute inset-0 bg-black/48"></div><p class="ui-label relative text-white/55">Fragrance Finder</p><div class="relative"><h3 class="display-serif text-5xl leading-[.92]">Start with a feeling.</h3><span class="mt-7 inline-block ui-label">Find your fragrance →</span></div></a>
            </div>
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="house-container py-14 lg:py-20">
        <div class="grid gap-4 lg:grid-cols-[.88fr_1.12fr]">
            <div class="relative min-h-[620px] overflow-hidden bg-[#c8b7a6]">@if($roseImage)<img src="{{ $roseImage }}" alt="Material study" class="absolute inset-0 h-full w-full object-cover" loading="lazy">@endif<div class="absolute inset-0 bg-black/18"></div><div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/55 to-transparent p-7 pt-28 text-white"><p class="ui-label text-white/55">Material Study</p><h3 class="mt-2 display-serif text-5xl">Material, altered.</h3></div></div>
            <div class="grid min-h-[620px] grid-rows-[1fr_auto] bg-[#f0ede6]"><div class="flex items-center p-8 sm:p-12 lg:p-16"><div data-reveal><p class="ui-label text-black/38">Journal</p><h2 class="mt-4 display-serif max-w-3xl text-6xl leading-[.92] sm:text-7xl">The materials behind the memory.</h2><p class="mt-7 max-w-xl text-sm leading-7 text-black/52">Oud, amber, musk, rose and woods become part of the house narrative — explored as texture, place and emotion.</p></div></div><a href="{{ route('journal') }}" class="flex min-h-[72px] items-center justify-between border-t border-black/10 px-8 ui-label"><span>Enter the Journal</span><span>→</span></a></div>
        </div>
    </div>
</section>

<section class="bg-[#f7f6f2] text-black"><div class="house-container grid gap-10 py-20 lg:grid-cols-[.62fr_1.38fr] lg:py-28"><div><p class="ui-label text-black/35">Final House Edit</p></div><div><h2 class="display-serif max-w-5xl text-6xl leading-[.9] sm:text-8xl">Choose slowly. Wear completely.</h2><div class="mt-9 flex flex-wrap gap-3"><a href="{{ route('shop') }}" class="btn-solid">Explore all fragrances</a><a href="{{ route('finder') }}" class="btn-outline">Find your scent</a></div></div></div></section>
@endsection

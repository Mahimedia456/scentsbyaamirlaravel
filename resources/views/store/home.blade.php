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
<section class="home-luxury-hero relative overflow-hidden bg-[#0a0a0a] text-white">
    <div class="house-container grid min-h-[82vh] items-stretch px-0 pt-[102px] sm:pt-[108px] lg:grid-cols-[.82fr_1.18fr] lg:px-10 xl:px-12">
        <div class="order-2 flex items-end bg-[#0a0a0a] px-5 py-12 sm:px-8 lg:order-1 lg:px-0 lg:pb-16 lg:pr-14 lg:pt-16">
            <div class="max-w-2xl" data-reveal>
                <div class="flex items-center gap-4"><span class="h-px w-10 bg-[#c9ad7a]"></span><p class="ui-label text-white/50">Scents by Aamir / The House</p></div>
                <h1 class="mt-7 display-serif text-[3.8rem] leading-[.86] tracking-[-.055em] sm:text-[5.8rem] xl:text-[7.2rem]">Fragrance,<br><span class="italic text-[#d2bd98]">made personal.</span></h1>
                <p class="mt-7 max-w-lg text-[14px] leading-7 text-white/60">A considered wardrobe of modern perfume — composed through material, atmosphere and memory.</p>
                <div class="mt-9 flex flex-wrap gap-3"><a href="{{ route('shop') }}" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Shop fragrances</a><a href="{{ route('finder') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">Find your scent</a></div>
                <div class="mt-12 flex max-w-lg items-center justify-between border-t border-white/15 pt-5"><div><p class="ui-label text-white/35">House selection</p><p class="mt-2 text-xs text-white/60">{{ max(1,$products->count()) }} fragrances</p></div>@if(!empty($heroProduct['slug']))<a href="{{ route('product.show',$heroProduct['slug']) }}" class="ui-label text-white/65">Featured scent →</a>@endif</div>
            </div>
        </div>
        <div class="order-1 relative min-h-[58vh] overflow-hidden bg-[#d8d2c7] lg:order-2 lg:min-h-[82vh]" data-reveal>
            @if($heroImage)
                <img src="{{ $heroImage }}" alt="{{ $heroProduct['name'] ?? 'Scents by Aamir fragrance' }}" class="absolute inset-0 h-full w-full object-cover object-center" fetchpriority="high">
            @else
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_55%_42%,#d8c6a8_0,#8b765d_30%,#171513_72%)]"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-black/10"></div>
            <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-6 p-5 text-white sm:p-8 lg:p-10"><div><p class="ui-label text-white/55">Featured fragrance</p><h2 class="mt-2 display-serif text-4xl sm:text-5xl">{{ $heroProduct['name'] ?? 'House Selection' }}</h2></div>@if(!empty($heroProduct['slug']))<a href="{{ route('product.show',$heroProduct['slug']) }}" class="hidden ui-label sm:block">Discover →</a>@endif</div>
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

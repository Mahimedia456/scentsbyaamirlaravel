@extends('layouts.store')

@php
    $custom = "images/ingredients/{$slug}-ingredient.webp";
    $heroImage = file_exists(public_path($custom))
        ? asset($custom)
        : ($ingredient['fallback_image'] ?? config('storefront.campaigns.rose_material.image'));

    $relatedFamily = strtolower($ingredient['family'] ?? '');
@endphp

@section('title', $ingredient['name'].' — Ingredients — Scents by Aamir')
@section('description', $ingredient['copy'])

@section('content')

<section class="relative overflow-hidden bg-black pt-[100px] text-white">
    <div class="absolute inset-x-0 bottom-0 top-[100px]">
        @if($heroImage)
            <img src="{{ $heroImage }}" alt="{{ $ingredient['name'] }}" class="h-full w-full object-cover" fetchpriority="high">
        @endif
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.90)_0%,rgba(0,0,0,.70)_38%,rgba(0,0,0,.20)_70%,rgba(0,0,0,.46)_100%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent"></div>
    </div>

    <div class="house-container relative flex min-h-[620px] items-end py-14 sm:min-h-[680px] lg:min-h-[720px] lg:items-center">
        <div class="max-w-[700px]" data-reveal>
            <p class="ui-label text-[#d2b77f]">{{ $ingredient['family'] }}</p>
            <h1 class="mt-4 display-serif text-[54px] leading-[.9] tracking-[-.035em] sm:text-[68px] lg:text-[80px]">
                {{ $ingredient['name'] }}
            </h1>
            <p class="mt-5 display-serif text-[22px] italic text-[#d6c19a] sm:text-[28px]">{{ $ingredient['descriptor'] }}</p>
            <p class="mt-6 max-w-xl text-sm leading-7 text-white/65">{{ $ingredient['copy'] }}</p>
        </div>
    </div>
</section>

<section class="bg-white text-black">
    <div class="house-container grid gap-10 py-16 lg:grid-cols-[.58fr_1.42fr] lg:py-24">
        <div>
            <p class="ui-label text-black/35">Material Study</p>
        </div>

        <div>
            <h2 class="max-w-4xl display-serif text-[44px] leading-[.96] sm:text-[58px]">
                {{ $ingredient['copy'] }}
            </h2>

            <div class="mt-10 grid gap-5 border-y border-black/10 py-7 sm:grid-cols-3">
                <div>
                    <p class="ui-label text-black/35">Character</p>
                    <p class="mt-3 display-serif text-2xl">{{ $ingredient['descriptor'] }}</p>
                </div>
                <div>
                    <p class="ui-label text-black/35">Material Type</p>
                    <p class="mt-3 display-serif text-2xl">{{ $ingredient['family'] }}</p>
                </div>
                <div>
                    <p class="ui-label text-black/35">In the wardrobe</p>
                    <p class="mt-3 display-serif text-2xl">{{ $products->count() }} fragrances</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#f7f6f2] text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="mb-10 flex items-end justify-between gap-6 border-b border-black/10 pb-7">
            <div>
                <p class="ui-label text-black/35">Found In</p>
                <h2 class="mt-3 display-serif text-[44px] sm:text-[56px]">Fragrances</h2>
            </div>
            <a href="{{ route('shop', ['family' => $slug]) }}" class="text-link">See catalogue edit →</a>
        </div>

        <div class="grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($products->take(8) as $product)
                <x-house.product-card
                    :product="$product"
                    :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                    :tone="in_array($product['slug'] ?? '', ['dark-seduction','smoky-chic']) ? 'dark' : 'light'"
                />
            @empty
                <div class="col-span-full border border-black/10 bg-white px-6 py-14 text-center">
                    <h3 class="display-serif text-4xl">The edit is being composed.</h3>
                    <p class="mx-auto mt-4 max-w-lg text-sm leading-7 text-black/45">
                        This material is now part of the house library. Assign matching product notes/categories in admin and they will appear here automatically.
                    </p>
                    <a href="{{ route('shop') }}" class="btn-solid mt-7">Explore fragrances</a>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="bg-black text-white">
    <div class="house-container grid gap-8 py-16 lg:grid-cols-[1fr_auto] lg:items-center lg:py-20">
        <div>
            <p class="ui-label text-white/38">Continue exploring</p>
            <h2 class="mt-3 display-serif text-[44px] leading-[.95] sm:text-[56px]">Move from materials to fragrance families.</h2>
        </div>
        <a href="{{ route('families') }}" class="line-button border-white text-white hover:bg-white hover:text-black">Explore families</a>
    </div>
</section>

@endsection

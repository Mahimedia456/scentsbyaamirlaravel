@extends('layouts.store')

@php
    $families = collect($families ?? []);
    $heroImage = file_exists(public_path('images/ingredients/families-hero.webp'))
        ? asset('images/ingredients/families-hero.webp')
        : (config('storefront.campaigns.signature.image') ?? null);
@endphp

@section('title', 'Fragrance Families — Scents by Aamir')
@section('description', 'Explore fresh, floral, woody, oud, amber and spicy fragrance families at Scents by Aamir.')

@section('content')

<section class="relative overflow-hidden bg-black pt-[100px] text-white">
    <div class="absolute inset-x-0 bottom-0 top-[100px]">
        @if($heroImage)
            <img src="{{ $heroImage }}" alt="Scents by Aamir fragrance families" class="h-full w-full object-cover" fetchpriority="high">
        @endif
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.92)_0%,rgba(0,0,0,.75)_36%,rgba(0,0,0,.24)_68%,rgba(0,0,0,.48)_100%)]"></div>
    </div>

    <div class="house-container relative flex min-h-[600px] items-end py-16 sm:min-h-[660px] lg:min-h-[700px] lg:items-center">
        <div class="max-w-[700px]">
            <p class="ui-label text-[#d2b77f]">Fragrance Families</p>
            <h1 class="mt-5 display-serif text-[50px] leading-[.92] sm:text-[64px] lg:text-[76px]">Choose by character.</h1>
            <p class="mt-6 max-w-xl text-sm leading-7 text-white/64">
                Families are a faster way to understand a fragrance wardrobe: brightness, petals, woods, oud, warmth or spice.
            </p>
        </div>
    </div>
</section>

<section class="bg-[#f7f6f2] text-black">
    <div class="house-container py-14 lg:py-20">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($families as $family)
                <a href="{{ route('families.show', $family['slug']) }}" class="group border border-black/10 bg-white p-7 transition hover:bg-[#efebe3] sm:p-8">
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <p class="ui-label text-black/35">{{ $family['products_count'] }} fragrances</p>
                            <h2 class="mt-4 display-serif text-[42px] leading-none">{{ $family['name'] }}</h2>
                            <p class="mt-4 ui-label text-black/42">{{ $family['descriptor'] }}</p>
                        </div>
                        <span class="text-lg transition-transform group-hover:translate-x-1">→</span>
                    </div>
                    <p class="mt-8 text-sm leading-7 text-black/50">{{ $family['copy'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-white text-black">
    <div class="house-container grid gap-10 py-16 lg:grid-cols-[.7fr_1.3fr] lg:items-center lg:py-24">
        <div>
            <p class="ui-label text-black/35">Material first?</p>
            <h2 class="mt-4 display-serif text-[44px] leading-[.95] sm:text-[56px]">Explore the ingredient library.</h2>
        </div>
        <div>
            <p class="max-w-2xl text-sm leading-7 text-black/50">
                Families describe the overall character. Ingredients explain the materials that help create it.
            </p>
            <a href="{{ route('ingredients') }}" class="btn-solid mt-7">Explore ingredients</a>
        </div>
    </div>
</section>

@endsection

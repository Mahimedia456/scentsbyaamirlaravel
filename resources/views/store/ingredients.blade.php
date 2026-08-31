@extends('layouts.store')

@php
    $ingredients = collect($ingredients ?? []);
    $heroImage = file_exists(public_path('images/ingredients/ingredients-hero.webp'))
        ? asset('images/ingredients/ingredients-hero.webp')
        : (config('storefront.campaigns.rose_material.image') ?? null);

    $families = config('storefront.fragrance_families', []);
@endphp

@section('title', 'Ingredients & Fragrance Families — Scents by Aamir')
@section('description', 'Explore oud, rose, amber, citrus, sandalwood and the fragrance families that shape Scents by Aamir.')

@section('content')

<section class="relative overflow-hidden bg-black pt-[100px] text-white">
    <div class="absolute inset-x-0 bottom-0 top-[100px]">
        @if($heroImage)
            <img
                src="{{ $heroImage }}"
                alt="Scents by Aamir fragrance materials"
                class="h-full w-full object-cover object-center"
                fetchpriority="high"
            >
        @endif
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.93)_0%,rgba(0,0,0,.78)_35%,rgba(0,0,0,.24)_68%,rgba(0,0,0,.48)_100%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/68 via-transparent to-black/10"></div>
    </div>

    <div class="house-container relative flex min-h-[620px] items-end py-16 sm:min-h-[680px] lg:min-h-[720px] lg:items-center">
        <div class="max-w-[720px]" data-reveal>
            <div class="flex items-center gap-4">
                <span class="h-px w-10 bg-[#c9ad7a]"></span>
                <p class="ui-label text-white/58">Material Library</p>
            </div>

            <h1 class="mt-6 max-w-[720px] display-serif text-[50px] leading-[.92] tracking-[-.035em] sm:text-[64px] lg:text-[76px]">
                Ingredients & Families
            </h1>

            <p class="mt-5 max-w-[620px] display-serif text-[22px] leading-tight italic text-[#d6c19a] sm:text-[28px]">
                Follow the raw material. Understand the fragrance.
            </p>

            <p class="mt-6 max-w-xl text-sm leading-7 text-white/64">
                Explore the woods, florals, resins, citrus and spices behind the house — then move directly into the fragrance families they create.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#materials" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Explore materials</a>
                <a href="#families" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">Browse families</a>
            </div>
        </div>
    </div>
</section>

<section id="families" class="border-b border-black/10 bg-[#f7f6f2] text-black">
    <div class="house-container py-10 lg:py-12">
        <div class="mb-7 flex items-end justify-between gap-6">
            <div>
                <p class="ui-label text-black/35">Fragrance Families</p>
                <h2 class="mt-2 display-serif text-[38px] leading-none sm:text-[46px]">Begin with a feeling.</h2>
            </div>
            <a href="{{ route('families') }}" class="hidden text-link sm:inline-block">View all families →</a>
        </div>

        <div class="grid border-y border-black/10 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($families as $slug => $family)
                <a
                    href="{{ route('families.show', $slug) }}"
                    class="group border-b border-black/10 px-5 py-6 transition-colors hover:bg-white sm:border-r lg:[&:nth-child(3n)]:border-r-0"
                >
                    <div class="flex items-center justify-between gap-5">
                        <div>
                            <h3 class="display-serif text-[32px]">{{ $family['name'] }}</h3>
                            <p class="mt-2 ui-label text-black/35">{{ $family['descriptor'] }}</p>
                        </div>
                        <span class="transition-transform group-hover:translate-x-1">→</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section id="materials" class="bg-white text-black">
    <div class="house-container py-14 lg:py-20">
        <div class="mb-10 grid gap-5 border-b border-black/10 pb-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="ui-label text-black/35">Raw Materials</p>
                <h2 class="mt-3 display-serif text-[46px] leading-[.94] sm:text-[58px]">The material wardrobe.</h2>
            </div>
            <p class="max-w-md text-sm leading-7 text-black/48">
                Each material is treated as texture, temperature and emotion — not just as a note on a pyramid.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($ingredients as $ingredient)
                @php
                    $custom = "images/ingredients/{$ingredient['slug']}-ingredient.webp";
                    $ingredientImage = file_exists(public_path($custom))
                        ? asset($custom)
                        : ($ingredient['fallback_image'] ?? null);
                @endphp

                <a
                    href="{{ route('ingredients.show', $ingredient['slug']) }}"
                    class="group relative min-h-[440px] overflow-hidden bg-[#171717] text-white"
                >
                    @if($ingredientImage)
                        <img
                            src="{{ $ingredientImage }}"
                            alt="{{ $ingredient['name'] }}"
                            class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.035]"
                            loading="lazy"
                        >
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-black/82 via-black/8 to-black/12"></div>

                    <div class="absolute inset-x-0 bottom-0 p-6">
                        <p class="ui-label text-white/48">{{ $ingredient['family'] }}</p>
                        <h3 class="mt-2 display-serif text-[42px] leading-none">{{ $ingredient['name'] }}</h3>
                        <p class="mt-3 ui-label text-white/50">{{ $ingredient['descriptor'] }}</p>

                        <div class="mt-6 flex items-center justify-between">
                            <span class="ui-label">Explore material →</span>
                            <span class="text-[10px] uppercase tracking-[.15em] text-white/40">
                                {{ $ingredient['products_count'] }} fragrances
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-[#efebe3] text-black">
    <div class="house-container grid gap-10 py-16 lg:grid-cols-[.72fr_1.28fr] lg:items-center lg:py-24">
        <div>
            <p class="ui-label text-black/35">From material to skin</p>
            <h2 class="mt-4 display-serif text-[46px] leading-[.94] sm:text-[58px]">Notes are only the beginning.</h2>
        </div>
        <div>
            <p class="max-w-2xl text-sm leading-7 text-black/52">
                A fragrance is shaped by proportion, contrast and wear. Use the material library to understand what you are drawn to, then explore the fragrances where those materials take on a complete identity.
            </p>
            <a href="{{ route('shop') }}" class="btn-solid mt-7">Explore all fragrances</a>
        </div>
    </div>
</section>

@endsection

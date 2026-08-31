@extends('layouts.store')

@php
    $heroImage = file_exists(public_path('images/about/about-hero.webp'))
        ? asset('images/about/about-hero.webp')
        : (config('storefront.campaigns.signature.image') ?? null);

    $craftImage = file_exists(public_path('images/about/craft-materials.webp'))
        ? asset('images/about/craft-materials.webp')
        : (config('storefront.campaigns.rose_material.image') ?? null);

    $ritualImage = file_exists(public_path('images/about/house-ritual.webp'))
        ? asset('images/about/house-ritual.webp')
        : (config('storefront.campaigns.light_studies.image') ?? null);

    $originImage = file_exists(public_path('images/about/house-origin.webp'))
        ? asset('images/about/house-origin.webp')
        : (config('storefront.campaigns.nocturnal.image') ?? null);

    $pageTitle = $page['title'] ?? 'Scents by Aamir';
    $intro = $page['intro'] ?? 'A fragrance house shaped by memory, material and the belief that scent should feel deeply personal.';
@endphp

@section('title', 'Our House — Scents by Aamir')
@section('description', $intro)

@section('content')

<section class="relative overflow-hidden bg-black pt-[100px] text-white">
    <div class="absolute inset-x-0 bottom-0 top-[100px]">
        @if($heroImage)
            <img
                src="{{ $heroImage }}"
                alt="Scents by Aamir house"
                class="h-full w-full object-cover object-center"
                fetchpriority="high"
            >
        @endif
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.94)_0%,rgba(0,0,0,.76)_38%,rgba(0,0,0,.18)_70%,rgba(0,0,0,.50)_100%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/72 via-transparent to-black/12"></div>
    </div>

    <div class="house-container relative flex min-h-[650px] items-end py-16 sm:min-h-[700px] lg:min-h-[760px] lg:items-center">
        <div class="max-w-[760px]" data-reveal>
            <div class="flex items-center gap-4">
                <span class="h-px w-10 bg-[#c9ad7a]"></span>
                <p class="ui-label text-white/55">Our House</p>
            </div>

            <h1 class="mt-6 max-w-[760px] display-serif text-[52px] leading-[.92] tracking-[-.035em] sm:text-[68px] lg:text-[80px]">
                Fragrance made personal.
            </h1>

            <p class="mt-5 max-w-[620px] display-serif text-[23px] leading-tight italic text-[#d6c19a] sm:text-[29px]">
                Memory, material and modern wearability.
            </p>

            <p class="mt-6 max-w-xl text-sm leading-7 text-white/64">
                {{ $intro }}
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#house-story" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Read our story</a>
                <a href="{{ route('shop') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">Explore fragrances</a>
            </div>
        </div>
    </div>
</section>

<section id="house-story" class="bg-white text-black">
    <div class="house-container grid gap-12 py-16 lg:grid-cols-[.52fr_1.48fr] lg:py-24">
        <div>
            <p class="ui-label text-black/35">The Beginning</p>
        </div>

        <div>
            <h2 class="max-w-5xl display-serif text-[44px] leading-[1.01] sm:text-[58px] lg:text-[66px]">
                We believe a fragrance should feel familiar before it feels impressive.
            </h2>

            <div class="mt-10 grid gap-8 border-t border-black/10 pt-8 sm:grid-cols-2">
                <p class="text-sm leading-7 text-black/55">
                    Scents by Aamir is built around the emotional side of fragrance: the place a note can recall, the mood a material can create, and the confidence of finding something that feels entirely your own.
                </p>

                <p class="text-sm leading-7 text-black/55">
                    Rather than treating perfume as a list of ingredients, the house brings together atmosphere, texture and wearability so each composition can become part of everyday memory.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#0d0d0d] text-white">
    <div class="house-container grid overflow-hidden lg:grid-cols-[1.1fr_.9fr]">
        <div class="relative min-h-[560px] lg:min-h-[720px]">
            @if($originImage)
                <img src="{{ $originImage }}" alt="Scents by Aamir origin story" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-black/5 to-black/25"></div>
        </div>

        <div class="flex items-center p-8 sm:p-12 lg:p-16">
            <div class="max-w-xl">
                <p class="ui-label text-[#c9ad7a]">Our Point of View</p>

                <h2 class="mt-5 display-serif text-[46px] leading-[.95] sm:text-[58px]">
                    Familiar materials. Reframed through atmosphere.
                </h2>

                <p class="mt-7 text-sm leading-7 text-white/55">
                    Oud can feel polished rather than heavy. Florals can feel architectural rather than decorative. Citrus can feel mineral and modern. The house is interested in those contrasts.
                </p>

                <a href="{{ route('ingredients') }}" class="btn-outline mt-8 border-white/35 text-white hover:bg-white hover:text-black">
                    Explore materials
                </a>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#f2eee6] text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="grid gap-4 lg:grid-cols-[.95fr_1.05fr]">
            <div class="relative min-h-[600px] overflow-hidden bg-[#d9d2c6]">
                @if($craftImage)
                    <img src="{{ $craftImage }}" alt="Fragrance materials and craft" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-7 text-white sm:p-10">
                    <p class="ui-label text-white/48">Material & Craft</p>
                    <h3 class="mt-3 max-w-lg display-serif text-[44px] leading-[.94] sm:text-[54px]">The material is only the beginning.</h3>
                </div>
            </div>

            <div class="grid gap-px bg-black/10 sm:grid-cols-2">
                @foreach([
                    ['01', 'Material', 'We begin with the character of the material: its temperature, texture and emotional register.'],
                    ['02', 'Contrast', 'Brightness against shadow, softness against structure, warmth against mineral clarity.'],
                    ['03', 'Wear', 'The final composition must live naturally on skin rather than exist only as an idea.'],
                    ['04', 'Memory', 'The most important result is what remains with the wearer after the fragrance settles.'],
                ] as [$index,$title,$copy])
                    <div class="flex min-h-[298px] flex-col justify-between bg-white p-7 sm:p-8">
                        <span class="ui-label text-black/28">{{ $index }}</span>
                        <div>
                            <h3 class="display-serif text-[38px]">{{ $title }}</h3>
                            <p class="mt-4 text-xs leading-6 text-black/48">{{ $copy }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="bg-white text-black">
    <div class="house-container grid gap-12 py-16 lg:grid-cols-[.78fr_1.22fr] lg:items-center lg:py-24">
        <div>
            <p class="ui-label text-black/35">House Values</p>

            <h2 class="mt-4 max-w-xl display-serif text-[46px] leading-[.95] sm:text-[58px]">
                Made to be worn, remembered and returned to.
            </h2>
        </div>

        <div class="divide-y divide-black/10 border-y border-black/10">
            @foreach([
                ['Authenticity', 'The house speaks in its own visual and fragrance language rather than imitating the identity of another brand.'],
                ['Wearability', 'Luxury should still belong in real life: on skin, through a day, and across different moments.'],
                ['Material Respect', 'Ingredients are treated as expressive building blocks, not decorative claims.'],
                ['Personal Discovery', 'There is no single correct fragrance. The goal is to help each wearer find their own.'],
            ] as [$title,$copy])
                <div class="grid gap-4 py-7 sm:grid-cols-[180px_1fr]">
                    <h3 class="display-serif text-[30px]">{{ $title }}</h3>
                    <p class="text-sm leading-7 text-black/50">{{ $copy }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="grid bg-[#111] text-white lg:grid-cols-2">
    <div class="flex min-h-[520px] items-center p-8 sm:p-12 lg:min-h-[620px] lg:p-16">
        <div class="max-w-xl">
            <p class="ui-label text-white/35">The Wearing Ritual</p>
            <h2 class="mt-4 display-serif text-[46px] leading-[.95] sm:text-[58px]">Choose slowly. Wear completely.</h2>
            <p class="mt-6 text-sm leading-7 text-white/52">
                Fragrance becomes meaningful through repetition. Give it time on skin, notice the dry-down, and let the composition become part of your own rhythm.
            </p>
            <a href="{{ route('finder') }}" class="btn-outline mt-8 border-white/35 text-white hover:bg-white hover:text-black">
                Find your fragrance
            </a>
        </div>
    </div>

    <div class="relative min-h-[520px] lg:min-h-[620px]">
        @if($ritualImage)
            <img src="{{ $ritualImage }}" alt="Scents by Aamir wearing ritual" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
        @endif
        <div class="absolute inset-0 bg-black/10"></div>
    </div>
</section>

<section class="bg-[#f7f6f2] text-black">
    <div class="house-container flex flex-col gap-8 py-16 sm:flex-row sm:items-center sm:justify-between lg:py-20">
        <div>
            <p class="ui-label text-black/35">Continue the house</p>
            <h2 class="mt-3 max-w-2xl display-serif text-[42px] leading-[.95] sm:text-[54px]">Stories, materials and fragrance culture.</h2>
        </div>
        <a href="{{ route('journal') }}" class="btn-solid shrink-0">Enter the Journal</a>
    </div>
</section>

@endsection

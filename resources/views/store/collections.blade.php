@extends('layouts.store')

@php $campaigns = config('storefront.campaigns'); @endphp

@section('title', 'Collections — Scents by Aamir')

@section('content')
<section class="bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container py-14 lg:py-20">
        <p class="ui-label text-black/35">Scents by Aamir</p>
        <div class="mt-4 grid gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
            <h1 class="display-serif text-6xl leading-[.88] sm:text-8xl lg:text-[8.5rem]">Collections</h1>
            <p class="max-w-lg text-sm leading-7 text-black/52">Each collection is a shared atmosphere: a way of grouping scent through light, material and emotional temperature.</p>
        </div>
    </div>
</section>

<section class="bg-[#efebe3]">
    <div class="house-container py-12 lg:py-16">
        <div class="grid gap-4 lg:grid-cols-[1.25fr_.75fr]">
            <a href="{{ route('collections.show','signature-worlds') }}" class="relative min-h-[760px] overflow-hidden text-white">
                <img src="{{ $campaigns['signature']['image'] }}" alt="Signature Worlds" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-black/22"></div>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent p-8 pt-32 sm:p-10">
                    <p class="ui-label text-white/58">Collection 01</p>
                    <h2 class="mt-3 display-serif text-6xl leading-[.9] sm:text-8xl">Signature Worlds</h2>
                    <p class="mt-5 max-w-lg text-sm leading-6 text-white/68">The essential wardrobe: four distinct atmospheres that define the house.</p>
                    <span class="mt-7 inline-block ui-label">Explore →</span>
                </div>
            </a>

            <div class="grid gap-4">
                <a href="{{ route('collections.show','nocturnal') }}" class="relative min-h-[370px] overflow-hidden text-white">
                    <img src="{{ $campaigns['nocturnal']['image'] }}" alt="Nocturnal" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-black/36"></div>
                    <div class="absolute inset-x-0 bottom-0 p-7">
                        <p class="ui-label text-white/50">Collection 02</p>
                        <h3 class="mt-2 display-serif text-5xl">Nocturnal</h3>
                    </div>
                </a>

                <a href="{{ route('collections.show','light-studies') }}" class="relative min-h-[370px] overflow-hidden text-white">
                    <img src="{{ $campaigns['light_studies']['image'] }}" alt="Light Studies" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-black/18"></div>
                    <div class="absolute inset-x-0 bottom-0 p-7">
                        <p class="ui-label text-white/60">Collection 03</p>
                        <h3 class="mt-2 display-serif text-5xl">Light Studies</h3>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="house-container grid gap-12 py-20 lg:grid-cols-[.68fr_1.32fr] lg:py-28">
        <div>
            <p class="ui-label text-black/35">Material Editions</p>
            <h2 class="mt-4 display-serif text-6xl leading-[.9]">One material. Four interpretations.</h2>
        </div>

        <div class="divide-y divide-black/10 border-y border-black/10">
            @foreach([
                ['Oud','Dense · Smoky · Polished'],
                ['Amber','Warm · Resinous · Skin-like'],
                ['Musk','Soft · Clean · Intimate'],
                ['Woods','Dry · Mineral · Textural'],
            ] as [$name,$copy])
                <a href="{{ route('shop') }}" class="group grid grid-cols-[1fr_auto] items-center gap-6 py-7">
                    <div>
                        <h3 class="display-serif text-4xl">{{ $name }}</h3>
                        <p class="mt-1 ui-label text-black/38">{{ $copy }}</p>
                    </div>
                    <span class="text-xl transition group-hover:translate-x-1">→</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection

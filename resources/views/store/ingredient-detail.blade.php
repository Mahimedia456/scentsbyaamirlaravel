@extends('layouts.store')

@php
    $slug = $slug ?? request()->route('slug');
    $ingredients = config('storefront.ingredients');
    $products = config('storefront.products');
    $ingredient = $ingredients[$slug];
@endphp

@section('title', $ingredient['name'].' — Ingredients — Scents by Aamir')

@section('content')
<section class="relative min-h-[82vh] overflow-hidden pt-[100px] text-white">
    <img src="{{ $ingredient['image'] }}" alt="{{ $ingredient['name'] }}" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-black/38"></div>
    <div class="house-container relative flex min-h-[calc(82vh-100px)] items-end py-10 lg:py-14">
        <div class="grid w-full gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
            <div>
                <p class="ui-label text-white/55">{{ $ingredient['family'] }}</p>
                <h1 class="mt-3 display-serif text-7xl leading-[.86] sm:text-9xl">{{ $ingredient['name'] }}</h1>
            </div>
            <p class="ui-label text-white/55 lg:text-right">{{ $ingredient['descriptor'] }}</p>
        </div>
    </div>
</section>

<section class="bg-white text-black">
    <div class="house-container grid gap-10 py-20 lg:grid-cols-[.65fr_1.35fr] lg:py-28">
        <p class="ui-label text-black/35">Material Study</p>
        <div>
            <h2 class="display-serif max-w-5xl text-5xl leading-[.95] sm:text-7xl">{{ $ingredient['copy'] }}</h2>
        </div>
    </div>
</section>

<section class="bg-[#f7f6f2] text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="mb-10 flex items-end justify-between">
            <div>
                <p class="ui-label text-black/35">Found In</p>
                <h2 class="mt-3 display-serif text-5xl sm:text-6xl">Fragrances</h2>
            </div>
        </div>

        <div class="grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($ingredient['products'] as $productSlug)
                @php $p=$products[$productSlug]; @endphp
                <x-house.product-card
                    :slug="$productSlug"
                    :name="$p['name']"
                    :family="$p['family']"
                    :price="$p['price']"
                    :index="str_pad($loop->iteration,2,'0',STR_PAD_LEFT)"
                    :badge="$p['badge']"
                    :image="$p['image']"
                />
            @endforeach
        </div>
    </div>
</section>
@endsection

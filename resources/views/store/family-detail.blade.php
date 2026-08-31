@extends('layouts.store')

@section('title', $family['name'].' Fragrances — Scents by Aamir')
@section('description', $family['copy'])

@section('content')

<section class="bg-black pt-[100px] text-white">
    <div class="house-container grid min-h-[500px] gap-10 py-16 lg:grid-cols-[.7fr_1.3fr] lg:items-end lg:py-24">
        <div>
            <p class="ui-label text-[#d2b77f]">Fragrance Family</p>
            <h1 class="mt-4 display-serif text-[52px] leading-[.9] sm:text-[68px] lg:text-[78px]">{{ $family['name'] }}</h1>
        </div>

        <div class="max-w-2xl lg:justify-self-end">
            <p class="display-serif text-[24px] italic text-[#d6c19a]">{{ $family['descriptor'] }}</p>
            <p class="mt-6 text-sm leading-7 text-white/62">{{ $family['copy'] }}</p>
        </div>
    </div>
</section>

<section class="bg-[#f7f6f2] text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-6 border-b border-black/10 pb-7">
            <div>
                <p class="ui-label text-black/35">The Edit</p>
                <h2 class="mt-3 display-serif text-[44px] sm:text-[56px]">{{ $family['name'] }} fragrances</h2>
            </div>
            <p class="ui-label text-black/38">{{ $products->count() }} fragrances</p>
        </div>

        <div class="grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($products as $product)
                <x-house.product-card
                    :product="$product"
                    :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                    :tone="in_array($product['slug'] ?? '', ['dark-seduction','smoky-chic']) ? 'dark' : 'light'"
                />
            @empty
                <div class="col-span-full border border-black/10 bg-white px-6 py-16 text-center">
                    <h3 class="display-serif text-4xl">No fragrances are mapped to this family yet.</h3>
                    <p class="mx-auto mt-4 max-w-lg text-sm leading-7 text-black/45">
                        Product notes and categories can be refined later in admin; matching fragrances will then appear automatically.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="bg-white text-black">
    <div class="house-container grid gap-8 py-16 lg:grid-cols-[1fr_auto] lg:items-center">
        <div>
            <p class="ui-label text-black/35">Discover another way</p>
            <h2 class="mt-3 display-serif text-[42px] leading-[.95] sm:text-[54px]">Explore materials behind the family.</h2>
        </div>
        <a href="{{ route('ingredients') }}" class="btn-outline">Ingredient library</a>
    </div>
</section>

@endsection

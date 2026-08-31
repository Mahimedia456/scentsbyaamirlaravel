@extends('layouts.store')

@php $ingredients = config('storefront.ingredients'); @endphp

@section('title', 'Ingredients — Scents by Aamir')

@section('content')
<section class="bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container py-14 lg:py-20">
        <p class="ui-label text-black/35">Material Library</p>
        <div class="mt-4 grid gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
            <h1 class="display-serif text-6xl leading-[.88] sm:text-8xl lg:text-[8.5rem]">Ingredients</h1>
            <p class="max-w-lg text-sm leading-7 text-black/52">
                The raw materials and accords that shape the house — presented as texture, atmosphere and function.
            </p>
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="house-container py-12 lg:py-16">
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($ingredients as $slug => $ingredient)
                <a href="{{ route('ingredients.show',$slug) }}" class="group relative min-h-[560px] overflow-hidden text-white">
                    <img src="{{ $ingredient['image'] }}" alt="{{ $ingredient['name'] }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]">
                    <div class="absolute inset-0 bg-black/24"></div>
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-7 pt-28 sm:p-9">
                        <p class="ui-label text-white/55">{{ $ingredient['family'] }}</p>
                        <h2 class="mt-2 display-serif text-6xl">{{ $ingredient['name'] }}</h2>
                        <p class="mt-3 ui-label text-white/55">{{ $ingredient['descriptor'] }}</p>
                        <span class="mt-7 inline-block ui-label">Explore material →</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection

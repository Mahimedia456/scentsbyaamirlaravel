@extends('layouts.store')

@php $campaigns = config('storefront.campaigns'); @endphp

@section('title', 'Gifting — Scents by Aamir')

@section('content')
<section class="relative min-h-[84vh] overflow-hidden pt-[100px] text-white">
    <img src="{{ $campaigns['rose_material']['image'] }}" alt="Scents by Aamir gifting" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-black/34"></div>

    <div class="house-container relative flex min-h-[calc(84vh-100px)] items-end py-10 lg:py-14">
        <div class="grid w-full gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
            <div>
                <p class="ui-label text-white/55">Gifting</p>
                <h1 class="mt-3 display-serif text-7xl leading-[.86] sm:text-9xl">A fragrance, made personal.</h1>
            </div>
            <p class="max-w-lg text-sm leading-7 text-white/65 lg:justify-self-end">
                Gift wrapping, personalized messages and considered recommendations for moments that deserve more than a standard box.
            </p>
        </div>
    </div>
</section>

<section class="bg-white text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="grid gap-4 lg:grid-cols-3">
            @foreach([
                ['Signature Gift Wrap','Minimal house wrapping with a premium presentation and fragrance-safe packaging.'],
                ['Personalized Message','Add a private message to accompany the fragrance.'],
                ['Guided Selection','Use the fragrance finder to choose by mood, intensity and occasion.']
            ] as [$title,$copy])
                <div class="min-h-[340px] border border-black/10 bg-[#f7f6f2] p-7 sm:p-9">
                    <p class="ui-label text-black/35">Service</p>
                    <h2 class="mt-16 display-serif text-5xl leading-[.95]">{{ $title }}</h2>
                    <p class="mt-5 text-sm leading-7 text-black/50">{{ $copy }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="grid bg-black text-white lg:grid-cols-2">
    <div class="relative min-h-[560px] overflow-hidden">
        <img loading="lazy" decoding="async" src="{{ $campaigns['finder']['image'] }}" alt="Gift consultation" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-black/20"></div>
    </div>
    <div class="flex min-h-[560px] items-center p-8 sm:p-12 lg:p-16">
        <div>
            <p class="ui-label text-white/40">Need guidance?</p>
            <h2 class="mt-4 display-serif text-6xl leading-[.9] sm:text-7xl">Choose by feeling, not by guesswork.</h2>
            <p class="mt-6 max-w-lg text-sm leading-7 text-white/55">The finder creates a short edit based on mood, intensity and occasion.</p>
            <a href="{{ route('finder') }}" class="btn-outline mt-8 border-white text-white hover:bg-white hover:text-black">Start fragrance finder</a>
        </div>
    </div>
</section>
@endsection

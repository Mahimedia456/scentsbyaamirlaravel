@extends('layouts.store')

@section('title', 'Services — Scents by Aamir')

@section('content')
<section class="bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container py-14 lg:py-20">
        <p class="ui-label text-black/35">The House</p>
        <div class="mt-4 grid gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
            <h1 class="display-serif text-6xl leading-[.88] sm:text-8xl lg:text-[8.5rem]">Services</h1>
            <p class="max-w-lg text-sm leading-7 text-black/52">
                Premium support around discovery, gifting and delivery — designed to feel as considered as the fragrance itself.
            </p>
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="house-container py-12 lg:py-16">
        <div class="divide-y divide-black/10 border-y border-black/10">
            @foreach([
                ['01','Complimentary Delivery','Available on selected orders and campaigns.'],
                ['02','Gift Wrapping','House presentation designed for gifting occasions.'],
                ['03','Personalized Message','Add a note during checkout.'],
                ['04','Fragrance Finder','A guided discovery flow based on mood and occasion.'],
                ['05','Order Support','Order history, tracking shell and future support integration.'],
                ['06','Secure Checkout','Structured checkout UI prepared for live payment gateway integration.']
            ] as [$index,$title,$copy])
                <div class="grid gap-5 py-8 sm:grid-cols-[70px_1fr_1fr] sm:items-start">
                    <span class="ui-label text-black/30">{{ $index }}</span>
                    <h2 class="display-serif text-4xl">{{ $title }}</h2>
                    <p class="max-w-xl text-sm leading-7 text-black/50">{{ $copy }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>


<section class="bg-[#f7f6f2] text-black">
    <div class="house-container py-14 lg:py-20">
        <p class="ui-label text-black/35">Explore customer services</p>
        <div class="mt-6 grid gap-px bg-black/10 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('shipping') }}" class="bg-white p-6 hover:bg-[#efebe3]">
                <h3 class="display-serif text-3xl">Shipping</h3>
                <span class="mt-5 inline-block text-link">View →</span>
            </a>
            <a href="{{ route('returns') }}" class="bg-white p-6 hover:bg-[#efebe3]">
                <h3 class="display-serif text-3xl">Returns</h3>
                <span class="mt-5 inline-block text-link">View →</span>
            </a>
            <a href="{{ route('gift-wrapping') }}" class="bg-white p-6 hover:bg-[#efebe3]">
                <h3 class="display-serif text-3xl">Gift Wrapping</h3>
                <span class="mt-5 inline-block text-link">View →</span>
            </a>
            <a href="{{ route('contact') }}" class="bg-white p-6 hover:bg-[#efebe3]">
                <h3 class="display-serif text-3xl">Support</h3>
                <span class="mt-5 inline-block text-link">View →</span>
            </a>
        </div>
    </div>
</section>

<section class="bg-black text-white">
    <div class="house-container grid gap-10 py-20 lg:grid-cols-[1.25fr_.75fr] lg:items-end lg:py-28">
        <h2 class="display-serif max-w-5xl text-6xl leading-[.9] sm:text-8xl">Service should feel invisible until you need it.</h2>
        <div>
            <p class="text-sm leading-7 text-white/55">Backend integrations for support, payment, order tracking and customer service will connect later without changing this frontend architecture.</p>
            <a href="{{ route('gifting') }}" class="text-link mt-7">Explore gifting →</a>
        </div>
    </div>
</section>
@endsection

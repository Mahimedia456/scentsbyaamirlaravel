@extends('layouts.store')

@php
    $testerBoxes = collect($testerBoxes ?? []);
    $giftEdit = collect($giftEdit ?? []);

    $heroImage = file_exists(public_path('images/gifting/gifting-hero.webp'))
        ? asset('images/gifting/gifting-hero.webp')
        : (config('storefront.campaigns.rose_material.image') ?? null);

    $testerImage = file_exists(public_path('images/gifting/tester-box-editorial.webp'))
        ? asset('images/gifting/tester-box-editorial.webp')
        : (config('storefront.campaigns.light_studies.image') ?? null);

    $wrapImage = file_exists(public_path('images/gifting/gift-wrap.webp'))
        ? asset('images/gifting/gift-wrap.webp')
        : (config('storefront.campaigns.signature.image') ?? null);
@endphp

@section('title', 'Gifting & Discovery Sets — Scents by Aamir')
@section('description', 'Gift fragrance, discovery sets and tester boxes from Scents by Aamir.')

@section('content')

<section class="relative overflow-hidden bg-black pt-[100px] text-white">
    <div class="absolute inset-x-0 bottom-0 top-[100px]">
        @if($heroImage)
            <img src="{{ $heroImage }}" alt="Scents by Aamir gifting" class="h-full w-full object-cover object-center" fetchpriority="high">
        @endif

        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.94)_0%,rgba(0,0,0,.78)_38%,rgba(0,0,0,.18)_70%,rgba(0,0,0,.48)_100%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/68 via-transparent to-black/12"></div>
    </div>

    <div class="house-container relative flex min-h-[620px] items-end py-16 sm:min-h-[680px] lg:min-h-[720px] lg:items-center">
        <div class="max-w-[720px]">
            <div class="flex items-center gap-4">
                <span class="h-px w-10 bg-[#c9ad7a]"></span>
                <p class="ui-label text-white/55">Gifting</p>
            </div>

            <h1 class="mt-6 max-w-[720px] display-serif text-[50px] leading-[.92] tracking-[-.035em] sm:text-[66px] lg:text-[78px]">
                A fragrance, made personal.
            </h1>

            <p class="mt-5 max-w-[620px] display-serif text-[22px] italic leading-tight text-[#d6c19a] sm:text-[28px]">
                Give one fragrance — or give the experience of discovering it.
            </p>

            <p class="mt-6 max-w-xl text-sm leading-7 text-white/62">
                Discovery boxes, tester sets, full-size fragrance and thoughtful presentation for moments that deserve more than a standard gift.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                @if($testerBoxes->isNotEmpty())
                    <a href="#discovery-sets" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Shop discovery sets</a>
                @else
                    <a href="{{ route('shop') }}" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Shop fragrances</a>
                @endif

                <a href="{{ route('finder') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">
                    Find a gift
                </a>
            </div>
        </div>
    </div>
</section>

<section class="border-b border-black/10 bg-[#f7f6f2] text-black">
    <div class="house-container grid sm:grid-cols-3">
        @foreach([
            ['Discovery First','Tester boxes make choosing a full-size fragrance easier.'],
            ['Gift Presentation','A considered house presentation for fragrance gifting.'],
            ['Guided Selection','Use the finder when you know the person but not the perfume.'],
        ] as [$title,$copy])
            <div class="border-b border-black/10 px-6 py-7 sm:border-r sm:border-b-0 sm:px-8 sm:py-9">
                <p class="ui-label text-black/30">House Service</p>
                <h2 class="mt-3 display-serif text-[30px]">{{ $title }}</h2>
                <p class="mt-3 text-xs leading-6 text-black/45">{{ $copy }}</p>
            </div>
        @endforeach
    </div>
</section>

@if($testerBoxes->isNotEmpty())
<section id="discovery-sets" class="bg-white text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="grid gap-10 border-b border-black/10 pb-9 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="ui-label text-black/35">Discovery Sets</p>
                <h2 class="mt-3 display-serif text-[46px] leading-[.94] sm:text-[58px]">Try the wardrobe first.</h2>
                <p class="mt-5 max-w-xl text-sm leading-7 text-black/48">
                    Tester boxes let the wearer experience several fragrances on skin before choosing a full bottle.
                </p>
            </div>

            <span class="ui-label text-black/35">{{ $testerBoxes->count() }} sets</span>
        </div>

        <div class="mt-10 grid gap-x-5 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($testerBoxes as $product)
                <x-house.product-card
                    :product="$product"
                    :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                />
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="bg-[#efebe3] text-black">
    <div class="house-container grid gap-4 py-12 lg:grid-cols-[1.12fr_.88fr] lg:py-16">
        <div class="relative min-h-[580px] overflow-hidden bg-[#d8d2c8]">
            @if($testerImage)
                <img src="{{ $testerImage }}" alt="Fragrance discovery experience" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
            @endif

            <div class="absolute inset-0 bg-gradient-to-t from-black/62 via-transparent to-transparent"></div>

            <div class="absolute inset-x-0 bottom-0 p-7 text-white sm:p-10">
                <p class="ui-label text-white/48">Discovery</p>
                <h2 class="mt-3 max-w-xl display-serif text-[44px] leading-[.94] sm:text-[54px]">The gift of finding the right scent.</h2>
            </div>
        </div>

        <div class="grid gap-px bg-black/10">
            @foreach([
                ['01','Wear on skin','A blotter introduces a fragrance. Skin reveals how it actually belongs to the wearer.'],
                ['02','Give it time','Move beyond the opening and allow the heart and dry-down to appear.'],
                ['03','Return to it','The right fragrance is often the one you keep wanting to smell again.'],
            ] as [$index,$title,$copy])
                <div class="flex min-h-[190px] flex-col justify-between bg-white p-7 sm:p-8">
                    <span class="ui-label text-black/28">{{ $index }}</span>
                    <div class="mt-8">
                        <h3 class="display-serif text-[34px]">{{ $title }}</h3>
                        <p class="mt-3 text-xs leading-6 text-black/48">{{ $copy }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@if($giftEdit->isNotEmpty())
<section class="bg-[#f7f6f2] text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="flex flex-wrap items-end justify-between gap-6 border-b border-black/10 pb-7">
            <div>
                <p class="ui-label text-black/35">Full-size gifting</p>
                <h2 class="mt-3 display-serif text-[46px] leading-[.94] sm:text-[58px]">House gift edit.</h2>
            </div>

            <a href="{{ route('shop') }}" class="text-link">Explore all fragrances →</a>
        </div>

        <div class="mt-10 grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach($giftEdit as $product)
                <x-house.product-card
                    :product="$product"
                    :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                />
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="grid bg-[#101010] text-white lg:grid-cols-2">
    <div class="relative min-h-[520px] overflow-hidden lg:min-h-[640px]">
        @if($wrapImage)
            <img src="{{ $wrapImage }}" alt="Scents by Aamir gift presentation" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
        @endif
        <div class="absolute inset-0 bg-black/12"></div>
    </div>

    <div class="flex min-h-[520px] items-center p-8 sm:p-12 lg:min-h-[640px] lg:p-16">
        <div class="max-w-xl">
            <p class="ui-label text-white/35">Presentation</p>

            <h2 class="mt-4 display-serif text-[46px] leading-[.95] sm:text-[58px]">
                The last detail matters.
            </h2>

            <p class="mt-6 text-sm leading-7 text-white/52">
                A gift should feel intentional before it is even opened. Presentation, a personal message and the fragrance itself should belong to the same experience.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('gift-wrapping') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">
                    Gift wrapping
                </a>

                <a href="{{ route('personalized-message') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">
                    Personal message
                </a>
            </div>
        </div>
    </div>
</section>

<section class="bg-white text-black">
    <div class="house-container flex flex-col gap-8 py-16 sm:flex-row sm:items-center sm:justify-between lg:py-20">
        <div>
            <p class="ui-label text-black/35">Not sure where to begin?</p>
            <h2 class="mt-3 max-w-2xl display-serif text-[42px] leading-[.95] sm:text-[54px]">
                Choose by feeling, not by guesswork.
            </h2>
        </div>

        <a href="{{ route('finder') }}" class="btn-solid shrink-0">Start the fragrance finder</a>
    </div>
</section>

@endsection

@extends('layouts.store')

@php
    $campaigns = config('storefront.campaigns');
    $collections = collect($dbCollections ?? [])->values();

    $collectionMeta = [
        'signature-worlds' => [
            'eyebrow' => 'Collection 01',
            'title' => 'Signature Worlds',
            'copy' => 'A defining wardrobe of scent: polished, expressive and unmistakably Scents by Aamir.',
            'image' => file_exists(public_path('images/collections/signature-worlds.webp'))
                ? asset('images/collections/signature-worlds.webp')
                : ($campaigns['signature']['image'] ?? null),
            'tone' => 'dark',
        ],
        'nocturnal' => [
            'eyebrow' => 'Collection 02',
            'title' => 'Nocturnal',
            'copy' => 'Smoke, woods, resin and shadow. Fragrance designed for the hours after daylight.',
            'image' => file_exists(public_path('images/collections/nocturnal.webp'))
                ? asset('images/collections/nocturnal.webp')
                : ($campaigns['nocturnal']['image'] ?? null),
            'tone' => 'dark',
        ],
        'light-studies' => [
            'eyebrow' => 'Collection 03',
            'title' => 'Light Studies',
            'copy' => 'Citrus, air, musk and clean woods interpreted with clarity and restraint.',
            'image' => file_exists(public_path('images/collections/light-studies.webp'))
                ? asset('images/collections/light-studies.webp')
                : ($campaigns['light_studies']['image'] ?? null),
            'tone' => 'light',
        ],
    ];

    $heroImage = file_exists(public_path('images/collections/collections-hero.webp'))
        ? asset('images/collections/collections-hero.webp')
        : ($campaigns['rose_material']['image'] ?? $campaigns['signature']['image'] ?? null);

    $displayCollections = $collections->map(function ($collection, $index) use ($collectionMeta, $campaigns) {
        $slug = $collection['slug'] ?? '';
        $meta = $collectionMeta[$slug] ?? [
            'eyebrow' => 'House Collection',
            'title' => $collection['name'] ?? 'Collection',
            'copy' => $collection['description'] ?? 'A curated fragrance world shaped through material, mood and atmosphere.',
            'image' => $campaigns['signature']['image'] ?? null,
            'tone' => 'dark',
        ];

        return array_merge($collection, $meta, [
            'title' => $collection['name'] ?? $meta['title'],
            'copy' => $collection['description'] ?: $meta['copy'],
            'eyebrow' => $meta['eyebrow'] ?? ('Collection '.str_pad($index + 1, 2, '0', STR_PAD_LEFT)),
        ]);
    });

    if ($displayCollections->isEmpty()) {
        $displayCollections = collect($collectionMeta)->map(function ($meta, $slug) {
            return array_merge($meta, [
                'slug' => $slug,
                'name' => $meta['title'],
                'description' => $meta['copy'],
                'products_count' => 0,
            ]);
        })->values();
    }
@endphp

@section('title', 'Collections — Scents by Aamir')
@section('description', 'Explore Scents by Aamir fragrance collections, scent families and edits for men, women and unisex wear.')

@section('content')

{{-- COLLECTION HERO --}}
<section class="relative overflow-hidden bg-black pt-[100px] text-white">
    <div class="absolute inset-x-0 bottom-0 top-[100px]">
        @if($heroImage)
            <img src="{{ $heroImage }}" alt="Scents by Aamir collections" class="h-full w-full object-cover object-center" fetchpriority="high">
        @endif
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.92)_0%,rgba(0,0,0,.76)_34%,rgba(0,0,0,.28)_62%,rgba(0,0,0,.54)_100%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-black/10"></div>
    </div>

    <div class="house-container relative flex min-h-[620px] items-end py-16 sm:min-h-[680px] lg:min-h-[720px] lg:items-center">
        <div class="max-w-[700px]" data-reveal>
            <div class="flex items-center gap-4">
                <span class="h-px w-10 bg-[#c9ad7a]"></span>
                <p class="ui-label text-white/58">The Fragrance Wardrobe</p>
            </div>

            <h1 class="mt-6 display-serif text-[52px] leading-[.92] tracking-[-.035em] sm:text-[66px] lg:text-[78px]">
                Collections
            </h1>

            <p class="mt-5 max-w-[590px] display-serif text-[23px] leading-tight italic text-[#d6c19a] sm:text-[28px]">
                Scent grouped by mood, material and the way you want to wear it.
            </p>

            <p class="mt-6 max-w-xl text-sm leading-7 text-white/64">
                Move through signature compositions, darker evening worlds and luminous fresh studies — or begin with who the fragrance is for.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#house-collections" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Explore collections</a>
                <a href="{{ route('shop') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">Shop all fragrances</a>
            </div>
        </div>
    </div>
</section>

{{-- AUDIENCE NAVIGATION --}}
<section class="border-b border-black/10 bg-[#f7f6f2] text-black">
    <div class="house-container">
        <div class="grid grid-cols-2 divide-x divide-black/10 border-x border-black/10 sm:grid-cols-4">
            @foreach([
                ['All Fragrances', route('shop'), 'The complete house'],
                ['Men', route('shop', ['audience' => 'men']), 'Structured · Refined'],
                ['Women', route('shop', ['audience' => 'women']), 'Radiant · Expressive'],
                ['Unisex', route('shop', ['audience' => 'unisex']), 'Fluid · Individual'],
            ] as [$label, $href, $copy])
                <a href="{{ $href }}" class="group px-5 py-7 transition-colors hover:bg-white sm:px-7 sm:py-9">
                    <p class="ui-label text-black/72">{{ $label }}</p>
                    <p class="mt-2 text-[11px] leading-5 text-black/40">{{ $copy }}</p>
                    <span class="mt-5 block text-sm transition-transform group-hover:translate-x-1">→</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- PRIMARY COLLECTIONS --}}
<section id="house-collections" class="bg-[#efebe3] text-black">
    <div class="house-container py-14 lg:py-20">
        <div class="mb-10 grid gap-6 border-b border-black/10 pb-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="ui-label text-black/38">House Collections</p>
                <h2 class="mt-3 display-serif text-[46px] leading-[.94] sm:text-[58px]">Three ways into the house.</h2>
            </div>
            <p class="max-w-md text-sm leading-7 text-black/50">
                Collections are editorial worlds, not rigid categories. Each brings together fragrances that share a particular atmosphere.
            </p>
        </div>

        @php
            $first = $displayCollections->get(0);
            $second = $displayCollections->get(1);
            $third = $displayCollections->get(2);
        @endphp

        <div class="grid gap-4 lg:grid-cols-[1.18fr_.82fr]">
            @if($first)
                <a href="{{ route('collections.show', $first['slug']) }}" class="sba-collection-card relative min-h-[600px] overflow-hidden bg-[#111] text-white lg:min-h-[700px]">
                    @if($first['image'])
                        <img src="{{ $first['image'] }}" alt="{{ $first['title'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/82 via-black/14 to-black/12"></div>
                    <div class="absolute inset-x-0 bottom-0 p-7 sm:p-10 lg:p-12">
                        <div class="flex items-center justify-between gap-4">
                            <p class="ui-label text-[#d4bd91]">{{ $first['eyebrow'] }}</p>
                            <p class="ui-label text-white/45">{{ $first['products_count'] ?? 0 }} fragrances</p>
                        </div>
                        <h3 class="mt-4 max-w-[650px] display-serif text-[48px] leading-[.92] sm:text-[62px]">{{ $first['title'] }}</h3>
                        <p class="mt-5 max-w-lg text-sm leading-7 text-white/68">{{ $first['copy'] }}</p>
                        <span class="mt-7 inline-block ui-label">Enter collection →</span>
                    </div>
                </a>
            @endif

            <div class="grid gap-4">
                @foreach(collect([$second, $third])->filter() as $item)
                    <a href="{{ route('collections.show', $item['slug']) }}" class="sba-collection-card relative min-h-[290px] overflow-hidden bg-[#151515] text-white lg:min-h-[342px]">
                        @if($item['image'])
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                        @endif
                        <div class="absolute inset-0 bg-black/38"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/72 via-transparent to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-7 sm:p-8">
                            <div class="flex items-center justify-between gap-4">
                                <p class="ui-label text-white/52">{{ $item['eyebrow'] }}</p>
                                <p class="ui-label text-white/38">{{ $item['products_count'] ?? 0 }} fragrances</p>
                            </div>
                            <h3 class="mt-3 display-serif text-[42px] leading-[.94] sm:text-[48px]">{{ $item['title'] }}</h3>
                            <span class="mt-5 inline-block ui-label">Explore →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        @if($displayCollections->count() > 3)
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($displayCollections->slice(3) as $item)
                    <a href="{{ route('collections.show', $item['slug']) }}" class="group border border-black/10 bg-[#f7f6f2] p-7 transition-colors hover:bg-white">
                        <p class="ui-label text-black/35">{{ $item['products_count'] ?? 0 }} fragrances</p>
                        <h3 class="mt-4 display-serif text-4xl">{{ $item['title'] }}</h3>
                        <p class="mt-4 line-clamp-3 text-sm leading-6 text-black/48">{{ $item['copy'] }}</p>
                        <span class="mt-7 inline-block text-sm transition-transform group-hover:translate-x-1">Explore →</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- MATERIAL / FAMILY EDITS --}}
<section class="bg-white text-black">
    <div class="house-container grid gap-12 py-18 lg:grid-cols-[.72fr_1.28fr] lg:py-24">
        <div>
            <p class="ui-label text-black/35">Fragrance Families</p>
            <h2 class="mt-4 max-w-xl display-serif text-[46px] leading-[.94] sm:text-[58px]">
                Follow a material. Find a mood.
            </h2>
            <p class="mt-6 max-w-md text-sm leading-7 text-black/50">
                Explore the catalogue through the notes and textures that shape a fragrance rather than by name alone.
            </p>
            <a href="{{ route('ingredients') }}" class="text-link mt-7 inline-block">Explore ingredients →</a>
        </div>

        <div class="divide-y divide-black/10 border-y border-black/10">
            @foreach([
                ['Oud', 'Dense · Smoky · Polished', route('shop', ['family' => 'oud'])],
                ['Floral', 'Petal · Radiant · Expressive', route('shop', ['family' => 'floral'])],
                ['Fresh', 'Citrus · Air · Mineral', route('shop', ['family' => 'fresh'])],
                ['Woody', 'Dry · Textural · Grounded', route('shop', ['family' => 'woody'])],
                ['Amber', 'Warm · Resinous · Addictive', route('shop', ['family' => 'amber'])],
            ] as [$name, $copy, $href])
                <a href="{{ $href }}" class="group grid grid-cols-[1fr_auto] items-center gap-6 py-7">
                    <div>
                        <h3 class="display-serif text-[36px]">{{ $name }}</h3>
                        <p class="mt-1 ui-label text-black/38">{{ $copy }}</p>
                    </div>
                    <span class="text-xl transition-transform group-hover:translate-x-1">→</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- CLOSING --}}
<section class="bg-black text-white">
    <div class="house-container grid gap-10 py-18 lg:grid-cols-[1fr_auto] lg:items-center lg:py-24">
        <div>
            <p class="ui-label text-white/40">The complete wardrobe</p>
            <h2 class="mt-4 max-w-3xl display-serif text-[46px] leading-[.94] sm:text-[60px]">
                Begin with a collection. End with something personal.
            </h2>
        </div>
        <a href="{{ route('finder') }}" class="line-button border-white text-white hover:bg-white hover:text-black">Find your scent</a>
    </div>
</section>

@endsection

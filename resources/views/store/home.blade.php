@extends('layouts.store')

@php
    $campaigns = config('storefront.campaigns');
    $products = ($homeProducts ?? $featuredProducts ?? collect())->values();

    if ($products->isEmpty()) {
        $products = collect(config('storefront.products'))
            ->map(fn ($product, $slug) => array_merge($product, ['slug' => $slug]))
            ->values();
    }

    $imageProducts = $products
        ->filter(fn ($product) => !empty($product['image']) || !empty($product['world_image']))
        ->values();

    $productAt = fn (int $index) => $imageProducts->get($index) ?? $products->get($index) ?? $products->first() ?? [];

    $productBySlug = function (string $slug, int $fallbackIndex = 0) use ($products, $productAt) {
        return $products->first(fn ($product) => ($product['slug'] ?? null) === $slug) ?? $productAt($fallbackIndex);
    };

    $customImage = fn (string $relative, ?string $fallback = null) =>
        file_exists(public_path($relative)) ? asset($relative) : $fallback;

    /*
    |--------------------------------------------------------------------------
    | Homepage campaign products
    |--------------------------------------------------------------------------
    */
    $heroOne = $productBySlug('dark-seduction', 0);
    $heroTwo = $productBySlug('smoky-chic', 1);
    $heroThree = $productBySlug('le-reve-dore-inspired-by-la-vie-est-belle-premium-womens-sweet-perfume', 2);

    $signatureProduct = $productBySlug('floral-charm-scentsbyaamir', 3);
    $nocturnalProduct = $productBySlug('desert-soul-inspired-by-ombre-nomade-dark-oud-unisex-perfume', 4);
    $finderProduct = $productBySlug('ocean-spirit-inspired-by-acqua-di-gio', 5);
    $storyProduct = $productBySlug('amerel-inspired-by-dior-jadore-elegant-womens-floral-perfume', 6);
    $materialProduct = $productBySlug('dark-aure-inspired-by-nuit-de-feu-intense-smoky-arabic-unisex-perfume', 7);

    /*
    |--------------------------------------------------------------------------
    | Hero slides
    |--------------------------------------------------------------------------
    | Keep marketing titles intentionally short. Imported WooCommerce product
    | names may contain long SEO phrases and should not be used as hero copy.
    */
    $heroSlides = [
        [
            'eyebrow' => 'After Dark',
            'title' => 'Dark Seduction',
            'line' => 'Coffee, florals and addictive warmth.',
            'copy' => 'A magnetic gourmand atmosphere with dark sweetness, polished amber and a warm floral trail.',
            'image' => $customImage(
                'images/home/hero-01-dark-seduction.webp',
                $heroOne['world_image'] ?? $heroOne['image'] ?? $campaigns['home_hero']['image'] ?? null
            ),
            'slug' => $heroOne['slug'] ?? null,
            'number' => '01',
        ],
        [
            'eyebrow' => 'Smoked Woods',
            'title' => 'Smoky Chic',
            'line' => 'Smoky woods. Amber warmth. Modern sophistication.',
            'copy' => 'An evening composition with textured woods, soft smoke and a refined contemporary finish.',
            'image' => $customImage(
                'images/home/hero-02-smoky-chic.webp',
                $heroTwo['world_image'] ?? $heroTwo['image'] ?? $campaigns['nocturnal']['image'] ?? null
            ),
            'slug' => $heroTwo['slug'] ?? null,
            'number' => '02',
        ],
        [
            'eyebrow' => 'Golden Floral',
            'title' => 'Le Reve Dore',
            'line' => 'Luminous florals. Golden sweetness.',
            'copy' => 'A polished feminine fragrance world of radiant florals, warm sweetness and a soft golden trail.',
            'image' => $customImage(
                'images/home/hero-03-le-reve-dore.webp',
                $heroThree['world_image'] ?? $heroThree['image'] ?? $campaigns['rose_material']['image'] ?? null
            ),
            'slug' => $heroThree['slug'] ?? null,
            'number' => '03',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Editorial imagery
    |--------------------------------------------------------------------------
    */
    $storyImage = $customImage(
        'images/home/story-house.webp',
        $storyProduct['world_image'] ?? $storyProduct['image'] ?? $campaigns['signature']['image'] ?? null
    );

    $signatureImage = $customImage(
        'images/home/banner-floral-charm.webp',
        $signatureProduct['world_image'] ?? $signatureProduct['image'] ?? $campaigns['signature']['image'] ?? null
    );

    $nocturnalImage = $customImage(
        'images/home/banner-desert-soul.webp',
        $nocturnalProduct['world_image'] ?? $nocturnalProduct['image'] ?? $campaigns['nocturnal']['image'] ?? null
    );

    $finderImage = $customImage(
        'images/home/banner-ocean-spirit.webp',
        $finderProduct['world_image'] ?? $finderProduct['image'] ?? $campaigns['finder']['image'] ?? null
    );

    $materialImage = $customImage(
        'images/home/banner-materials.webp',
        $materialProduct['world_image'] ?? $materialProduct['image'] ?? $campaigns['rose_material']['image'] ?? null
    );

    $journalImage = $customImage(
        'images/home/banner-journal.webp',
        $campaigns['light_studies']['image'] ?? $storyImage
    );

    $closingImage = $customImage(
        'images/home/banner-closing.webp',
        $campaigns['light_studies']['image'] ?? null
    );

    $homeResponsive = function (?string $url, int $width) {
        if (!$url) return null;
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (!str_starts_with($path, '/images/home/') || !str_ends_with($path, '.webp')) return null;
        return asset(ltrim(preg_replace('/\.webp$/', '-'.$width.'.webp', $path), '/'));
    };

    $heroOne768 = $homeResponsive($heroSlides[0]['image'] ?? null, 768);
    $heroOne1200 = $homeResponsive($heroSlides[0]['image'] ?? null, 1200);
@endphp

@if(!empty($heroSlides[0]['image']))
    @push('head')
        <link
            rel="preload"
            as="image"
            href="{{ $heroOne768 ?: $heroSlides[0]['image'] }}"
            @if($heroOne768)
                imagesrcset="{{ $heroOne768 }} 768w, {{ $heroOne1200 }} 1200w, {{ $heroSlides[0]['image'] }} 1586w"
                imagesizes="100vw"
            @endif
            fetchpriority="high"
        >
    @endpush
@endif

@section('title', 'Scents by Aamir — Fine Fragrance')
@section('description', 'Modern fine fragrance shaped through atmosphere, material and memory.')

@section('content')

{{-- HERO --}}
{{--
    Mobile performance path: render the first campaign as plain HTML with no
    Alpine visibility/timer dependency so Lighthouse can discover and paint the
    LCP image immediately. Desktop keeps the full luxury carousel below.
--}}
<section class="sba-home-hero relative overflow-hidden bg-black text-white md:hidden">
    <div class="relative min-h-[700px] pt-[102px]">
        @php($mobileHero = $heroSlides[0])
        <article class="absolute inset-x-0 bottom-0 top-[102px]">
            @if($mobileHero['image'])
                <img
                    src="{{ $heroOne768 ?: $mobileHero['image'] }}"
                    srcset="{{ $homeResponsive($mobileHero['image'], 768) }} 768w, {{ $homeResponsive($mobileHero['image'], 1200) }} 1200w, {{ $mobileHero['image'] }} 1586w"
                    sizes="100vw"
                    width="1586"
                    height="992"
                    alt="{{ $mobileHero['title'] }} fragrance campaign"
                    fetchpriority="high"
                    decoding="async"
                    class="absolute inset-0 h-full w-full object-cover object-center"
                >
            @endif

            <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.90)_0%,rgba(0,0,0,.70)_30%,rgba(0,0,0,.30)_50%,rgba(0,0,0,.10)_70%,rgba(0,0,0,.34)_100%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/68 via-transparent to-black/15"></div>

            <div class="house-container relative flex h-full items-end pb-24 pt-16">
                <div class="max-w-[650px]">
                    <div class="flex items-center gap-4">
                        <span class="h-px w-10 bg-[#c9ad7a]"></span>
                        <p class="ui-label text-white/58">{{ $mobileHero['eyebrow'] }}</p>
                    </div>

                    <h1 class="mt-6 max-w-[650px] display-serif text-[44px] leading-[.94] tracking-[-.035em]">
                        {{ $mobileHero['title'] }}
                    </h1>

                    <p class="mt-5 max-w-[560px] display-serif text-[22px] leading-tight italic text-[#d6c19a]">
                        {{ $mobileHero['line'] }}
                    </p>

                    <p class="mt-5 max-w-lg text-[14px] leading-7 text-white/66">
                        {{ $mobileHero['copy'] }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @if($mobileHero['slug'])
                            <a href="{{ route('product.show', $mobileHero['slug']) }}" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">
                                Discover fragrance
                            </a>
                        @else
                            <a href="{{ route('shop') }}" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Shop fragrances</a>
                        @endif
                        <a href="{{ route('finder') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">Find your scent</a>
                    </div>
                </div>
            </div>
        </article>

        <div class="house-container absolute inset-x-0 bottom-7 z-20">
            <div class="flex items-center gap-2">
                <span class="ui-label text-white/55">{{ $mobileHero['number'] }}</span>
                <span class="h-px w-12 bg-white"></span>
            </div>
        </div>
    </div>
</section>

{{-- Desktop/tablet luxury carousel --}}
<section
    class="sba-home-hero relative hidden overflow-hidden bg-black text-white md:block"
    x-data="{
        active: 0,
        total: {{ count($heroSlides) }},
        timer: null,
        start() {
            this.stop();
            this.timer = setInterval(() => {
                this.active = (this.active + 1) % this.total;
            }, 6500);
        },
        stop() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        }
    }"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
>
    <div class="relative min-h-[760px] pt-[108px] lg:min-h-[820px]">
        @foreach($heroSlides as $index => $slide)
            <article
                @if($index > 0) x-cloak @endif
                x-show="active === {{ $index }}"
                x-transition:enter="transition duration-700 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition duration-500 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-x-0 bottom-0 top-[108px]"
            >
                @if($slide['image'])
                    <img
                        src="{{ $slide['image'] }}"
                        srcset="{{ $homeResponsive($slide['image'], 768) }} 768w, {{ $homeResponsive($slide['image'], 1200) }} 1200w, {{ $slide['image'] }} 1586w"
                        sizes="100vw"
                        width="1586"
                        height="992"
                        alt="{{ $slide['title'] }} fragrance campaign"
                        decoding="async"
                        class="absolute inset-0 h-full w-full object-cover object-center"
                        @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                    >
                @endif
                <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.90)_0%,rgba(0,0,0,.70)_30%,rgba(0,0,0,.30)_50%,rgba(0,0,0,.10)_70%,rgba(0,0,0,.34)_100%)]"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/68 via-transparent to-black/15"></div>
                <div class="house-container relative flex h-full items-end pb-28 pt-16 lg:items-center lg:pb-10">
                    <div class="max-w-[650px]" data-reveal>
                        <div class="flex items-center gap-4"><span class="h-px w-10 bg-[#c9ad7a]"></span><p class="ui-label text-white/58">{{ $slide['eyebrow'] }}</p></div>
                        <h1 class="mt-6 max-w-[650px] display-serif text-[54px] leading-[.94] tracking-[-.035em] lg:text-[66px] xl:text-[76px]">{{ $slide['title'] }}</h1>
                        <p class="mt-5 max-w-[560px] display-serif text-[28px] leading-tight italic text-[#d6c19a]">{{ $slide['line'] }}</p>
                        <p class="mt-5 max-w-lg text-[14px] leading-7 text-white/66">{{ $slide['copy'] }}</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @if($slide['slug'])
                                <a href="{{ route('product.show', $slide['slug']) }}" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Discover fragrance</a>
                            @else
                                <a href="{{ route('shop') }}" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Shop fragrances</a>
                            @endif
                            <a href="{{ route('finder') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">Find your scent</a>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach

        <div class="house-container absolute inset-x-0 bottom-9 z-20 flex items-center justify-between gap-5">
            <div class="flex items-center gap-3">
                @foreach($heroSlides as $index => $slide)
                    <button type="button" @click="active = {{ $index }}; start()" class="group flex items-center gap-2" aria-label="Show hero {{ $index + 1 }}">
                        <span class="ui-label text-white/55">{{ $slide['number'] }}</span>
                        <span class="h-px transition-all duration-300" :class="active === {{ $index }} ? 'w-12 bg-white' : 'w-5 bg-white/30 group-hover:bg-white/60'"></span>
                    </button>
                @endforeach
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="active = (active - 1 + total) % total; start()" class="sba-hero-arrow" aria-label="Previous hero">←</button>
                <button type="button" @click="active = (active + 1) % total; start()" class="sba-hero-arrow" aria-label="Next hero">→</button>
            </div>
        </div>
    </div>
</section>

{{-- FRAGRANCE FAMILIES --}}
<section class="border-b border-black/10 bg-[#f7f6f2] text-black">
    <div class="house-container grid grid-cols-2 divide-x divide-black/10 py-0 sm:grid-cols-5">
        @foreach([
            ['Signature', 'Iconic · Timeless'],
            ['Fresh', 'Light · Uplifting'],
            ['Woody', 'Deep · Textural'],
            ['Floral', 'Soft · Expressive'],
            ['Oriental', 'Rich · Intense'],
        ] as [$name, $copy])
            <a
                href="{{ route('shop') }}"
                class="group px-4 py-7 text-center transition-colors hover:bg-white sm:px-5 sm:py-9"
            >
                <span class="mx-auto block h-2 w-2 rounded-full border border-[#96784d] transition-transform group-hover:scale-150"></span>
                <p class="mt-4 ui-label text-black/75">{{ $name }}</p>
                <p class="mt-2 text-[11px] text-black/42">{{ $copy }}</p>
            </a>
        @endforeach
    </div>
</section>

{{-- HOUSE SELECTION --}}
<section class="bg-[#f7f6f2] text-black">
    <div class="house-container py-16 lg:py-24">
        <div class="flex items-end justify-between gap-6">
            <div>
                <p class="ui-label text-black/38">Curated for you</p>
                <h2 class="mt-3 display-serif text-5xl sm:text-6xl">House Selection</h2>
            </div>

            <a href="{{ route('shop') }}" class="text-link">View all →</a>
        </div>

        @if($products->isNotEmpty())
            <div class="mt-10 grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                @foreach($products->take(6) as $index => $product)
                    <x-house.product-card
                        :product="$product"
                        :index="str_pad($index + 1, 2, '0', STR_PAD_LEFT)"
                        :tone="in_array($product['slug'] ?? '', ['velvet-oud','after-dark','oud-noir','midnight-resin']) ? 'dark' : 'light'"
                    />
                @endforeach
            </div>
        @else
            <div class="mt-10 border border-black/10 px-6 py-16 text-center">
                <p class="display-serif text-4xl">The next fragrance edit is being prepared.</p>
            </div>
        @endif
    </div>
</section>

{{-- OUR HOUSE --}}
<section class="bg-black text-white">
    <div class="house-container grid min-h-[620px] overflow-hidden lg:grid-cols-[.82fr_1.18fr]">

        <div class="flex items-center px-1 py-16 sm:px-8 lg:px-0 lg:pr-16">
            <div class="max-w-xl" data-reveal>
                <p class="ui-label text-[#c9ad7a]">Our House</p>

                <h2 class="mt-5 max-w-[620px] display-serif text-[46px] leading-[.94] sm:text-[58px] lg:text-[66px]">
                    Crafted with memory & intention.
                </h2>

                <p class="mt-7 max-w-lg text-sm leading-7 text-white/58">
                    Scents by Aamir is built around the idea that fragrance should feel personal.
                    Familiar materials are reframed through atmosphere, contrast and a modern visual language.
                </p>

                <a
                    href="{{ url('/about') }}"
                    class="btn-outline mt-8 border-white/35 text-white hover:bg-white hover:text-black"
                >
                    Discover the house
                </a>
            </div>
        </div>

        <div class="relative min-h-[500px] overflow-hidden lg:min-h-[620px]">
            @if($storyImage)
                <img
                    src="{{ $storyImage }}"
                    srcset="{{ $homeResponsive($storyImage, 768) }} 768w, {{ $homeResponsive($storyImage, 1200) }} 1200w, {{ $storyImage }} 1600w"
                    sizes="(max-width: 767px) 100vw, 60vw"
                    width="1600"
                    height="1000"
                    alt="Scents by Aamir house story"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                >
            @endif

            <div class="absolute inset-0 bg-gradient-to-r from-black/30 via-transparent to-transparent"></div>
        </div>
    </div>
</section>

{{-- EDITORIAL WORLDS --}}
<section class="bg-[#efebe3]">
    <div class="house-container py-12 lg:py-16">
        <div class="grid gap-4 lg:grid-cols-[1.25fr_.75fr]">

            <a
                href="{{ route('shop') }}"
                class="sba-editorial-card relative min-h-[620px] overflow-hidden bg-[#171717] text-white"
            >
                @if($signatureImage)
                    <img
                        src="{{ $signatureImage }}"
                    srcset="{{ $homeResponsive($signatureImage, 768) }} 768w, {{ $homeResponsive($signatureImage, 1200) }} 1200w, {{ $signatureImage }} 1600w"
                    sizes="(max-width: 767px) 100vw, 60vw"
                    width="1600"
                    height="1000"
                        alt="Floral Charm editorial fragrance world"
                        class="absolute inset-0 h-full w-full object-cover"
                        loading="lazy"
                    >
                @endif

                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/15 to-black/10"></div>

                <div class="absolute inset-x-0 bottom-0 p-7 sm:p-10 lg:p-12">
                    <p class="ui-label text-[#d4bd91]">House Edit</p>
                    <h2 class="mt-3 max-w-[620px] display-serif text-[48px] leading-[.92] sm:text-[60px]">
                        Floral Charm
                    </h2>

                    <p class="mt-4 max-w-lg text-sm leading-6 text-white/70">
                        A refined floral world shaped with softness, contrast and a polished modern finish.
                    </p>

                    <span class="mt-7 inline-block ui-label">Explore fragrances →</span>
                </div>
            </a>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">

                <a
                    href="{{ route('shop') }}"
                    class="sba-editorial-card relative min-h-[300px] overflow-hidden bg-[#151515] text-white"
                >
                    @if($nocturnalImage)
                        <img
                            src="{{ $nocturnalImage }}"
                            srcset="{{ $homeResponsive($nocturnalImage, 768) }} 768w, {{ $homeResponsive($nocturnalImage, 1200) }} 1200w, {{ $nocturnalImage }} 1600w"
                            sizes="(max-width: 767px) 100vw, 60vw"
                            width="1600"
                            height="1000"
                            alt="Nocturnal fragrance world"
                            class="absolute inset-0 h-full w-full object-cover"
                            loading="lazy"
                        >
                    @endif

                    <div class="absolute inset-0 bg-black/46"></div>

                    <div class="absolute inset-x-0 bottom-0 p-7">
                        <p class="ui-label text-white/48">After dark</p>
                        <h3 class="mt-2 display-serif text-[42px] leading-none sm:text-[48px]">Nocturnal</h3>
                        <span class="mt-5 inline-block ui-label">Explore →</span>
                    </div>
                </a>

                <a
                    href="{{ route('finder') }}"
                    class="sba-editorial-card relative min-h-[300px] overflow-hidden bg-[#6f6455] text-white"
                >
                    @if($finderImage)
                        <img
                            src="{{ $finderImage }}"
                            srcset="{{ $homeResponsive($finderImage, 768) }} 768w, {{ $homeResponsive($finderImage, 1200) }} 1200w, {{ $finderImage }} 1600w"
                            sizes="(max-width: 767px) 100vw, 60vw"
                            width="1600"
                            height="1000"
                            alt="Fragrance finder freshness study"
                            class="absolute inset-0 h-full w-full object-cover"
                            loading="lazy"
                        >
                    @endif

                    <div class="absolute inset-0 bg-black/48"></div>

                    <div class="absolute inset-x-0 bottom-0 p-7">
                        <p class="ui-label text-white/48">Fragrance Finder</p>
                        <h3 class="mt-2 max-w-[360px] display-serif text-[40px] leading-[.94] sm:text-[46px]">
                            Start with a feeling.
                        </h3>
                        <span class="mt-5 inline-block ui-label">Find your fragrance →</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- MATERIAL LIBRARY --}}
<section class="bg-[#f7f6f2] text-black">
    <div class="house-container grid gap-10 py-16 lg:grid-cols-[.64fr_1.36fr] lg:items-end lg:py-24">

        <div>
            <p class="ui-label text-black/38">Material Library</p>

            <h2 class="mt-4 max-w-[680px] display-serif text-[44px] leading-[.96] sm:text-[54px] lg:text-[60px]">
                The finest raw materials, translated through the house.
            </h2>

            <p class="mt-6 max-w-md text-sm leading-7 text-black/50">
                Oud, amber, rose, woods and musk are treated as texture and emotion — not simply as a list of notes.
            </p>

            <a href="{{ route('ingredients') }}" class="text-link mt-7 inline-block">
                Explore ingredients →
            </a>
        </div>

        <div class="relative min-h-[500px] overflow-hidden bg-[#d2c7b8] lg:min-h-[540px]">
            @if($materialImage)
                <img
                    src="{{ $materialImage }}"
                    srcset="{{ $homeResponsive($materialImage, 768) }} 768w, {{ $homeResponsive($materialImage, 1200) }} 1200w, {{ $materialImage }} 1600w"
                    sizes="(max-width: 767px) 100vw, 60vw"
                    width="1600"
                    height="1000"
                    alt="Scents by Aamir raw fragrance materials"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                >
            @endif

            <div class="absolute inset-0 bg-gradient-to-t from-black/64 via-transparent to-transparent"></div>

            <div class="absolute inset-x-0 bottom-0 grid grid-cols-2 gap-px border-t border-white/20 bg-black/20 text-white backdrop-blur-sm sm:grid-cols-4">
                @foreach(['Oud', 'Rose', 'Sandalwood', 'Amber'] as $material)
                    <div class="px-5 py-5 sm:px-6">
                        <p class="display-serif text-2xl">{{ $material }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-[.18em] text-white/52">
                            House material
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- JOURNAL --}}
<section class="bg-white">
    <div class="house-container py-14 lg:py-20">
        <div class="grid gap-4 lg:grid-cols-[.92fr_1.08fr]">

            <div class="relative min-h-[520px] overflow-hidden bg-[#c8b7a6]">
                @if($journalImage)
                    <img
                        src="{{ $journalImage }}"
                    srcset="{{ $homeResponsive($journalImage, 768) }} 768w, {{ $homeResponsive($journalImage, 1200) }} 1200w, {{ $journalImage }} 1600w"
                    sizes="(max-width: 767px) 100vw, 60vw"
                    width="1600"
                    height="1000"
                        alt="Scents by Aamir perfume making journal study"
                        class="absolute inset-0 h-full w-full object-cover"
                        loading="lazy"
                    >
                @endif

                <div class="absolute inset-0 bg-black/20"></div>

                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/68 to-transparent p-7 pt-32 text-white sm:p-10">
                    <p class="ui-label text-white/55">The Journal</p>
                    <h3 class="mt-2 max-w-[520px] display-serif text-[44px] leading-[.94] sm:text-[52px]">
                        Notes, materials and ritual.
                    </h3>
                </div>
            </div>

            <div class="grid min-h-[520px] grid-rows-[1fr_auto] bg-[#f0ede6]">
                <div class="flex items-center p-8 sm:p-12 lg:p-16">
                    <div data-reveal>
                        <p class="ui-label text-black/38">Journal</p>

                        <h2 class="mt-4 max-w-3xl display-serif text-[46px] leading-[.94] sm:text-[58px] lg:text-[64px]">
                            Fragrance becomes image, memory and ritual.
                        </h2>

                        <p class="mt-7 max-w-xl text-sm leading-7 text-black/52">
                            Enter the house journal for material stories, fragrance rituals and the ideas behind each world.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('journal') }}"
                    class="flex min-h-[78px] items-center justify-between border-t border-black/10 px-8 ui-label transition-colors hover:bg-white"
                >
                    <span>Enter the Journal</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- CLOSING CTA --}}
<section class="relative overflow-hidden bg-[#f4f0e8] text-black">
    <div class="house-container relative grid min-h-[480px] items-center gap-10 py-20 lg:grid-cols-[.82fr_1.18fr] lg:py-24">

        <div class="relative z-10">
            <p class="ui-label text-black/38">Find your signature</p>

            <h2 class="mt-4 max-w-[720px] display-serif text-[48px] leading-[.94] sm:text-[62px] lg:text-[72px]">
                Choose slowly.<br>
                Wear completely.
            </h2>

            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('shop') }}" class="btn-solid">
                    Explore all fragrances
                </a>

                <a href="{{ route('finder') }}" class="btn-outline">
                    Find your scent
                </a>
            </div>
        </div>

        <div class="relative min-h-[300px] lg:min-h-[360px]">
            @if($closingImage)
                <img
                    src="{{ $closingImage }}"
                    srcset="{{ $homeResponsive($closingImage, 768) }} 768w, {{ $homeResponsive($closingImage, 1200) }} 1200w, {{ $closingImage }} 1600w"
                    sizes="(max-width: 767px) 100vw, 60vw"
                    width="1600"
                    height="1000"
                    alt="Scents by Aamir closing fragrance artwork"
                    class="absolute inset-0 h-full w-full object-contain object-center"
                    loading="lazy"
                >
            @endif
        </div>
    </div>
</section>

@endsection

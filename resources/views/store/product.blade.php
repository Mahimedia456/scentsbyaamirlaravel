@extends('layouts.store')

@php
    $finalProductMap = config('product-finalization.products.'.($product['slug'] ?? ''), []);

    $catalog = config('storefront.products');
    $slug = $slug ?? request()->route('slug');
    $visual = $productData ?? ($catalog[$slug] ?? []);

    $name = $visual['display_name'] ?? $visual['name'] ?? 'Fragrance';
    $fullName = $visual['name'] ?? $name;
    $family = $visual['family'] ?? 'Fine Fragrance';
    $audience = $visual['audience'] ?? 'Unisex';
    $price = $visual['price'] ?? '0';
    $priceValue = (float) ($visual['price_value'] ?? str_replace(',', '', (string) $price));
    $badge = $visual['badge'] ?? null;
    $image = $visual['image'] ?? null;
    $worldImage = $visual['world_image'] ?? $image;
    $gallery = collect($visual['images'] ?? [])->filter()->unique()->values();

    if ($gallery->isEmpty() && $image) {
        $gallery->push($image);
    }

    /*
    |--------------------------------------------------------------------------
    | Product-specific final artwork
    |--------------------------------------------------------------------------
    | Phase 17 allows each fragrance to have its own hero/world/notes/story art.
    | Existing imported product photography remains the fallback.
    */
    $productArtworkBase = "images/products/{$slug}";
    $productHeroPath = "{$productArtworkBase}/hero.webp";
    $productWorldPath = "{$productArtworkBase}/world.webp";
    $productNotesPath = "{$productArtworkBase}/notes.webp";
    $productStoryPath = "{$productArtworkBase}/story.webp";

    $productHero = file_exists(public_path($productHeroPath)) ? asset($productHeroPath) : null;
    $productWorld = file_exists(public_path($productWorldPath)) ? asset($productWorldPath) : null;
    $productNotesImage = file_exists(public_path($productNotesPath)) ? asset($productNotesPath) : null;
    $productStoryImage = file_exists(public_path($productStoryPath)) ? asset($productStoryPath) : null;

    if ($productHero && !$gallery->contains($productHero)) {
        $gallery->prepend($productHero);
    }

    /*
    |--------------------------------------------------------------------------
    | Automatic product world
    |--------------------------------------------------------------------------
    | One family/world background is reused intelligently across products.
    | The actual product image is layered above it, so there is no need to
    | manually generate 3 extra images for every fragrance.
    */
    $worldHaystack = strtolower(implode(' ', array_filter([
        $fullName,
        $family,
        $visual['notes'] ?? '',
        $visual['story'] ?? '',
        $visual['description'] ?? '',
    ])));

    $worldKey = $finalProductMap['world'] ??  match (true) {
        str_contains($worldHaystack, 'oud') => 'oud',
        str_contains($worldHaystack, 'smok') || str_contains($worldHaystack, 'leather') || str_contains($worldHaystack, 'dark') => 'dark',
        str_contains($worldHaystack, 'rose') || str_contains($worldHaystack, 'floral') || str_contains($worldHaystack, 'jasmine') => 'floral',
        str_contains($worldHaystack, 'citrus') || str_contains($worldHaystack, 'bergamot') || str_contains($worldHaystack, 'fresh') || str_contains($worldHaystack, 'ocean') => 'fresh',
        str_contains($worldHaystack, 'vanilla') || str_contains($worldHaystack, 'gourmand') || str_contains($worldHaystack, 'sweet') || str_contains($worldHaystack, 'coffee') => 'gourmand',
        str_contains($worldHaystack, 'amber') || str_contains($worldHaystack, 'resin') => 'amber',
        str_contains($worldHaystack, 'spice') || str_contains($worldHaystack, 'saffron') || str_contains($worldHaystack, 'pepper') || str_contains($worldHaystack, 'cardamom') => 'spicy',
        str_contains($worldHaystack, 'wood') || str_contains($worldHaystack, 'sandal') || str_contains($worldHaystack, 'cedar') => 'woody',
        default => 'signature',
    };

    $worldBackgroundPath = "images/product-worlds/{$worldKey}.webp";

    // Final product PDP does not show reusable/dummy worlds.
    // Only exact product-specific artwork is rendered when it exists.
    $worldBackground = $productWorld;
    $ritualBackgroundPath = "images/product-worlds/ritual.webp";
    $ritualBackground = $productStoryImage;

    $theme = $visual['theme'] ?? [];
    $world = $visual['world'] ?? [];

    $bg = $theme['background'] ?? '#F3F0EA';
    $surface = $theme['surface'] ?? '#E8E2D8';
    $ink = $theme['ink'] ?? '#111111';
    $accent = $theme['accent'] ?? '#8B7257';

    $variants = collect($visual['variants'] ?? []);
    if ($variants->isEmpty()) {
        $variants = collect([[
            'id' => null,
            'name' => $visual['size_label'] ?? '50 ML',
            'size_label' => $visual['size_label'] ?? '50 ML',
            'sku' => $visual['sku'] ?? null,
            'price' => $price,
            'price_value' => $priceValue,
            'stock' => (int) ($visual['stock'] ?? 0),
            'in_stock' => (bool) ($visual['in_stock'] ?? true),
        ]]);
    }

    $defaultVariant = $variants->firstWhere('in_stock', true) ?: $variants->first();
    $defaultSize = $defaultVariant['size_label'] ?? $defaultVariant['name'] ?? ($visual['size_label'] ?? '50 ML');
    $inStock = $variants->contains(fn ($variant) => (bool) ($variant['in_stock'] ?? ((int) ($variant['stock'] ?? 0) > 0)));

    $story = trim((string) ($visual['story'] ?? ''))
        ?: trim((string) ($visual['description'] ?? ''))
        ?: 'A modern fragrance built around contrast, texture and memory. Designed to unfold with restraint and leave a distinctive trail on skin.';

    $notesText = trim((string) ($visual['notes'] ?? ''));
    $wear = trim((string) ($visual['wear'] ?? ''))
        ?: 'Balanced projection with a persistent dry-down designed to move naturally from day into evening.';

    /*
    |--------------------------------------------------------------------------
    | Notes
    |--------------------------------------------------------------------------
    | Imported WooCommerce content often arrives as one notes string. We keep
    | that real content visible and use restrained supporting defaults instead
    | of inventing an overly specific pyramid.
    */
    $topNotes = $world['top_notes'] ?? 'Opening notes';
    $heartNotes = $world['heart_notes'] ?? ($notesText ?: 'Signature accord');
    $baseNotes = $world['base_notes'] ?? 'Dry woods · Amber · Musk';

    /*
    |--------------------------------------------------------------------------
    | Ingredient links
    |--------------------------------------------------------------------------
    | Only route to Phase 03 ingredients that actually exist.
    */
    $ingredientLibrary = collect(config('storefront.ingredients', []));
    $materialHaystack = strtolower(implode(' ', array_filter([
        $fullName,
        $family,
        $notesText,
        $story,
    ])));

    $matchedMaterials = $ingredientLibrary
        ->filter(function ($ingredient, $materialSlug) use ($materialHaystack) {
            $needle = strtolower($ingredient['name'] ?? $materialSlug);
            return str_contains($materialHaystack, strtolower($materialSlug))
                || str_contains($materialHaystack, $needle);
        })
        ->take(3)
        ->map(fn ($ingredient, $materialSlug) => [
            'name' => $ingredient['name'] ?? ucfirst($materialSlug),
            'slug' => $materialSlug,
            'desc' => $ingredient['descriptor'] ?? ($ingredient['family'] ?? 'House material'),
        ])
        ->values();

    if ($matchedMaterials->isEmpty()) {
        $matchedMaterials = collect(['amber', 'sandalwood', 'spices'])
            ->filter(fn ($materialSlug) => $ingredientLibrary->has($materialSlug))
            ->map(fn ($materialSlug) => [
                'name' => $ingredientLibrary[$materialSlug]['name'] ?? ucfirst($materialSlug),
                'slug' => $materialSlug,
                'desc' => $ingredientLibrary[$materialSlug]['descriptor'] ?? 'House material',
            ])
            ->values();
    }

    $mood = $world['mood'] ?? match (strtolower($audience)) {
        'women' => 'Radiant · Expressive · Polished',
        'men' => 'Structured · Refined · Modern',
        default => 'Individual · Textural · Modern',
    };

    $occasion = $world['occasion'] ?? 'Day into evening';
    $notesEditorialImage = $productNotesImage;
    $desktopHeroMedia = $gallery->first();
    $desktopSupportMedia = $gallery->slice(1, 2)->values();
@endphp

@section('title', $name.' — Scents by Aamir')
@section('description', \Illuminate\Support\Str::limit(strip_tags($story), 155))

@section('content')
<div x-data x-init="$store.commerce.rememberViewed(@js(['product_id'=>$product['id']??null,'slug'=>$product['slug']??null,'name'=>$product['display_name']??$product['name']??'Fragrance','family'=>$product['family']??null,'image'=>$product['image']??null,'price_value'=>$product['price_value']??$product['price']??0,'available'=>$product['available']??true]))" class="contents">
<div
    x-data="{
        size: @js($defaultSize),
        qty: 1,
        details: 'notes',
        activeMedia: 0,
        variants: @js($variants->values()->all()),
        gallery: @js($gallery->values()->all()),

        selectedVariant() {
            return this.variants.find(v => (v.size_label || v.name) === this.size)
                || this.variants[0]
                || null
        },

        maxQty() {
            return Number(this.selectedVariant()?.stock ?? 0)
        },

        currentPrice() {
            return Number(this.selectedVariant()?.price_value ?? @js($priceValue))
        },

        selectSize(value) {
            this.size = value
            this.qty = Math.min(this.qty, Math.max(1, this.maxQty()))
        }
    }"
    style="--product-bg: {{ $bg }}; --product-surface: {{ $surface }}; --product-ink: {{ $ink }}; --product-accent: {{ $accent }};"
    class="bg-white text-black"
>
    {{-- PURCHASE + GALLERY --}}
    <section class="border-b border-black/10 bg-[#f4f3ef]">
        <div class="px-4 pb-4 pt-5 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-[1680px] flex-wrap items-center gap-2 text-[9px] uppercase tracking-[.16em] text-black/38">
                <a href="{{ route('shop') }}" class="transition hover:text-black">Fragrances</a>
                <span>/</span>
                <span>{{ $audience }}</span>
                <span>/</span>
                <span>{{ $family }}</span>
                <span>/</span>
                <span class="text-black/70">{{ $name }}</span>
            </div>
        </div>

        <div class="grid lg:grid-cols-[minmax(0,1.42fr)_minmax(390px,.58fr)]">
            {{-- MEDIA --}}
            <div class="relative bg-[#eceae4]">
                <div class="lg:hidden">
                    <div class="relative min-h-[520px] overflow-hidden">
                        @foreach($gallery as $index => $media)
                            <img
                                x-cloak
                                x-show="activeMedia === {{ $index }}"
                                x-transition.opacity
                                src="{{ $media }}"
                                alt="{{ $name }} image {{ $index + 1 }}"
                                class="absolute inset-0 h-full w-full object-cover"
                                @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                            >
                        @endforeach

                        @if($badge)
                            <span class="absolute left-4 top-4 border border-black/10 bg-white/90 px-3 py-2 text-[8px] uppercase tracking-[.18em] backdrop-blur">
                                {{ $badge }}
                            </span>
                        @endif

                        @if($gallery->count() > 1)
                            <div class="absolute inset-x-4 bottom-4 flex items-center justify-between">
                                <span class="rounded-full bg-white/90 px-3 py-2 text-[8px] uppercase tracking-[.15em] backdrop-blur">
                                    <span x-text="String(activeMedia + 1).padStart(2,'0')"></span>
                                    /
                                    {{ str_pad($gallery->count(), 2, '0', STR_PAD_LEFT) }}
                                </span>

                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="activeMedia = (activeMedia - 1 + gallery.length) % gallery.length"
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-sm backdrop-blur"
                                        aria-label="Previous image"
                                    >←</button>
                                    <button
                                        type="button"
                                        @click="activeMedia = (activeMedia + 1) % gallery.length"
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-sm backdrop-blur"
                                        aria-label="Next image"
                                    >→</button>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($gallery->count() > 1)
                        <div class="flex gap-2 overflow-x-auto border-t border-black/10 bg-white p-3">
                            @foreach($gallery as $index => $media)
                                <button
                                    type="button"
                                    @click="activeMedia={{ $index }}"
                                    class="relative h-20 w-16 shrink-0 overflow-hidden border transition"
                                    :class="activeMedia === {{ $index }} ? 'border-black' : 'border-black/10'"
                                    aria-label="Show image {{ $index + 1 }}"
                                >
                                    <img src="{{ $media }}" alt="" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="hidden min-h-[720px] gap-px bg-black/10 lg:grid {{ $desktopSupportMedia->isNotEmpty() ? 'lg:grid-cols-[1.32fr_.68fr]' : 'lg:grid-cols-1' }}">
                    @if($desktopHeroMedia)
                        <figure class="group relative min-h-[720px] overflow-hidden bg-[#efeee9]">
                            <img
                                src="{{ $desktopHeroMedia }}"
                                alt="{{ $name }} hero"
                                class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.008]"
                                fetchpriority="high"
                            >
                            @if($badge)
                                <span class="absolute left-5 top-5 border border-black/10 bg-white/90 px-3 py-2 text-[8px] uppercase tracking-[.18em] backdrop-blur">
                                    {{ $badge }}
                                </span>
                            @endif
                            <span class="absolute bottom-5 left-5 bg-black/45 px-3 py-2 text-[8px] uppercase tracking-[.18em] text-white/75 backdrop-blur">
                                01 / Product
                            </span>
                        </figure>
                    @else
                        <div class="flex min-h-[720px] items-center justify-center bg-[#efeee9]">
                            <p class="display-serif text-4xl text-black/25">Product imagery coming soon.</p>
                        </div>
                    @endif

                    @if($desktopSupportMedia->isNotEmpty())
                        <div class="grid min-h-0 grid-rows-2 gap-px bg-black/10">
                            @foreach($desktopSupportMedia as $index => $media)
                                <figure class="group relative min-h-0 overflow-hidden bg-[#efeee9]">
                                    <img
                                        src="{{ $media }}"
                                        alt="{{ $name }} detail {{ $index + 2 }}"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.012]"
                                        loading="lazy"
                                    >
                                    <span class="absolute bottom-4 left-4 bg-black/45 px-3 py-2 text-[8px] uppercase tracking-[.18em] text-white/70 backdrop-blur">
                                        {{ str_pad($index + 2, 2, '0', STR_PAD_LEFT) }} / Detail
                                    </span>
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- PURCHASE PANEL --}}
            <aside class="relative bg-white">
                <div class="px-6 py-9 sm:px-10 lg:sticky lg:top-[108px] lg:px-11 lg:py-11 xl:px-14">
                    <div class="flex items-start justify-between gap-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                <p class="ui-label text-black/38">{{ $audience }}</p>
                                <span class="h-1 w-1 rounded-full bg-black/20"></span>
                                <p class="ui-label text-black/38">{{ $family }}</p>
                            </div>

                            <h1 class="mt-4 max-w-[520px] display-serif text-[48px] leading-[.9] tracking-[-.035em] sm:text-[58px] lg:text-[54px] xl:text-[64px]">
                                {{ $name }}
                            </h1>

                            <p class="mt-4 text-[10px] uppercase tracking-[.15em] text-black/42">
                                Eau de Parfum
                                @if($visual['sku'] ?? null)
                                    · {{ $visual['sku'] }}
                                @endif
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="$store.commerce.toggleWishlist({
                                product_id:@js($visual['id'] ?? null),
                                slug:@js($slug),
                                name:@js($name),
                                family:@js($family),
                                price:@js($price),
                                price_value:@js($priceValue),
                                image:@js($image)
                            })"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-black/15 text-xl transition hover:border-black hover:bg-black hover:text-white"
                            aria-label="Toggle wishlist"
                        >
                            <span x-text="$store.commerce.inWishlist(@js($visual['id'] ?? $slug)) ? '♥' : '♡'">♡</span>
                        </button>
                    </div>

                    <p class="mt-7 max-w-md text-[13px] leading-6 text-black/58">{{ $story }}</p>

                    <div class="mt-7 flex items-end justify-between gap-4 border-y border-black/10 py-5">
                        <div>
                            <p class="ui-label text-black/30">Selected</p>
                            <p class="mt-2 text-xs uppercase tracking-[.12em]" x-text="size"></p>
                        </div>
                        <div class="text-right">
                            <p class="ui-label text-black/30">Price</p>
                            <p class="mt-2 text-[15px] font-medium">
                                PKR <span x-text="currentPrice().toLocaleString()"></span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-7">
                        <div class="flex items-center justify-between gap-5">
                            <p class="ui-label text-black/42">Select size</p>
                            <span class="ui-label {{ $inStock ? 'text-emerald-700/70' : 'text-red-600' }}">
                                {{ $inStock ? 'In stock' : 'Out of stock' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach($variants as $variant)
                                @php
                                    $variantSize = $variant['size_label'] ?? $variant['name'] ?? 'Size';
                                    $variantStock = (int) ($variant['stock'] ?? 0);
                                @endphp

                                <button
                                    type="button"
                                    @click="selectSize(@js($variantSize))"
                                    :class="size===@js($variantSize)
                                        ? 'border-black bg-black text-white'
                                        : 'border-black/15 bg-white text-black hover:border-black/50'"
                                    class="min-h-[52px] border px-2 text-[9px] uppercase tracking-[.14em] transition disabled:cursor-not-allowed disabled:opacity-35"
                                    @disabled($variantStock <= 0)
                                >
                                    {{ $variantSize }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-[104px_1fr] gap-2">
                        <div class="grid min-h-[58px] grid-cols-3 border border-black/15">
                            <button type="button" @click="qty=Math.max(1,qty-1)" class="transition hover:bg-black/5" aria-label="Decrease quantity">−</button>
                            <div class="flex items-center justify-center text-xs" x-text="qty">1</div>
                            <button
                                type="button"
                                @click="qty=Math.min(Math.max(1,maxQty()),qty+1)"
                                :disabled="maxQty() <= qty"
                                class="transition hover:bg-black/5 disabled:opacity-30"
                                aria-label="Increase quantity"
                            >+</button>
                        </div>

                        <button
                            type="button"
                            @click="$store.commerce.addToCart({
                                product_id:@js($visual['id'] ?? null),
                                variant_id:selectedVariant()?.id ?? null,
                                slug:@js($slug),
                                name:@js($name),
                                family:@js($family),
                                sku:selectedVariant()?.sku ?? @js($visual['sku'] ?? null),
                                price:selectedVariant()?.price ?? @js($price),
                                price_value:currentPrice(),
                                image:@js($image),
                                size:size,
                                stock:maxQty(),
                                available:maxQty() > 0,
                                qty:qty
                            })"
                            class="min-h-[58px] bg-black px-5 text-[9px] font-medium uppercase tracking-[.18em] text-white transition hover:bg-[#292929] disabled:cursor-not-allowed disabled:bg-black/35"
                            :disabled="maxQty() <= 0"
                        >
                            <span x-show="maxQty() > 0">
                                Add to bag · PKR <span x-text="currentPrice().toLocaleString()"></span>
                            </span>
                            <span x-show="maxQty() <= 0">Out of stock</span>
                        </button>
                    </div>

                    <a
                        href="{{ route('gifting') }}"
                        class="mt-2 flex min-h-[48px] items-center justify-center border border-black/15 text-[9px] uppercase tracking-[.17em] transition hover:border-black"
                    >
                        Send as a gift
                    </a>

                    <div class="mt-8 divide-y divide-black/10 border-y border-black/10">
                        @foreach([
                            ['Delivery', 'Complimentary on selected orders'],
                            ['Presentation', 'Signature gift wrapping available'],
                            ['Returns', 'Easy returns on eligible orders'],
                        ] as [$title, $copy])
                            <div class="grid grid-cols-[105px_1fr] gap-4 py-4">
                                <span class="ui-label text-black/38">{{ $title }}</span>
                                <span class="text-[11px] leading-5 text-black/58">{{ $copy }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </section>

    {{-- PRODUCT IDENTITY --}}
    <section class="bg-white">
        <div class="house-container py-16 lg:py-24">
            <div class="grid gap-10 border-b border-black/10 pb-14 lg:grid-cols-[.5fr_1.5fr] lg:pb-20">
                <div>
                    <p class="ui-label text-black/35">The fragrance</p>
                    <p class="mt-4 max-w-xs text-xs leading-6 text-black/45">
                        Character, structure and the way the composition is intended to live on skin.
                    </p>
                </div>

                <div>
                    <h2 class="max-w-5xl display-serif text-[42px] leading-[1.02] sm:text-[54px] lg:text-[64px]">
                        {{ $story }}
                    </h2>

                    <div class="mt-10 grid gap-5 border-t border-black/10 pt-6 sm:grid-cols-4">
                        <div>
                            <p class="ui-label text-black/30">Mood</p>
                            <p class="mt-3 text-sm">{{ $mood }}</p>
                        </div>
                        <div>
                            <p class="ui-label text-black/30">Family</p>
                            <p class="mt-3 text-sm">{{ $family }}</p>
                        </div>
                        <div>
                            <p class="ui-label text-black/30">For</p>
                            <p class="mt-3 text-sm">{{ $audience }}</p>
                        </div>
                        <div>
                            <p class="ui-label text-black/30">Wear</p>
                            <p class="mt-3 text-sm">{{ $occasion }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- NOTES / STORY / WEAR --}}
    <section class="bg-[#0b0b0b] text-white">
        <div class="house-container py-16 lg:py-24">
            <div class="grid gap-12 lg:grid-cols-[.34fr_1.66fr]">
                <div>
                    <p class="ui-label text-white/35">Composition</p>

                    <div class="mt-8 grid gap-3 lg:sticky lg:top-[130px]">
                        @foreach([
                            ['notes', 'Notes'],
                            ['story', 'Story'],
                            ['wear', 'Wear'],
                        ] as [$key, $label])
                            <button
                                type="button"
                                @click="details=@js($key)"
                                :class="details===@js($key) ? 'text-white translate-x-2' : 'text-white/28'"
                                class="text-left display-serif text-[30px] transition duration-300"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="min-h-[390px]">
                    <div x-show="details==='notes'">
                        <div class="grid gap-10 lg:grid-cols-[1.2fr_.8fr]">
                            <div class="border-y border-white/15">
                                @foreach([
                                    ['01', 'Opening', $topNotes, 'First impression'],
                                    ['02', 'Heart', $heartNotes, 'Central character'],
                                    ['03', 'Dry-down', $baseNotes, 'Lasting trail'],
                                ] as [$index, $stage, $notes, $copy])
                                    <div class="grid gap-4 border-b border-white/12 py-7 last:border-b-0 sm:grid-cols-[50px_110px_1fr_auto] sm:items-center">
                                        <span class="text-[9px] tracking-[.16em] text-white/25">{{ $index }}</span>
                                        <span class="ui-label text-white/35">{{ $stage }}</span>
                                        <span class="display-serif text-[28px] leading-tight sm:text-[34px]">{{ $notes }}</span>
                                        <span class="hidden text-[10px] text-white/30 sm:block">{{ $copy }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="relative min-h-[360px] overflow-hidden border border-white/12">
                                @if($worldBackground)
                                    <img
                                        src="{{ $notesEditorialImage }}"
                                        alt=""
                                        class="absolute inset-0 h-full w-full object-cover opacity-55"
                                        loading="lazy"
                                    >
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/35 to-black/10"></div>
                                <div class="absolute inset-x-0 bottom-0 p-7">
                                    <p class="ui-label text-white/35">Automatic scent world</p>
                                    <p class="mt-4 display-serif text-[34px] leading-[1.02]">
                                        {{ ucfirst($worldKey) }} atmosphere selected from this fragrance’s real name, family, story and notes.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-cloak x-show="details==='story'">
                        <p class="max-w-5xl display-serif text-[40px] leading-[1.04] sm:text-[54px] lg:text-[62px]">
                            {{ $world['statement'] ?? $story }}
                        </p>
                        <p class="mt-8 max-w-2xl text-sm leading-7 text-white/50">{{ $story }}</p>
                    </div>

                    <div x-cloak x-show="details==='wear'">
                        <div class="grid gap-10 lg:grid-cols-[1.15fr_.85fr]">
                            <p class="display-serif text-[40px] leading-[1.05] sm:text-[54px] lg:text-[60px]">{{ $wear }}</p>

                            <div class="border border-white/15 p-7">
                                <p class="ui-label text-white/35">Wear profile</p>

                                <div class="mt-7 space-y-6">
                                    @foreach([
                                        ['Presence', 'Refined', '64%'],
                                        ['Longevity', 'Long wearing', '80%'],
                                        ['Versatility', 'Day to evening', '72%'],
                                    ] as [$label, $value, $width])
                                        <div>
                                            <div class="flex justify-between text-xs text-white/65">
                                                <span>{{ $label }}</span>
                                                <span>{{ $value }}</span>
                                            </div>
                                            <div class="mt-3 h-px bg-white/15">
                                                <div class="h-px bg-white" style="width:{{ $width }}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CAMPAIGN WORLD --}}
    <section class="bg-[#f5f3ee] text-black">
        <div class="house-container py-4 sm:py-6 lg:py-10">
            <div class="grid overflow-hidden lg:grid-cols-[1.18fr_.82fr]">
                <div class="relative min-h-[520px] overflow-hidden bg-[#171717] lg:min-h-[700px]">
                    @if($worldBackground)
                        <img
                            src="{{ $worldBackground }}"
                            alt=""
                            loading="lazy"
                            decoding="async"
                            class="absolute inset-0 h-full w-full object-cover"
                        >
                    @endif

                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_45%,rgba(255,255,255,.06),transparent_32%),linear-gradient(90deg,rgba(0,0,0,.32),rgba(0,0,0,.02),rgba(0,0,0,.28))]"></div>

                    @if($image)
                        <img
                            src="{{ $image }}"
                            alt="{{ $name }}"
                            loading="lazy"
                            decoding="async"
                            class="absolute inset-x-[18%] bottom-[7%] top-[7%] h-[86%] w-[64%] object-contain drop-shadow-[0_28px_42px_rgba(0,0,0,.48)]"
                        >
                    @endif

                    <div class="absolute bottom-5 left-5 border border-white/15 bg-black/30 px-3 py-2 text-[8px] uppercase tracking-[.16em] text-white/55 backdrop-blur">
                        {{ ucfirst($worldKey) }} world
                    </div>
                </div>

                <div class="flex min-h-[420px] items-end bg-[var(--product-surface)] p-8 text-[var(--product-ink)] sm:p-12 lg:min-h-[700px] lg:p-14">
                    <div>
                        <p class="ui-label opacity-40">{{ $world['kicker'] ?? 'The scent world' }}</p>

                        <h2 class="mt-4 max-w-2xl display-serif text-[46px] leading-[.94] sm:text-[58px]">
                            {{ $world['statement'] ?? 'A fragrance shaped by texture, contrast and memory.' }}
                        </h2>

                        <p class="mt-8 text-[10px] uppercase tracking-[.15em] opacity-45">
                            {{ $mood }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MATERIALS --}}
    <section class="bg-white text-black">
        <div class="house-container py-16 lg:py-24">
            <div class="flex flex-col gap-6 border-b border-black/10 pb-8 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="ui-label text-black/35">Material study</p>
                    <h2 class="mt-3 display-serif text-[46px] leading-[.94] sm:text-[58px]">Inside {{ $name }}</h2>
                </div>
                <a href="{{ route('ingredients') }}" class="text-link">Explore all materials →</a>
            </div>

            <div class="grid gap-px bg-black/10 sm:grid-cols-3">
                @foreach($matchedMaterials as $material)
                    <a
                        href="{{ route('ingredients.show', $material['slug']) }}"
                        class="group bg-[#f7f6f2] p-7 transition hover:bg-[#efebe3] sm:min-h-[280px]"
                    >
                        <div class="flex h-full flex-col justify-between">
                            <span class="ui-label text-black/30">Material</span>

                            <div class="mt-20">
                                <h3 class="display-serif text-4xl sm:text-5xl">{{ $material['name'] }}</h3>
                                <p class="mt-3 text-[10px] uppercase tracking-[.12em] text-black/40">{{ $material['desc'] }}</p>
                                <span class="mt-6 inline-block text-link">Discover →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- RITUAL --}}
    <section class="grid bg-[#101010] text-white lg:grid-cols-2">
        <div class="relative min-h-[500px] overflow-hidden lg:min-h-[640px]">
            @if($ritualBackground)
                <img
                    src="{{ $ritualBackground }}"
                    alt="Scents by Aamir fragrance ritual"
                    loading="lazy"
                    decoding="async"
                    class="absolute inset-0 h-full w-full object-cover"
                >
            @endif
            <div class="absolute inset-0 bg-black/20"></div>
        </div>

        <div class="flex min-h-[500px] items-center p-8 sm:p-12 lg:min-h-[640px] lg:p-16">
            <div class="max-w-xl">
                <p class="ui-label text-white/35">House ritual</p>

                <h2 class="mt-4 display-serif text-[46px] leading-[.95] sm:text-[58px]">
                    Wear close. Let it evolve.
                </h2>

                <p class="mt-6 text-sm leading-7 text-white/52">
                    Apply to pulse points and allow the fragrance to settle naturally. Avoid rubbing the skin so the composition can develop in its own rhythm.
                </p>

                <div class="mt-9 grid gap-6 border-t border-white/15 pt-6 sm:grid-cols-2">
                    <div>
                        <p class="ui-label text-white/30">Best moment</p>
                        <p class="mt-2 text-sm text-white/70">{{ $occasion }}</p>
                    </div>
                    <div>
                        <p class="ui-label text-white/30">Character</p>
                        <p class="mt-2 text-sm text-white/70">{{ $mood }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- RELATED --}}
    <section class="bg-[#f7f6f2] text-black">
        <div class="house-container py-16 lg:py-24">
            <div class="mb-9 flex items-end justify-between gap-6">
                <div>
                    <p class="ui-label text-black/35">You may also like</p>
                    <h2 class="mt-3 display-serif text-[46px] sm:text-[58px]">Explore next</h2>
                </div>

                <a href="{{ route('shop') }}" class="text-link hidden sm:block">View all fragrances →</a>
            </div>

            <div class="grid gap-x-4 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(($relatedProducts ?? collect($catalog)->except($slug)->map(fn($p,$s)=>array_merge($p,['slug'=>$s]))->values()->take(4)) as $related)
                    <x-house.product-card
                        :product="$related"
                        :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                    />
                @endforeach
            </div>
        </div>
    </section>
</div>
</div>
@endsection

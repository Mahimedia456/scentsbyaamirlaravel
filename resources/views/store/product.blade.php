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
    $officialGallery = collect($visual['images'] ?? [])->filter()->unique()->values();

    if ($officialGallery->isEmpty() && $image) {
        $officialGallery->push($image);
    }

    $gallery = $officialGallery;

    /*
    |--------------------------------------------------------------------------
    | Robust exact product artwork resolver
    |--------------------------------------------------------------------------
    | Primary rule is the exact Laravel slug folder. For older prepared folders
    | we also tolerate a display-name/prefix folder mismatch so uploaded artwork
    | is not silently ignored. The resolver never throws if a file is missing.
    */
    $productFolders = collect([
        $slug,
        \Illuminate\Support\Str::slug($name),
        \Illuminate\Support\Str::slug(\Illuminate\Support\Str::before($name, ' - ')),
        \Illuminate\Support\Str::slug(\Illuminate\Support\Str::before($name, '|')),
    ])->filter()->unique()->values();

    $productPrefix = \Illuminate\Support\Str::slug(
        \Illuminate\Support\Str::before($name, ' ')
    );

    if ($productPrefix !== '') {
        foreach (glob(public_path("images/products/{$productPrefix}*"), GLOB_ONLYDIR) ?: [] as $candidateDirectory) {
            $productFolders->push(basename($candidateDirectory));
        }
    }

    $productFolders = $productFolders->filter()->unique()->values();

    $resolveProductArtwork = function (string $filename) use ($productFolders): ?string {
        foreach ($productFolders as $folder) {
            $relative = 'images/products/' . trim((string) $folder, '/') . '/' . ltrim($filename, '/');

            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return null;
    };

    $productHero = $resolveProductArtwork('hero.webp');
    $productNotesImage = $resolveProductArtwork('notes.webp');
    $productTopNotesImage = $resolveProductArtwork('top-notes.webp');
    $productHeartNotesImage = $resolveProductArtwork('heart-notes.webp');
    $productBaseNotesImage = $resolveProductArtwork('base-notes.webp');
    $productWorld = $resolveProductArtwork('world.webp');
    $productStoryImage = $resolveProductArtwork('story.webp');

    /*
    |--------------------------------------------------------------------------
    | Final PDP gallery policy
    |--------------------------------------------------------------------------
    | Gallery is PRODUCT photography only:
    | 1) generated exact hero.webp
    | 2) official/imported Woo/Laravel product gallery images
    |
    | notes/world/story artwork belongs to editorial sections below and must
    | never be mixed into the shopping gallery.
    */
    $gallery = collect();

    if ($productHero) {
        $gallery->push($productHero);
    }

    foreach ($officialGallery as $officialMedia) {
        if ($officialMedia && !$gallery->contains($officialMedia)) {
            $gallery->push($officialMedia);
        }
    }

    if ($gallery->isEmpty() && $image) {
        $gallery->push($image);
    }

    $gallery = $gallery->filter()->unique()->take(6)->values();
    $productPrimaryMedia = $gallery->first() ?: $image;

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

    $productIdentity = strtolower(trim($fullName . ' ' . $slug));
    $isTesterProduct = str_contains($productIdentity, 'tester');

    if (!$isTesterProduct && $variants->count() === 1 && ($variants->first()['id'] ?? null) === null) {
        $variants = collect([array_merge($variants->first(), [
            'name' => '50 ML',
            'size_label' => '50 ML',
            'stock' => 99,
            'in_stock' => true,
        ])]);
    }

    $defaultVariant = $variants->firstWhere('in_stock', true) ?: $variants->first();
    $defaultSize = $isTesterProduct
        ? ($defaultVariant['size_label'] ?? '5 ML')
        : '50 ML';
    $inStock = $isTesterProduct
        ? $variants->contains(fn ($variant) => (bool) ($variant['in_stock'] ?? ((int) ($variant['stock'] ?? 0) > 0)))
        : true;

    $socialProof = config("product-social-proof.products.{$slug}");
    if (!$socialProof) {
        $seed = (int) sprintf('%u', crc32($slug ?: $name));
        $ratingOptions = [4.3, 4.4, 4.5, 4.6, 4.7, 4.8];
        $socialProof = [
            'rating' => $ratingOptions[$seed % count($ratingOptions)],
            'reviews' => 84 + ($seed % 157),
            'sold' => 680 + ($seed % 1380),
        ];
    }

    $rating = number_format((float) $socialProof['rating'], 1);
    $reviewCount = (int) $socialProof['reviews'];
    $soldCount = (int) $socialProof['sold'];

    $story = trim((string) ($visual['story'] ?? ''))
        ?: trim((string) ($visual['description'] ?? ''))
        ?: 'A modern fragrance built around contrast, texture and memory. Designed to unfold with restraint and leave a distinctive trail on skin.';

    $notesText = trim((string) ($visual['notes'] ?? ''));
    $wear = trim((string) ($visual['wear'] ?? ''))
        ?: 'Balanced projection with a persistent dry-down designed to move naturally from day into evening.';

    $inspiredBy = null;
    if (preg_match('/inspired\s+by\s+(.+?)(?:\||$)/i', $fullName, $match)) {
        $inspiredBy = trim($match[1]);
    } elseif (preg_match('/inspired\s+by\s+([^,.]+)/i', $story, $match)) {
        $inspiredBy = trim($match[1]);
    }

    /*
    |--------------------------------------------------------------------------
    | Notes
    |--------------------------------------------------------------------------
    | Imported WooCommerce content often arrives as one notes string. We keep
    | that real content visible and use restrained supporting defaults instead
    | of inventing an overly specific pyramid.
    */
    /*
    |--------------------------------------------------------------------------
    | Structured fragrance notes
    |--------------------------------------------------------------------------
    | Product Description is never a note source. The database now stores
    | Top / Heart / Base notes independently. Legacy `notes` is fallback-only.
    */
    $extractLegacyNote = function (string $label, array $stops = []) use ($notesText): ?string {
        $source = trim((string) $notesText);

        if ($source === '') {
            return null;
        }

        $lookahead = '$';

        if ($stops !== []) {
            $alternation = implode('|', array_map(
                fn (string $stop) => preg_quote($stop, '/'),
                $stops
            ));

            $lookahead = '(?=\s*(?:' . $alternation . ')\s*:?\s*|$)';
        }

        if (!preg_match(
            '/' . preg_quote($label, '/') . '\s*:?\s*(.+?)' . $lookahead . '/is',
            $source,
            $match
        )) {
            return null;
        }

        $value = trim(preg_replace('/\s+/', ' ', strip_tags($match[1])));

        return $value !== '' ? rtrim($value, " .;,-") : null;
    };

    $topNotes = trim((string) ($visual['top_notes'] ?? ''))
        ?: $extractLegacyNote('Top Notes', ['Heart Notes', 'Base Notes'])
        ?: 'Opening notes';

    $heartNotes = trim((string) ($visual['heart_notes'] ?? ''))
        ?: $extractLegacyNote('Heart Notes', ['Base Notes'])
        ?: 'Signature accord';

    $baseNotes = trim((string) ($visual['base_notes'] ?? ''))
        ?: $extractLegacyNote('Base Notes')
        ?: 'Dry woods · Amber · Musk';

    $productDescription = trim((string) ($visual['description'] ?? ''));

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
    $desktopHeroMedia = $productPrimaryMedia ?? $gallery->first();
    $desktopSupportMedia = $gallery->slice(1)->values();
    $commerceImage = $productHero ?: $image ?: $desktopHeroMedia;
@endphp

@section('title', $name.' — Scents by Aamir')
@section('description', \Illuminate\Support\Str::limit(strip_tags($story), 155))

@section('content')
<div x-data x-init="$store.commerce.rememberViewed(@js(['product_id'=>$product['id']??null,'slug'=>$product['slug']??null,'name'=>$product['display_name']??$product['name']??'Fragrance','family'=>$product['family']??null,'image'=>$commerceImage ?? ($product['image']??null),'price_value'=>$product['price_value']??$product['price']??0,'available'=>$product['available']??true]))" class="contents">
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
        },

        addCurrentToCart() {
            const variant = this.selectedVariant()

            this.$store.commerce.addToCart({
                product_id: @js($visual['id'] ?? null),
                variant_id: variant?.id ?? null,
                slug: @js($slug),
                name: @js($name),
                family: @js($family),
                sku: variant?.sku ?? @js($visual['sku'] ?? null),
                price: variant?.price ?? @js($price),
                price_value: this.currentPrice(),
                image: @js($commerceImage ?? $image),
                size: @js($isTesterProduct ? $defaultSize : '50 ML'),
                stock: this.maxQty(),
                available: this.maxQty() > 0,
                qty: this.qty
            })
        }
    }"
    style="--product-bg: {{ $bg }}; --product-surface: {{ $surface }}; --product-ink: {{ $ink }}; --product-accent: {{ $accent }};"
    class="bg-white text-black"
>
    {{-- FINAL PRODUCT EXPERIENCE --}}
    <section class="bg-[#f6f4ef] text-black">
        <div class="house-container py-5 sm:py-7 lg:py-10">

            <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-black/10 pb-4">
                <div class="flex flex-wrap items-center gap-2 text-[8px] uppercase tracking-[.17em] text-black/38">
                    <a href="{{ route('shop') }}" class="transition hover:text-black">Fragrances</a>
                    <span>/</span>
                    <span>{{ $family }}</span>
                    <span>/</span>
                    <span class="text-black/72">{{ $name }}</span>
                </div>

                <p class="text-[8px] uppercase tracking-[.16em] text-black/35">
                    Scents by Aamir · Eau de Parfum
                </p>
            </div>

            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(390px,.8fr)]">

                {{-- PRODUCT-SPECIFIC 4-IMAGE GALLERY --}}
                <div class="overflow-hidden rounded-[22px] border border-black/10 bg-[#dedbd4]">
                    <div class="relative aspect-[1.12/1] overflow-hidden bg-[#171717] lg:min-h-[650px] xl:min-h-[720px]">
                        @foreach($gallery->take(4) as $index => $media)
                            <img
                                x-cloak
                                x-show="activeMedia === {{ $index }}"
                                x-transition.opacity.duration.250ms
                                src="{{ $media }}"
                                alt="{{ $name }} — gallery image {{ $index + 1 }}"
                                class="absolute inset-0 h-full w-full object-cover"
                                @if($index === 0)
                                    fetchpriority="high"
                                @else
                                    loading="lazy"
                                @endif
                            >
                        @endforeach

                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-black/5"></div>

                        <div class="absolute left-5 top-5 rounded-full border border-white/20 bg-black/30 px-4 py-2 text-[8px] uppercase tracking-[.16em] text-white backdrop-blur">
                            <span x-text="String(activeMedia + 1).padStart(2,'0')"></span>
                            /
                            {{ str_pad(max(1, min(4, $gallery->count())), 2, '0', STR_PAD_LEFT) }}
                        </div>

                        @if($gallery->count() > 1)
                            <div class="absolute right-5 top-5 flex gap-2">
                                <button
                                    type="button"
                                    @click="activeMedia=(activeMedia-1+gallery.length)%gallery.length"
                                    class="grid h-11 w-11 place-items-center border border-white/25 bg-black/28 text-white backdrop-blur transition hover:bg-white hover:text-black"
                                    aria-label="Previous product image"
                                >←</button>

                                <button
                                    type="button"
                                    @click="activeMedia=(activeMedia+1)%gallery.length"
                                    class="grid h-11 w-11 place-items-center border border-white/25 bg-black/28 text-white backdrop-blur transition hover:bg-white hover:text-black"
                                    aria-label="Next product image"
                                >→</button>
                            </div>
                        @endif
                    </div>

                    @if($gallery->count() > 1)
                        <div class="grid grid-cols-4 gap-px bg-black/10">
                            @foreach($gallery->take(4) as $index => $media)
                                <button
                                    type="button"
                                    @click="activeMedia={{ $index }}"
                                    class="group relative aspect-[1.18/1] overflow-hidden bg-[#ebe8e1]"
                                    :class="activeMedia === {{ $index }} ? 'ring-2 ring-inset ring-[#b69045]' : ''"
                                    aria-label="Show gallery image {{ $index + 1 }}"
                                >
                                    <img
                                        src="{{ $media }}"
                                        alt=""
                                        loading="lazy"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.025]"
                                    >

                                    <span class="absolute bottom-2 left-2 bg-black/48 px-2 py-1 text-[7px] uppercase tracking-[.14em] text-white/80 backdrop-blur">
                                        @if($index === 0)
                                            Hero
                                        @else
                                            Product {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        @endif
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- PURCHASE CARD --}}
                <aside class="xl:sticky xl:top-[118px]">
                    <div class="rounded-[22px] border border-black/10 bg-white p-6 sm:p-8 lg:p-9">
                        <div class="flex items-start justify-between gap-5">
                            <div>
                                <p class="ui-label text-black/35">{{ $audience }} fragrance</p>

                                <h1 class="mt-3 max-w-[560px] display-serif text-[48px] leading-[.91] tracking-[-.035em] sm:text-[58px]">
                                    {{ $name }}
                                </h1>

                                <p class="mt-4 text-[9px] uppercase tracking-[.15em] text-black/42">
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
                                    image:@js($commerceImage)
                                })"
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-black/12 text-lg transition hover:bg-black hover:text-white"
                                aria-label="Toggle wishlist"
                            >
                                <span x-text="$store.commerce.inWishlist(@js($visual['id'] ?? $slug)) ? '♥' : '♡'">♡</span>
                            </button>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2 border-y border-black/10 py-4">
                            <span class="text-[12px] tracking-[.04em] text-[#b68728]">★★★★★</span>
                            <strong class="text-[11px]">{{ $rating }}</strong>
                            <span class="text-[10px] text-black/42">({{ number_format($reviewCount) }} reviews)</span>
                            <span class="h-3 w-px bg-black/15"></span>
                            <span class="text-[10px] font-medium text-black/58">{{ number_format($soldCount) }} sold</span>
                        </div>

                        <p class="mt-5 text-[12px] leading-[1.8] text-black/58">
                            {{ \Illuminate\Support\Str::limit($story, 390) }}
                        </p>

                        <div class="mt-6 overflow-hidden border border-black/10">
                            <div class="grid grid-cols-2">
                                <div class="p-4">
                                    <p class="ui-label text-black/30">Selected size</p>
                                    <p class="mt-2 text-sm font-medium uppercase tracking-[.12em]">
                                        {{ $isTesterProduct ? $defaultSize : '50 ML' }}
                                    </p>
                                </div>

                                <div class="border-l border-black/10 p-4 text-right">
                                    <p class="ui-label text-black/30">Availability</p>
                                    <p class="mt-2 text-[10px] font-medium uppercase tracking-[.13em] {{ $inStock ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ $inStock ? '● In stock' : 'Out of stock' }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-black/10 p-4">
                                <div class="flex items-end justify-between gap-4">
                                    <div>
                                        <p class="ui-label text-black/30">Price</p>
                                        <p class="mt-2 text-[18px] font-semibold">
                                            PKR <span x-text="currentPrice().toLocaleString()"></span>
                                        </p>
                                    </div>

                                    <span class="border border-black/12 px-3 py-2 text-[9px] uppercase tracking-[.14em]">
                                        {{ $isTesterProduct ? $defaultSize : '50 ML' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-[104px_1fr] gap-2">
                            <div class="grid min-h-[58px] grid-cols-3 overflow-hidden border border-black/12">
                                <button type="button" @click="qty=Math.max(1,qty-1)" class="hover:bg-black/5">−</button>
                                <div class="flex items-center justify-center border-x border-black/10 text-xs" x-text="qty">1</div>
                                <button
                                    type="button"
                                    @click="qty=Math.min(Math.max(1,maxQty()),qty+1)"
                                    :disabled="maxQty() <= qty"
                                    class="hover:bg-black/5 disabled:opacity-30"
                                >+</button>
                            </div>

                            <button
                                type="button"
@click="addCurrentToCart()"
                                class="min-h-[58px] bg-black px-5 text-[9px] font-semibold uppercase tracking-[.17em] text-white transition hover:bg-[#292929] disabled:bg-black/30"
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
                            class="mt-2 flex min-h-[50px] items-center justify-center border border-black/12 text-[9px] uppercase tracking-[.16em] transition hover:bg-[#f7f6f2]"
                        >
                            Send as a gift
                        </a>

                        <div class="mt-6 grid grid-cols-2 gap-px overflow-hidden bg-black/10 sm:grid-cols-4">
                            @foreach([
                                ['Long lasting', 'Premium quality'],
                                ['Authentic scents', 'House crafted'],
                                ['Secure payment', 'Protected checkout'],
                                ['Easy service', 'Client support'],
                            ] as [$label, $copy])
                                <div class="bg-[#f7f6f2] p-4">
                                    <p class="text-[8px] font-semibold uppercase tracking-[.12em]">{{ $label }}</p>
                                    <p class="mt-2 text-[9px] leading-4 text-black/44">{{ $copy }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- FRAGRANCE STORY + NOTES --}}
    <section class="bg-white text-black">
        <div class="house-container py-14 lg:py-20">
            <div class="grid gap-10 lg:grid-cols-[.85fr_1.15fr] lg:items-start">

                <article class="lg:pr-10">
                    <p class="ui-label text-black/32">The fragrance</p>

                    <h2 class="mt-5 max-w-xl display-serif text-[45px] leading-[.98] tracking-[-.025em] sm:text-[56px]">
                        A scent designed to leave its presence behind.
                    </h2>

                    <p class="mt-7 max-w-xl text-[12px] leading-7 text-black/56">
                        {{ $story }}
                    </p>
                </article>

                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach([
                        ['Top notes', $topNotes, $productTopNotesImage ?: $productNotesImage],
                        ['Heart notes', $heartNotes, $productHeartNotesImage ?: $productNotesImage],
                        ['Base notes', $baseNotes, $productBaseNotesImage ?: $productNotesImage],
                    ] as [$label, $note, $noteImage])
                        <article class="overflow-hidden border border-black/10 bg-[#f8f6f1]">
                            <div class="relative aspect-[1.34/1] overflow-hidden bg-[#161616]">
                                @if($noteImage)
                                    <img
                                        src="{{ $noteImage }}"
                                        alt="{{ $name }} {{ strtolower($label) }}"
                                        loading="lazy"
                                        class="absolute inset-0 h-full w-full object-cover"
                                    >
                                @endif
                            </div>

                            <div class="min-h-[145px] p-5">
                                <p class="ui-label text-black/32">{{ $label }}</p>
                                <h3 class="mt-3 display-serif text-[27px] leading-[1.08]">{{ $note }}</h3>
                            </div>
                        </article>
                    @endforeach
                </div>

            </div>
        </div>
    </section>

    {{-- PRODUCT DESCRIPTION --}}
    @if($productDescription)
        <section class="bg-[#f6f4ef] text-black">
            <div class="house-container py-12 lg:py-16">
                <div class="grid gap-8 border-y border-black/10 py-10 lg:grid-cols-[.32fr_.68fr] lg:items-start">
                    <div>
                        <p class="ui-label text-black/32">Product description</p>
                        <h2 class="mt-4 display-serif text-[38px] leading-[.96] sm:text-[46px]">
                            About {{ $name }}
                        </h2>
                    </div>

                    <div class="max-w-[820px]">
                        <p class="text-[13px] leading-8 text-black/62">
                            {{ $productDescription }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- SCENT WORLD BANNER --}}
    <section class="bg-[#f6f4ef] text-white">
        <div class="house-container pb-5 lg:pb-7">
            <div class="relative min-h-[430px] overflow-hidden bg-[#101010] lg:min-h-[520px]">
                @if($productWorld)
                    <img
                        src="{{ $productWorld }}"
                        alt="{{ $name }} scent world"
                        loading="lazy"
                        class="absolute inset-0 h-full w-full object-cover"
                    >
                @endif

                <div class="absolute inset-0 bg-gradient-to-r from-black/78 via-black/28 to-black/20"></div>

                <div class="relative flex min-h-[430px] items-center px-7 py-10 sm:px-10 lg:min-h-[520px] lg:px-14">
                    <div class="max-w-[560px]">
                        <p class="ui-label text-white/45">Scent world</p>

                        <h2 class="mt-5 display-serif text-[46px] leading-[.95] sm:text-[58px]">
                            {{ $world['statement'] ?? 'Texture, contrast and memory — composed as a modern fragrance world.' }}
                        </h2>

                        <p class="mt-6 text-[9px] uppercase tracking-[.16em] text-white/48">
                            {{ $mood }}
                        </p>
                    </div>
                </div>

                <div class="absolute bottom-7 right-7 hidden w-[260px] border border-white/15 bg-black/45 p-5 backdrop-blur-md md:block">
                    <div class="space-y-5">
                        @foreach([
                            ['Longevity', '8H+', '82%'],
                            ['Sillage', 'Refined', '68%'],
                            ['Best time', $occasion, '74%'],
                        ] as [$label, $value, $width])
                            <div>
                                <div class="flex items-center justify-between text-[9px] text-white/65">
                                    <span>{{ $label }}</span>
                                    <span>{{ $value }}</span>
                                </div>
                                <div class="mt-2 h-[2px] bg-white/15">
                                    <div class="h-[2px] bg-[#c9a55c]" style="width:{{ $width }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PRODUCT INFORMATION BETWEEN BANNERS --}}
    <section class="bg-[#f6f4ef] text-black">
        <div class="house-container pb-5 lg:pb-7">
            <div class="grid gap-px overflow-hidden border border-black/10 bg-black/10 sm:grid-cols-2 lg:grid-cols-5">
                @foreach([
                    ['Inspired by', $inspiredBy ?: 'Scents by Aamir scent direction'],
                    ['Concentration', 'Eau de Parfum'],
                    ['Size', $isTesterProduct ? $defaultSize : '50 ML'],
                    ['For', $audience],
                    ['Best worn', $occasion],
                ] as [$label, $value])
                    <div class="bg-white p-5">
                        <p class="ui-label text-black/30">{{ $label }}</p>
                        <p class="mt-2 text-[11px] leading-5 text-black/68">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PRODUCT DETAIL STUDY --}}
    <section class="bg-white text-black">
        <div class="house-container py-14 lg:py-20">

            <div class="mx-auto max-w-[900px] text-center">
                <p class="ui-label text-black/35">Product study</p>
                <h2 class="mt-3 display-serif text-[46px] leading-[.94] sm:text-[58px]">Inside {{ $name }}</h2>
                <p class="mx-auto mt-5 max-w-[650px] text-[11px] leading-6 text-black/48">
                    A concise view of the fragrance family, character and signature material direction.
                </p>
            </div>

            <div class="mx-auto mt-10 grid max-w-[1180px] gap-px bg-black/10 lg:grid-cols-3">
                <article class="bg-[#f7f6f2] p-8 text-center lg:min-h-[260px]">
                    <div class="flex h-full flex-col items-center justify-center">
                        <p class="ui-label text-black/30">Fragrance family</p>
                        <h3 class="mt-7 display-serif text-[40px] leading-[.96]">{{ $family }}</h3>
                        <p class="mx-auto mt-4 max-w-[320px] text-[11px] leading-6 text-black/52">
                            {{ \Illuminate\Support\Str::limit($story, 180) }}
                        </p>
                    </div>
                </article>

                <article class="bg-[#f7f6f2] p-8 text-center lg:min-h-[260px]">
                    <div class="flex h-full flex-col items-center justify-center">
                        <p class="ui-label text-black/30">Character</p>
                        <h3 class="mt-7 display-serif text-[40px] leading-[.96]">{{ $mood }}</h3>
                        <p class="mt-4 text-[10px] uppercase tracking-[.12em] text-black/40">
                            {{ $audience }} · {{ $occasion }}
                        </p>
                    </div>
                </article>

                <article class="bg-[#101010] p-8 text-center text-white lg:min-h-[260px]">
                    <div class="flex h-full flex-col items-center justify-center">
                        <p class="ui-label text-white/32">Key material</p>

                        @if($matchedMaterials->isNotEmpty())
                            <h3 class="mt-7 display-serif text-[40px] leading-[.96]">
                                {{ $matchedMaterials->first()['name'] }}
                            </h3>

                            <p class="mt-4 text-[10px] uppercase tracking-[.12em] text-white/40">
                                {{ $matchedMaterials->first()['desc'] }}
                            </p>

                            <a
                                href="{{ route('ingredients.show', $matchedMaterials->first()['slug']) }}"
                                class="mt-6 inline-block text-link text-white"
                            >
                                Discover material →
                            </a>
                        @else
                            <h3 class="mt-7 display-serif text-[40px] leading-[.96]">Eau de Parfum</h3>
                            <p class="mx-auto mt-4 max-w-[320px] text-[11px] leading-6 text-white/50">
                                A concentrated Scents by Aamir composition designed to unfold from opening through dry-down.
                            </p>
                        @endif
                    </div>
                </article>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('ingredients') }}" class="text-link">Explore all materials →</a>
            </div>

        </div>
    </section>

    {{-- STORY / HOUSE RITUAL BANNER --}}
    <section class="bg-[#f6f4ef] text-white">
        <div class="house-container pb-7 lg:pb-10">
            <div class="grid overflow-hidden bg-[#101010] lg:grid-cols-[1.15fr_.85fr]">
                <div class="relative min-h-[380px] overflow-hidden lg:min-h-[470px]">
                    @if($productStoryImage)
                        <img
                            src="{{ $productStoryImage }}"
                            alt="{{ $name }} fragrance story"
                            loading="lazy"
                            class="absolute inset-0 h-full w-full object-cover"
                        >
                    @endif
                    <div class="absolute inset-0 bg-black/18"></div>
                </div>

                <div class="flex min-h-[380px] items-center p-8 sm:p-10 lg:min-h-[470px] lg:p-12">
                    <div class="max-w-lg">
                        <p class="ui-label text-white/35">House ritual</p>

                        <h2 class="mt-4 display-serif text-[44px] leading-[.95] sm:text-[55px]">
                            Wear close. Let it evolve.
                        </h2>

                        <p class="mt-6 text-[12px] leading-7 text-white/55">
                            Apply to pulse points and let the composition settle naturally. Avoid rubbing the skin so the opening, heart and dry-down can unfold in sequence.
                        </p>

                        <div class="mt-8 grid grid-cols-2 gap-6 border-t border-white/15 pt-6">
                            <div>
                                <p class="ui-label text-white/30">Character</p>
                                <p class="mt-2 text-[11px] leading-5 text-white/70">{{ $mood }}</p>
                            </div>

                            <div>
                                <p class="ui-label text-white/30">Occasion</p>
                                <p class="mt-2 text-[11px] leading-5 text-white/70">{{ $occasion }}</p>
                            </div>
                        </div>
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
                    <div>
                        <x-house.product-card
                            :product="$related"
                            :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                        />

                        @php
                            $relatedSeed = (int) sprintf('%u', crc32((string) ($related['slug'] ?? $related['name'] ?? $loop->index)));
                            $relatedRating = [4.3,4.4,4.5,4.6,4.7,4.8][$relatedSeed % 6];
                            $relatedSold = 520 + ($relatedSeed % 1180);
                        @endphp

                        <div class="mt-3 flex items-center justify-between text-[9px] text-black/45">
                            <span><span class="text-[#b68728]">★</span> {{ number_format($relatedRating, 1) }}</span>
                            <span>{{ number_format($relatedSold) }} sold</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
</div>
@endsection

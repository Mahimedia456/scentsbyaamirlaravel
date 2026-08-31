@extends('layouts.store')

@php
    $catalog = config('storefront.products');
    $slug = $slug ?? request()->route('slug');
    $visual = $productData ?? ($catalog[$slug] ?? []);

    $name = $visual['name'] ?? 'Fragrance';
    $family = $visual['family'] ?? 'Fine Fragrance';
    $price = $visual['price'] ?? '0';
    $badge = $visual['badge'] ?? null;
    $image = $visual['image'] ?? null;
    $worldImage = $visual['world_image'] ?? $image;
    $theme = $visual['theme'] ?? [];
    $world = $visual['world'] ?? [];
    $variants = collect($visual['variants'] ?? []);
    if ($variants->isEmpty()) {
        $variants = collect([[
            'id' => null,
            'name' => '100 ML',
            'size_label' => '100 ML',
            'sku' => $visual['sku'] ?? null,
            'price' => $price,
            'price_value' => (float) str_replace(',', '', (string) $price),
            'stock' => (int) ($visual['stock'] ?? 0),
            'in_stock' => (bool) ($visual['in_stock'] ?? true),
        ]]);
    }
    $defaultVariant = $variants->firstWhere('in_stock', true) ?: $variants->first();
    $defaultSize = $defaultVariant['size_label'] ?? $defaultVariant['name'] ?? '100 ML';
    $inStock = $variants->contains(fn ($variant) => (bool) ($variant['in_stock'] ?? ((int) ($variant['stock'] ?? 0) > 0)));

    $bg = $theme['background'] ?? '#F3F0EA';
    $surface = $theme['surface'] ?? '#E8E2D8';
    $ink = $theme['ink'] ?? '#111111';
    $accent = $theme['accent'] ?? '#8B7257';

    $isVelvet = $slug === 'velvet-oud';

    $story = $visual['story'] ?: ($visual['description'] ?: ($isVelvet
        ? 'A dark floral structure built around oud, rose and soft woods. Rich in character, but deliberately controlled.'
        : 'A modern fragrance built around contrast, texture and memory. Designed to evolve quietly on skin rather than announce itself all at once.'));

    $notesText = $visual['notes'] ?? null;
    $topNotes = $isVelvet ? 'Saffron · Pink Pepper' : 'Bergamot · Cardamom';
    $heartNotes = $notesText ?: ($isVelvet ? 'Rose · Oud' : 'Iris · Warm Woods');
    $baseNotes = $isVelvet ? 'Amber · Musk' : 'Amber · Skin Musk';

    $performance = $visual['wear'] ?: ($isVelvet
        ? 'Deep, polished and long-wearing with a controlled trail.'
        : 'Balanced projection with a close, persistent dry-down designed for everyday wear.');

    $materials = $isVelvet
        ? [
            ['name' => 'Oud', 'slug' => 'oud', 'desc' => 'Dark · Resinous · Textural'],
            ['name' => 'Rose', 'slug' => 'rose', 'desc' => 'Floral · Velvety · Warm'],
            ['name' => 'Amber', 'slug' => 'amber', 'desc' => 'Warm · Soft · Diffusive'],
        ]
        : [
            ['name' => 'Amber', 'slug' => 'amber', 'desc' => 'Warm · Soft · Diffusive'],
            ['name' => 'Musk', 'slug' => 'musk', 'desc' => 'Clean · Skin · Soft'],
            ['name' => 'Cedar', 'slug' => 'cedar', 'desc' => 'Dry · Structured · Woody'],
        ];
@endphp

@section('title', $name.' — Scents by Aamir')

@section('content')
<div
    x-data="{
        size: @js($defaultSize),
        qty: 1,
        details: 'story',
        variants: @js($variants->values()->all()),
        selectedVariant() { return this.variants.find(v => (v.size_label || v.name) === this.size) || this.variants[0] || null },
        maxQty() { return Number(this.selectedVariant()?.stock ?? 0) },
        currentPrice() { return Number(this.selectedVariant()?.price_value ?? @js((float) str_replace(',', '', (string) $price))) }
    }"
    style="--product-bg: {{ $bg }}; --product-surface: {{ $surface }}; --product-ink: {{ $ink }}; --product-accent: {{ $accent }};"
    class="bg-white text-black"
>
    <!-- PRODUCT PURCHASE / GALLERY -->
    <section class="border-b border-black/10 bg-[#f4f3ef]">
        <div class="px-4 pb-4 pt-5 sm:px-6 lg:px-8 lg:pb-8">
            <div class="mx-auto flex max-w-[1680px] items-center gap-2 text-[9px] uppercase tracking-[.16em] text-black/38">
                <a href="{{ route('shop') }}" class="transition hover:text-black">Shop</a>
                <span>/</span>
                <span>{{ $family }}</span>
                <span>/</span>
                <span class="text-black/70">{{ $name }}</span>
            </div>
        </div>

        <div class="grid lg:min-h-[780px] lg:grid-cols-[minmax(0,1.45fr)_minmax(390px,.55fr)]">
            <!-- Media -->
            <div class="grid gap-px bg-black/10 sm:grid-cols-2">
                <figure class="relative min-h-[620px] overflow-hidden bg-[#efeee9] lg:min-h-[780px]">
                    <img src="{{ $image }}" alt="{{ $name }} bottle" class="absolute inset-0 h-full w-full object-cover transition duration-700 hover:scale-[1.01]">
                    @if($badge)
                        <span class="absolute left-5 top-5 border border-black/15 bg-white/90 px-3 py-2 text-[8px] uppercase tracking-[.18em] backdrop-blur">{{ $badge }}</span>
                    @endif
                    <span class="absolute bottom-5 left-5 text-[8px] uppercase tracking-[.18em] text-black/35">01 / Product</span>
                </figure>

                <figure class="relative hidden min-h-[620px] overflow-hidden bg-[#e9e5dc] sm:block lg:min-h-[780px]">
                    <img src="{{ $worldImage }}" alt="{{ $name }} atmosphere" class="absolute inset-0 h-full w-full object-cover transition duration-700 hover:scale-[1.01]">
                    <div class="absolute inset-0 bg-black/[.04]"></div>
                    <span class="absolute bottom-5 left-5 text-[8px] uppercase tracking-[.18em] text-white/65">02 / Atmosphere</span>
                </figure>
            </div>

            <!-- Purchase -->
            <aside class="relative bg-white">
                <div class="px-6 py-10 sm:px-10 lg:sticky lg:top-[108px] lg:px-11 lg:py-12 xl:px-14">
                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <p class="text-[9px] uppercase tracking-[.19em] text-black/40">{{ $family }}</p>
                            <h1 class="mt-3 display-serif text-[3.4rem] leading-[.92] sm:text-[4.2rem]">{{ $name }}</h1>
                            <p class="mt-4 text-xs uppercase tracking-[.12em] text-black/45">Eau de Parfum</p>
                        </div>
                        <button
                            @click="$store.commerce.toggleWishlist({ product_id:@js($visual['id'] ?? null), slug:@js($slug), name:@js($name), family:@js($family), price:@js($price), price_value:@js($visual['price_value'] ?? (float) str_replace(',', '', (string) $price)), image:@js($image) })"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-black/15 text-xl transition hover:border-black hover:bg-black hover:text-white"
                            aria-label="Toggle wishlist"
                        >
                            <span x-text="$store.commerce.inWishlist(@js($visual['id'] ?? $slug)) ? '♥' : '♡'">♡</span>
                        </button>
                    </div>

                    <p class="mt-7 max-w-md text-[13px] leading-6 text-black/58">{{ $story }}</p>

                    <div class="mt-8 flex items-center justify-between border-y border-black/10 py-5">
                        <span class="text-[10px] uppercase tracking-[.14em] text-black/45" x-text="size"></span>
                        <span class="text-sm font-medium">PKR <span x-text="currentPrice().toLocaleString()"></span></span>
                    </div>

                    <div class="mt-7">
                        <div class="flex items-center justify-between">
                            <p class="text-[9px] uppercase tracking-[.17em] text-black/45">Select size</p>
                            <span class="text-[9px] uppercase tracking-[.14em] {{ $inStock ? 'text-black/35' : 'text-red-600' }}">{{ $inStock ? 'In stock' : 'Out of stock' }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach($variants as $variant)
                                @php
                                    $variantSize = $variant['size_label'] ?? $variant['name'] ?? 'Size';
                                    $variantStock = (int) ($variant['stock'] ?? 0);
                                @endphp
                                <button
                                    @click="size=@js($variantSize); qty=Math.min(qty, Math.max(1,maxQty()))"
                                    :class="size===@js($variantSize) ? 'border-black bg-black text-white' : 'border-black/15 bg-white text-black hover:border-black/50'"
                                    class="min-h-[50px] border px-2 text-[9px] uppercase tracking-[.14em] transition disabled:cursor-not-allowed disabled:opacity-35"
                                    @disabled($variantStock <= 0)
                                >{{ $variantSize }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-[106px_1fr] gap-2">
                        <div class="grid min-h-[56px] grid-cols-3 border border-black/15">
                            <button @click="qty=Math.max(1,qty-1)" class="transition hover:bg-black/5" aria-label="Decrease quantity">−</button>
                            <div class="flex items-center justify-center text-xs" x-text="qty">1</div>
                            <button @click="qty=Math.min(Math.max(1,maxQty()),qty+1)" :disabled="maxQty() <= qty" class="transition hover:bg-black/5 disabled:opacity-30" aria-label="Increase quantity">+</button>
                        </div>
                        <button
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
                                qty:qty
                            })"
                            class="min-h-[56px] bg-black px-5 text-[9px] font-medium uppercase tracking-[.18em] text-white transition hover:bg-black/82"
                            @disabled(!$inStock)
                        <span x-show="maxQty() > 0">Add to bag · PKR <span x-text="currentPrice().toLocaleString()"></span></span><span x-show="maxQty() <= 0">Out of stock</span></button>
                    </div>

                    <a href="{{ route('gifting') }}" class="mt-2 flex min-h-[48px] items-center justify-center border border-black/15 text-[9px] uppercase tracking-[.17em] transition hover:border-black">
                        Send as a gift
                    </a>

                    <div class="mt-8 divide-y divide-black/10 border-y border-black/10">
                        @foreach([
                            ['Delivery','Complimentary on selected orders'],
                            ['Presentation','Signature gift wrapping available'],
                            ['Returns','Easy returns on eligible orders']
                        ] as [$title,$copy])
                            <div class="grid grid-cols-[105px_1fr] gap-4 py-4">
                                <span class="text-[9px] uppercase tracking-[.14em] text-black/42">{{ $title }}</span>
                                <span class="text-[11px] leading-5 text-black/60">{{ $copy }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <!-- PRODUCT IDENTITY -->
    <section class="bg-white">
        <div class="house-container py-16 lg:py-24">
            <div class="grid gap-10 border-b border-black/10 pb-14 lg:grid-cols-[.52fr_1.48fr] lg:pb-20">
                <div>
                    <p class="ui-label text-black/35">The fragrance</p>
                    <p class="mt-4 max-w-xs text-xs leading-6 text-black/45">An introduction to the scent, its structure and the way it sits on skin.</p>
                </div>
                <div>
                    <p class="display-serif max-w-5xl text-4xl leading-[1.04] sm:text-6xl lg:text-[4.6rem]">{{ $story }}</p>
                    <div class="mt-10 grid gap-5 border-t border-black/10 pt-6 sm:grid-cols-3">
                        <div><p class="ui-label text-black/30">Mood</p><p class="mt-3 text-sm">{{ $world['mood'] ?? 'Modern · Textural · Intimate' }}</p></div>
                        <div><p class="ui-label text-black/30">Family</p><p class="mt-3 text-sm">{{ $family }}</p></div>
                        <div><p class="ui-label text-black/30">Wear</p><p class="mt-3 text-sm">Eau de Parfum · Day to evening</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NOTES / STORY / WEAR -->
    <section class="bg-[#0b0b0b] text-white">
        <div class="house-container py-16 lg:py-24">
            <div class="grid gap-12 lg:grid-cols-[.38fr_1.62fr]">
                <div>
                    <p class="ui-label text-white/35">Composition</p>
                    <div class="mt-8 grid gap-3 lg:sticky lg:top-[130px]">
                        <button @click="details='notes'" :class="details==='notes' ? 'text-white' : 'text-white/28'" class="text-left display-serif text-3xl transition">Notes</button>
                        <button @click="details='story'" :class="details==='story' ? 'text-white' : 'text-white/28'" class="text-left display-serif text-3xl transition">Story</button>
                        <button @click="details='wear'" :class="details==='wear' ? 'text-white' : 'text-white/28'" class="text-left display-serif text-3xl transition">Wear</button>
                    </div>
                </div>

                <div class="min-h-[390px]">
                    <div x-show="details==='notes'">
                        <div class="border-y border-white/15">
                            @foreach([
                                ['01','Top',$topNotes,'The opening impression'],
                                ['02','Heart',$heartNotes,'The central character'],
                                ['03','Base',$baseNotes,'The lasting trail'],
                            ] as [$index,$stage,$notes,$copy])
                                <div class="grid gap-4 border-b border-white/12 py-7 last:border-b-0 sm:grid-cols-[50px_110px_1fr_auto] sm:items-center">
                                    <span class="text-[9px] tracking-[.16em] text-white/25">{{ $index }}</span>
                                    <span class="ui-label text-white/35">{{ $stage }}</span>
                                    <span class="display-serif text-3xl sm:text-4xl">{{ $notes }}</span>
                                    <span class="hidden text-[10px] text-white/30 sm:block">{{ $copy }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div x-cloak x-show="details==='story'">
                        <p class="display-serif max-w-5xl text-4xl leading-[1.05] sm:text-6xl">{{ $world['statement'] ?? $story }}</p>
                        <p class="mt-8 max-w-2xl text-sm leading-7 text-white/50">{{ $story }}</p>
                    </div>

                    <div x-cloak x-show="details==='wear'">
                        <div class="grid gap-10 lg:grid-cols-[1.15fr_.85fr]">
                            <p class="display-serif text-4xl leading-[1.05] sm:text-6xl">{{ $performance }}</p>
                            <div class="border border-white/15 p-7">
                                <p class="ui-label text-white/35">Performance profile</p>
                                <div class="mt-7 space-y-6">
                                    @foreach([['Projection','Balanced','68%'],['Longevity','Long wearing','80%'],['Presence','Refined','62%']] as [$label,$value,$width])
                                        <div>
                                            <div class="flex justify-between text-xs text-white/65"><span>{{ $label }}</span><span>{{ $value }}</span></div>
                                            <div class="mt-3 h-px bg-white/15"><div class="h-px bg-white" style="width:{{ $width }}"></div></div>
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

    <!-- ATMOSPHERE -->
    <section class="bg-[#f5f3ee] text-black">
        <div class="house-container py-4 sm:py-6 lg:py-10">
            <div class="grid overflow-hidden lg:grid-cols-[1.2fr_.8fr]">
                <div class="relative min-h-[560px] lg:min-h-[720px]">
                    <img src="{{ $worldImage }}" alt="{{ $name }} world" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover">
                </div>
                <div class="flex min-h-[430px] items-end bg-[var(--product-surface)] p-8 text-[var(--product-ink)] sm:p-12 lg:min-h-[720px] lg:p-14">
                    <div>
                        <p class="ui-label opacity-40">{{ $world['kicker'] ?? 'The scent world' }}</p>
                        <h2 class="mt-4 display-serif text-5xl leading-[.94] sm:text-6xl">{{ $world['statement'] ?? 'A fragrance shaped by texture and memory.' }}</h2>
                        <p class="mt-8 text-[10px] uppercase tracking-[.15em] opacity-45">{{ $world['mood'] ?? $family }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MATERIALS -->
    <section class="bg-white text-black">
        <div class="house-container py-16 lg:py-24">
            <div class="flex flex-col gap-6 border-b border-black/10 pb-8 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="ui-label text-black/35">Material study</p><h2 class="mt-3 display-serif text-5xl leading-[.94] sm:text-6xl">Inside {{ $name }}</h2></div>
                <a href="{{ route('ingredients') }}" class="text-link">Explore all materials →</a>
            </div>
            <div class="grid gap-px bg-black/10 sm:grid-cols-3">
                @foreach($materials as $material)
                    <a href="{{ route('ingredients.show',$material['slug']) }}" class="group bg-[#f7f6f2] p-7 transition hover:bg-[#efebe3] sm:min-h-[280px]">
                        <div class="flex h-full flex-col justify-between">
                            <span class="ui-label text-black/30">Material</span>
                            <div class="mt-20"><h3 class="display-serif text-4xl sm:text-5xl">{{ $material['name'] }}</h3><p class="mt-3 text-[10px] uppercase tracking-[.12em] text-black/40">{{ $material['desc'] }}</p><span class="mt-6 inline-block text-link">Discover →</span></div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- HOUSE RITUAL -->
    <section class="grid bg-[#101010] text-white lg:grid-cols-2">
        <div class="relative min-h-[500px] overflow-hidden lg:min-h-[640px]">
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-black/8"></div>
        </div>
        <div class="flex min-h-[500px] items-center p-8 sm:p-12 lg:min-h-[640px] lg:p-16">
            <div class="max-w-xl">
                <p class="ui-label text-white/35">House ritual</p>
                <h2 class="mt-4 display-serif text-5xl leading-[.95] sm:text-6xl">Wear close. Let it evolve.</h2>
                <p class="mt-6 text-sm leading-7 text-white/52">Apply to pulse points and allow the fragrance to settle naturally. Avoid rubbing the skin so the composition can develop in its own rhythm.</p>
                <div class="mt-9 grid gap-6 border-t border-white/15 pt-6 sm:grid-cols-2">
                    <div><p class="ui-label text-white/30">Best moment</p><p class="mt-2 text-sm text-white/70">Day into evening</p></div>
                    <div><p class="ui-label text-white/30">Character</p><p class="mt-2 text-sm text-white/70">{{ $world['mood'] ?? 'Textural · Intimate' }}</p></div>
                </div>
            </div>
        </div>
    </section>

    <!-- RELATED -->
    <section class="bg-[#f7f6f2] text-black">
        <div class="house-container py-16 lg:py-24">
            <div class="mb-9 flex items-end justify-between gap-6">
                <div><p class="ui-label text-black/35">You may also like</p><h2 class="mt-3 display-serif text-5xl sm:text-6xl">Explore next</h2></div>
                <a href="{{ route('shop') }}" class="text-link hidden sm:block">View all fragrances →</a>
            </div>
            <div class="grid gap-x-4 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(($relatedProducts ?? collect($catalog)->except($slug)->map(fn($p,$s)=>array_merge($p,['slug'=>$s]))->values()->take(4)) as $related)
                    <x-house.product-card :product="$related" :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)" />
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

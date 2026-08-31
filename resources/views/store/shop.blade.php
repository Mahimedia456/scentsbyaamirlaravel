@extends('layouts.store')

@php
    $filters = $filters ?? [];
    $audience = $filters['audience'] ?? '';
    $family = $filters['family'] ?? '';
    $category = $filters['category'] ?? '';
    $collection = $filters['collection'] ?? '';
    $availability = $filters['availability'] ?? '';
    $edit = $filters['edit'] ?? '';
    $search = $filters['search'] ?? '';
    $catalogHero = file_exists(public_path('images/catalog/fragrances-hero.webp'))
        ? asset('images/catalog/fragrances-hero.webp')
        : null;

    $activeFilterCount = collect([
        $audience, $family, $category, $collection, $availability,
        $filters['min_price'] ?? null, $filters['max_price'] ?? null,
    ])->filter(fn ($value) => $value !== null && $value !== '')->count();
@endphp

@section('title', 'Fragrances — Scents by Aamir')
@section('description', 'Explore the Scents by Aamir fragrance wardrobe across men, women, unisex and distinctive fragrance families.')

@section('content')
<div
    x-data="catalogAjax({
        initialAudience: @js($audience),
        initialEdit: @js($edit),
        endpoint: @js(route('shop'))
    })"
    class="bg-[#f6f4ef] text-black"
>

    {{-- CATALOG HERO --}}
    <section class="relative overflow-hidden bg-[#0b0b0b] pt-[102px] text-white sm:pt-[108px]">
        <div class="relative min-h-[430px] sm:min-h-[500px] lg:min-h-[560px]">
            @if($catalogHero)
                <img
                    src="{{ $catalogHero }}"
                    alt="Scents by Aamir fragrance wardrobe"
                    class="absolute inset-0 h-full w-full object-cover object-center"
                    fetchpriority="high"
                >
            @endif

            <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.92)_0%,rgba(0,0,0,.73)_38%,rgba(0,0,0,.30)_67%,rgba(0,0,0,.48)_100%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/58 via-transparent to-black/10"></div>

            <div class="house-container relative flex min-h-[430px] items-end py-14 sm:min-h-[500px] sm:py-16 lg:min-h-[560px] lg:items-center lg:py-20">
                <div class="max-w-[660px]" data-reveal>
                    <div class="flex items-center gap-4">
                        <span class="h-px w-10 bg-[#c9ad7a]"></span>
                        <p class="ui-label text-white/55">The Fragrance Wardrobe</p>
                    </div>

                    <h1 class="mt-5 display-serif text-[48px] leading-[.92] tracking-[-.035em] sm:text-[60px] lg:text-[74px]">
                        Fragrances for every atmosphere.
                    </h1>

                    <p class="mt-6 max-w-xl text-sm leading-7 text-white/62">
                        Discover the complete house through character, material and mood — from bright daily signatures to deeper evening compositions.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#catalog" class="btn-solid bg-white text-black hover:bg-[#d2bd98]">Explore the wardrobe</a>
                        <a href="{{ route('finder') }}" class="btn-outline border-white/35 text-white hover:bg-white hover:text-black">Find your scent</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- AUDIENCE NAVIGATION — CENTERED + AJAX --}}
    <section class="border-b border-black/10 bg-[#f6f4ef]">
        <div class="sba-catalog-tabbar house-container overflow-x-auto">
            <nav
                class="mx-auto flex min-w-max items-center justify-center gap-8 py-6 sm:gap-11 lg:gap-14"
                aria-label="Fragrance audience"
            >
                @foreach([
                    ['label' => 'All Fragrances', 'audience' => '', 'edit' => ''],
                    ['label' => 'Men', 'audience' => 'men', 'edit' => ''],
                    ['label' => 'Women', 'audience' => 'women', 'edit' => ''],
                    ['label' => 'Unisex', 'audience' => 'unisex', 'edit' => ''],
                    ['label' => 'New', 'audience' => '', 'edit' => 'new'],
                    ['label' => 'House Favourites', 'audience' => '', 'edit' => 'featured'],
                ] as $tab)
                    @php
                        $tabUrl = route('shop', array_filter([
                            'audience' => $tab['audience'],
                            'edit' => $tab['edit'],
                            'sort' => $activeSort !== 'featured' ? $activeSort : null,
                        ]));
                    @endphp

                    <a
                        href="{{ $tabUrl }}#catalog"
                        @click.prevent="loadEdit(@js($tab['audience']), @js($tab['edit']), @js($tabUrl))"
                        class="relative whitespace-nowrap pb-2 ui-label transition-colors"
                        :class="isActive(@js($tab['audience']), @js($tab['edit']))
                            ? 'text-black'
                            : 'text-black/38 hover:text-black'"
                        :aria-current="isActive(@js($tab['audience']), @js($tab['edit'])) ? 'page' : null"
                    >
                        {{ $tab['label'] }}
                        <span
                            class="absolute inset-x-0 bottom-0 h-px bg-black transition-all duration-300"
                            :class="isActive(@js($tab['audience']), @js($tab['edit'])) ? 'scale-x-100 opacity-100' : 'scale-x-0 opacity-0'"
                        ></span>
                    </a>
                @endforeach
            </nav>
        </div>
    </section>

    {{-- TOOLBAR --}}
    <section id="catalog" class="sticky top-[100px] z-30 border-b border-black/10 bg-[#f6f4ef]/95 backdrop-blur-xl">
        <div class="house-container flex min-h-[64px] items-center justify-between gap-4">
            <button
                type="button"
                @click="window.innerWidth < 768 ? mobileFilters = true : filters = !filters"
                class="flex items-center gap-2 ui-label"
            >
                <span>Filter</span>
                @if($activeFilterCount)
                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-black px-1.5 text-[9px] text-white">{{ $activeFilterCount }}</span>
                @else
                    <span>+</span>
                @endif
            </button>

            <form action="{{ route('shop') }}" method="GET" class="hidden w-full max-w-md md:block">
                @if($audience)<input type="hidden" name="audience" value="{{ $audience }}">@endif
                @if($family)<input type="hidden" name="family" value="{{ $family }}">@endif
                @if($activeSort)<input type="hidden" name="sort" value="{{ $activeSort }}">@endif
                <label class="flex items-center gap-3 border-b border-black/20 py-2">
                    <span class="text-black/35">⌕</span>
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search the fragrance wardrobe"
                        class="w-full border-0 bg-transparent p-0 text-[12px] outline-none placeholder:text-black/32 focus:ring-0"
                    >
                </label>
            </form>

            <div class="relative">
                <button type="button" @click="sort = !sort" class="ui-label">Sort ↕</button>

                <div
                    x-cloak
                    x-show="sort"
                    @click.outside="sort=false"
                    x-transition.opacity
                    class="absolute right-0 top-[calc(100%+22px)] w-64 border border-black/10 bg-white p-5 shadow-2xl"
                >
                    <div class="grid gap-4 text-[12px]">
                        @foreach([
                            'featured' => 'Featured',
                            'newest' => 'Newest',
                            'name-asc' => 'Name: A–Z',
                            'price-asc' => 'Price: Low to High',
                            'price-desc' => 'Price: High to Low',
                        ] as $value => $label)
                            <a
                                href="{{ request()->fullUrlWithQuery(['sort' => $value]) }}#catalog"
                                class="flex items-center justify-between {{ $activeSort === $value ? 'font-medium text-black' : 'text-black/55 hover:text-black' }}"
                            >
                                <span>{{ $label }}</span>
                                @if($activeSort === $value)<span>✓</span>@endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- DESKTOP FILTER PANEL --}}
        <div x-cloak x-show="filters" x-transition class="hidden border-t border-black/10 bg-white md:block">
            <form action="{{ route('shop') }}" method="GET" class="house-container py-8">
                @if($search)<input type="hidden" name="q" value="{{ $search }}">@endif
                @if($activeSort)<input type="hidden" name="sort" value="{{ $activeSort }}">@endif
                @if($edit)<input type="hidden" name="edit" value="{{ $edit }}">@endif

                <div class="grid gap-8 lg:grid-cols-[.9fr_1fr_1fr_1fr_auto]">
                    <div>
                        <label class="ui-label text-black/35" for="audience-desktop">For</label>
                        <select id="audience-desktop" name="audience" class="mt-4 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                            <option value="">Everyone</option>
                            <option value="men" @selected($audience === 'men')>Men</option>
                            <option value="women" @selected($audience === 'women')>Women</option>
                            <option value="unisex" @selected($audience === 'unisex')>Unisex</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label text-black/35" for="family-desktop">Fragrance family</label>
                        <select id="family-desktop" name="family" class="mt-4 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                            <option value="">All families</option>
                            @foreach(['Fresh','Citrus','Floral','Woody','Oud','Amber','Musk','Spicy'] as $item)
                                <option value="{{ strtolower($item) }}" @selected(strtolower($family) === strtolower($item))>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label text-black/35" for="collection-desktop">Collection</label>
                        <select id="collection-desktop" name="collection" class="mt-4 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                            <option value="">All collections</option>
                            @foreach($collections as $item)
                                <option value="{{ $item['slug'] }}" @selected($collection === $item['slug'])>{{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label text-black/35" for="availability-desktop">Availability</label>
                        <select id="availability-desktop" name="availability" class="mt-4 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                            <option value="">All products</option>
                            <option value="in-stock" @selected($availability === 'in-stock')>In stock</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <button type="submit" class="btn-solid min-w-[128px]">Apply</button>
                        <a href="{{ route('shop') }}#catalog" class="px-3 py-3 ui-label text-black/45 hover:text-black">Reset</a>
                    </div>
                </div>

                <div class="mt-7 grid gap-6 border-t border-black/10 pt-6 sm:grid-cols-2 lg:max-w-2xl">
                    <label>
                        <span class="ui-label text-black/35">Minimum PKR</span>
                        <input type="number" min="0" step="100" name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="0" class="mt-3 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                    </label>
                    <label>
                        <span class="ui-label text-black/35">Maximum PKR</span>
                        <input type="number" min="0" step="100" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="Any" class="mt-3 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                    </label>
                </div>
            </form>
        </div>
    </section>
    {{-- RESULTS --}}
    <section class="house-container relative py-10 lg:py-14">
        <div
            x-cloak
            x-show="loading"
            x-transition.opacity
            class="pointer-events-none absolute inset-x-0 top-0 z-20 flex h-16 items-center justify-center"
            aria-hidden="true"
        >
            <span class="ui-label rounded-full border border-black/10 bg-[#f6f4ef]/95 px-5 py-3 shadow-sm backdrop-blur">
                Updating fragrance edit…
            </span>
        </div>

        <div
            x-ref="results"
            :class="loading ? 'opacity-45' : 'opacity-100'"
            class="transition-opacity duration-300"
        >
            @include('store.partials.catalog-results')
        </div>
    </section>

    {{-- CATALOG END NOTE --}}
    <section class="border-t border-black/10 bg-[#ece8df]">
        <div class="house-container grid gap-8 py-14 sm:py-16 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <p class="ui-label text-black/35">Not sure where to begin?</p>
                <h2 class="mt-3 max-w-3xl display-serif text-[42px] leading-[.96] sm:text-[54px]">Choose by feeling, not by formula.</h2>
                <p class="mt-5 max-w-xl text-sm leading-7 text-black/50">Use the fragrance finder to move through mood, freshness, depth and occasion.</p>
            </div>
            <a href="{{ route('finder') }}" class="btn-solid">Start fragrance finder</a>
        </div>
    </section>

    {{-- MOBILE FILTER DRAWER --}}
    <div x-cloak x-show="mobileFilters" class="fixed inset-0 z-[90] md:hidden">
        <div class="absolute inset-0 bg-black/50" @click="mobileFilters=false"></div>

        <aside
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition duration-250 ease-in"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="absolute bottom-0 left-0 top-0 w-[92%] max-w-[430px] overflow-y-auto bg-[#f6f4ef] p-6"
        >
            <div class="flex items-center justify-between border-b border-black/10 pb-5">
                <span class="ui-label">Refine Fragrances</span>
                <button type="button" @click="mobileFilters=false" class="ui-label">Close</button>
            </div>

            <form action="{{ route('shop') }}" method="GET" class="py-2">
                @if($activeSort)<input type="hidden" name="sort" value="{{ $activeSort }}">@endif
                @if($edit)<input type="hidden" name="edit" value="{{ $edit }}">@endif

                <label class="block border-b border-black/10 py-5">
                    <span class="ui-label text-black/35">Search</span>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Fragrance name or note" class="mt-3 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:border-black focus:ring-0">
                </label>

                <label class="block border-b border-black/10 py-5">
                    <span class="ui-label text-black/35">For</span>
                    <select name="audience" class="mt-3 w-full border-0 bg-transparent px-0 py-3 text-sm focus:ring-0">
                        <option value="">Everyone</option>
                        <option value="men" @selected($audience === 'men')>Men</option>
                        <option value="women" @selected($audience === 'women')>Women</option>
                        <option value="unisex" @selected($audience === 'unisex')>Unisex</option>
                    </select>
                </label>

                <label class="block border-b border-black/10 py-5">
                    <span class="ui-label text-black/35">Fragrance family</span>
                    <select name="family" class="mt-3 w-full border-0 bg-transparent px-0 py-3 text-sm focus:ring-0">
                        <option value="">All families</option>
                        @foreach(['Fresh','Citrus','Floral','Woody','Oud','Amber','Musk','Spicy'] as $item)
                            <option value="{{ strtolower($item) }}" @selected(strtolower($family) === strtolower($item))>{{ $item }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block border-b border-black/10 py-5">
                    <span class="ui-label text-black/35">Collection</span>
                    <select name="collection" class="mt-3 w-full border-0 bg-transparent px-0 py-3 text-sm focus:ring-0">
                        <option value="">All collections</option>
                        @foreach($collections as $item)
                            <option value="{{ $item['slug'] }}" @selected($collection === $item['slug'])>{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block border-b border-black/10 py-5">
                    <span class="ui-label text-black/35">Availability</span>
                    <select name="availability" class="mt-3 w-full border-0 bg-transparent px-0 py-3 text-sm focus:ring-0">
                        <option value="">All products</option>
                        <option value="in-stock" @selected($availability === 'in-stock')>In stock</option>
                    </select>
                </label>

                <div class="grid grid-cols-2 gap-4 border-b border-black/10 py-5">
                    <label>
                        <span class="ui-label text-black/35">Min PKR</span>
                        <input type="number" min="0" step="100" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="mt-3 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:ring-0">
                    </label>
                    <label>
                        <span class="ui-label text-black/35">Max PKR</span>
                        <input type="number" min="0" step="100" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="mt-3 w-full border-0 border-b border-black/15 bg-transparent px-0 py-3 text-sm focus:ring-0">
                    </label>
                </div>

                <div class="sticky bottom-0 -mx-6 mt-5 grid grid-cols-[1fr_auto] gap-3 border-t border-black/10 bg-[#f6f4ef] px-6 py-5">
                    <button type="submit" class="btn-solid">Show fragrances</button>
                    <a href="{{ route('shop') }}#catalog" class="flex items-center px-3 ui-label text-black/45">Reset</a>
                </div>
            </form>
        </aside>
    </div>
</div>
@endsection

@extends('layouts.store')

@section('title', 'Fragrances — Scents by Aamir')

@section('content')
<div x-data="{ filters:false, sort:false, mobileFilters:false, active:'All' }">
<section class="bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container py-14 lg:py-20">
        <div class="grid gap-8 lg:grid-cols-[1.25fr_.75fr] lg:items-end">
            <div>
                <p class="ui-label text-black/35">The Fragrance Wardrobe</p>
                <h1 class="mt-3 display-serif text-6xl leading-[.88] sm:text-8xl lg:text-[8.5rem]">Fragrances</h1>
            </div>
            <p class="max-w-md text-sm leading-7 text-black/52">Explore the complete house through mood, material and atmosphere.</p>
        </div>
    </div>

    <div class="sticky top-[100px] z-30 border-y border-black/10 bg-[#f7f6f2]/95 backdrop-blur-xl">
        <div class="house-container flex min-h-[56px] items-center justify-between gap-5">
            <button @click="window.innerWidth < 768 ? mobileFilters=true : filters=!filters" class="ui-label">Filter +</button>
            <div class="hidden items-center gap-7 md:flex">
                @foreach(['All','New','Best Sellers','Woody','Amber'] as $tab)
                    <button @click="active='{{ $tab }}'" class="ui-label"
                            :class="active==='{{ $tab }}' ? 'text-black border-b border-black pb-1' : 'text-black/35'">{{ $tab }}</button>
                @endforeach
            </div>
            <button @click="sort=!sort" class="ui-label">Sort ↕</button>
        </div>

        <div x-cloak x-show="filters" class="hidden border-t border-black/10 bg-white md:block">
            <div class="house-container grid gap-10 py-8 lg:grid-cols-[1fr_1fr_1fr_auto]">
                @foreach([
                    ['Family',['Woody','Amber','Oud','Citrus','Musky']],
                    ['Intensity',['Soft','Moderate','Strong']],
                    ['Occasion',['Everyday','Evening','Formal','Gifting']]
                ] as [$label,$items])
                    <div>
                        <p class="ui-label text-black/35">{{ $label }}</p>
                        <div class="mt-5 grid gap-3 text-sm">
                            @foreach($items as $item)
                                <label class="flex items-center justify-between gap-6"><span>{{ $item }}</span><input type="checkbox"></label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="flex items-end"><button class="btn-solid min-w-[180px]">Apply</button></div>
            </div>
        </div>

        <div x-cloak x-show="sort" @click.outside="sort=false" class="absolute right-4 top-full w-60 border border-black/10 bg-white p-5 shadow-xl sm:right-10">
            <div class="grid gap-3 text-sm">
                <a href="{{ request()->fullUrlWithQuery(['sort'=>'featured']) }}" class="text-left {{ $activeSort === 'featured' ? 'font-medium' : '' }}">Featured</a>
                <a href="{{ request()->fullUrlWithQuery(['sort'=>'newest']) }}" class="text-left {{ $activeSort === 'newest' ? 'font-medium' : '' }}">Newest</a>
                <a href="{{ request()->fullUrlWithQuery(['sort'=>'price-asc']) }}" class="text-left {{ $activeSort === 'price-asc' ? 'font-medium' : '' }}">Price: Low to High</a>
                <a href="{{ request()->fullUrlWithQuery(['sort'=>'price-desc']) }}" class="text-left {{ $activeSort === 'price-desc' ? 'font-medium' : '' }}">Price: High to Low</a>
            </div>
        </div>
    </div>

    <div class="house-container py-9 lg:py-12">
        <div class="mb-8 flex items-center justify-between">
            <span class="ui-label text-black/35">{{ $products->count() }} Products</span>
            <span class="ui-label text-black/35" x-text="active"></span>
        </div>

        <div class="grid gap-x-4 gap-y-14 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($products as $p)
                <x-house.product-card
                    :product="$p"
                    :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                    :tone="in_array($p['slug'], ['velvet-oud','after-dark','oud-noir','ember','rose-smoke','midnight-resin']) ? 'dark' : 'light'"
                />
            @empty
                <div class="col-span-full border border-black/10 bg-white p-10 text-center">
                    <p class="display-serif text-4xl">No fragrances found.</p>
                    <a href="{{ route('shop') }}" class="mt-5 inline-block text-link">Clear filters →</a>
                </div>
            @endforelse
        </div>
    </div>
</section>

<div x-cloak x-show="mobileFilters" class="fixed inset-0 z-[85] md:hidden">
    <div class="absolute inset-0 bg-black/35" @click="mobileFilters=false"></div>
    <aside class="absolute bottom-0 left-0 top-0 w-[90%] max-w-[430px] overflow-y-auto bg-[#f7f6f2] p-6">
        <div class="flex items-center justify-between border-b border-black/10 pb-5">
            <span class="ui-label">Filters</span>
            <button @click="mobileFilters=false" class="ui-label">Close</button>
        </div>
        @foreach([
            ['Family',['Woody','Amber','Oud','Citrus','Musky']],
            ['Intensity',['Soft','Moderate','Strong']],
            ['Occasion',['Everyday','Evening','Formal','Gifting']]
        ] as [$label,$items])
            <div class="border-b border-black/10 py-6">
                <p class="ui-label text-black/35">{{ $label }}</p>
                <div class="mt-4 grid gap-4 text-sm">
                    @foreach($items as $item)
                        <label class="flex items-center justify-between"><span>{{ $item }}</span><input type="checkbox"></label>
                    @endforeach
                </div>
            </div>
        @endforeach
        <button @click="mobileFilters=false" class="btn-solid mt-6 w-full">Show Products</button>
    </aside>
</div>
</div>
@endsection

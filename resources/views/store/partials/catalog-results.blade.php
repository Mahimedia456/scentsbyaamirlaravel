@php
    $filters = $filters ?? [];
    $audience = $filters['audience'] ?? '';
    $edit = $filters['edit'] ?? '';
    $search = $filters['search'] ?? '';
    $activeSort = $activeSort ?? ($filters['sort'] ?? 'featured');
@endphp

<div class="mb-9 flex flex-wrap items-end justify-between gap-5 border-b border-black/10 pb-6">
    <div>
        <p class="ui-label text-black/35">Current Edit</p>
        <h2 class="mt-2 display-serif text-[38px] leading-none sm:text-[44px]">
            @if($audience === 'men')
                Men’s Fragrances
            @elseif($audience === 'women')
                Women’s Fragrances
            @elseif($audience === 'unisex')
                Unisex Fragrances
            @elseif($edit === 'new')
                New Arrivals
            @elseif($edit === 'featured')
                House Favourites
            @else
                All Fragrances
            @endif
        </h2>
    </div>

    <p class="ui-label text-black/38">
        {{ $products->count() }} {{ $products->count() === 1 ? 'fragrance' : 'fragrances' }}
    </p>
</div>

@if($search)
    <div class="mb-8 flex items-center justify-between border border-black/10 bg-white px-5 py-4 text-sm">
        <span>Results for “{{ $search }}”</span>
        <a
            href="{{ route('shop', array_filter(['audience' => $audience, 'sort' => $activeSort])) }}#catalog"
            class="ui-label text-black/45 hover:text-black"
        >
            Clear search
        </a>
    </div>
@endif

<div class="grid gap-x-4 gap-y-14 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @forelse($products as $p)
        <x-house.product-card
            :product="$p"
            :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
            :tone="in_array($p['slug'] ?? '', [
                'dark-seduction',
                'smoky-chic',
                'dark-aure-inspired-by-nuit-de-feu-intense-smoky-arabic-unisex-perfume',
                'desert-soul-inspired-by-ombre-nomade-dark-oud-unisex-perfume'
            ]) ? 'dark' : 'light'"
        />
    @empty
        <div class="col-span-full border border-black/10 bg-white px-6 py-20 text-center">
            <p class="ui-label text-black/35">Nothing matched this edit</p>
            <h3 class="mt-4 display-serif text-[42px] leading-tight sm:text-[52px]">Try a wider fragrance selection.</h3>
            <p class="mx-auto mt-5 max-w-md text-sm leading-7 text-black/48">
                Clear one or more filters to return to the complete Scents by Aamir wardrobe.
            </p>
            <a href="{{ route('shop') }}#catalog" class="btn-solid mt-7">View all fragrances</a>
        </div>
    @endforelse
</div>

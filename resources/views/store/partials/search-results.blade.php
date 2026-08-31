@php
    $q = trim((string) ($q ?? ''));
    $results = collect($results ?? []);
@endphp

@if($q !== '')
    <div class="flex items-end justify-between gap-5 border-b border-black/10 pb-5">
        <div>
            <p class="ui-label text-black/35">Search results</p>
            <h2 class="mt-2 display-serif text-[34px] leading-tight sm:text-[42px]">
                Results for “{{ $q }}”
            </h2>
        </div>
        <span class="ui-label text-black/35">{{ $results->count() }} found</span>
    </div>

    @if($results->isNotEmpty())
        <div class="mt-8 grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($results as $product)
                <x-house.product-card
                    :product="$product"
                    :index="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                />
            @endforeach
        </div>
    @else
        <div class="py-20 text-center">
            <p class="display-serif text-[44px] leading-tight sm:text-[54px]">Nothing matched that search.</p>
            <p class="mx-auto mt-4 max-w-lg text-sm leading-7 text-black/50">
                Try a product name, ingredient, fragrance family, Men, Women, Unisex, fresh, floral, oud or amber.
            </p>

            <div class="mt-7 flex flex-wrap justify-center gap-3">
                <a href="{{ route('finder') }}" class="btn-solid">Try the fragrance finder</a>
                <a href="{{ route('shop') }}" class="btn-outline">Browse all fragrances</a>
            </div>
        </div>
    @endif
@else
    <div class="grid gap-px bg-black/10 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['Men', 'Structured and refined', route('shop', ['audience'=>'men'])],
            ['Women', 'Radiant and expressive', route('shop', ['audience'=>'women'])],
            ['Unisex', 'Fluid and individual', route('shop', ['audience'=>'unisex'])],
            ['Materials', 'Oud, rose, amber, citrus', route('ingredients')],
        ] as [$title,$copy,$href])
            <a href="{{ $href }}" class="group bg-white p-7 transition hover:bg-[#efebe3]">
                <p class="ui-label text-black/35">Discover</p>
                <h3 class="mt-4 display-serif text-[34px]">{{ $title }}</h3>
                <p class="mt-4 text-xs leading-6 text-black/42">{{ $copy }}</p>
                <span class="mt-7 inline-block transition-transform group-hover:translate-x-1">→</span>
            </a>
        @endforeach
    </div>
@endif

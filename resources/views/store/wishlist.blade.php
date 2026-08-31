@extends('layouts.store')

@section('title', 'Wishlist — Scents by Aamir')

@section('content')
<section x-data x-init="$store.commerce.syncWishlist()" class="min-h-screen bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container py-14 lg:py-20">
        <p class="ui-label text-black/35">Saved Selection</p>
        <div class="mt-4 flex items-end justify-between gap-8">
            <h1 class="display-serif text-6xl leading-[.88] sm:text-8xl">Wishlist</h1>
            <p class="ui-label text-black/35"><span x-text="$store.commerce.wishlist.length"></span> saved</p>
        </div>
    </div>

    <div class="house-container pb-20">
        <template x-if="$store.commerce.wishlist.length === 0">
            <div class="border-t border-black/10 py-24 text-center">
                <p class="display-serif text-5xl">Nothing saved yet.</p>
                <p class="mt-4 text-sm text-black/50">Keep the fragrances you want to return to.</p>
                <a href="{{ route('shop') }}" class="btn-solid mt-7">Explore fragrances</a>
            </div>
        </template>

        <div class="grid gap-x-4 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
            <template x-for="item in $store.commerce.wishlist" :key="item.product_id || item.slug">
                <article class="group" :class="item.available === false ? 'opacity-55' : ''">
                    <a :href="'/product/'+item.slug" class="block">
                        <div class="relative aspect-[4/5] overflow-hidden bg-[#e9e5dd]">
                            <img :src="item.image" :alt="item.name" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]">
                            <button @click.prevent.stop="$store.commerce.toggleWishlist(item)" class="absolute right-3 top-3 z-10 text-lg">♥</button>
                            <span x-show="item.available === false" class="absolute bottom-3 left-3 bg-white px-3 py-2 text-[8px] uppercase tracking-[.15em] text-red-600">Unavailable</span>
                        </div>
                        <div class="grid grid-cols-[1fr_auto] gap-4 pt-4">
                            <div>
                                <h3 class="text-[12px] font-medium uppercase tracking-[.03em]" x-text="item.name"></h3>
                                <p class="mt-1.5 text-[9px] uppercase tracking-[.14em] text-black/42" x-text="item.family"></p>
                                <p x-show="item.stock !== undefined && item.available !== false" class="mt-2 text-[9px] uppercase tracking-[.12em] text-black/30"><span x-text="item.stock"></span> available</p>
                            </div>
                            <p class="text-[11px]">PKR <span x-text="Number(item.price_value ?? String(item.price).replace(/,/g,'')).toLocaleString()"></span></p>
                        </div>
                    </a>
                </article>
            </template>
        </div>
    </div>
</section>
@endsection

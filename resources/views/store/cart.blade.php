@extends('layouts.store')

@section('title','Shopping Bag — Scents by Aamir')
@section('description','Review your Scents by Aamir fragrance selection before checkout.')

@section('content')
<section
    x-data
    class="min-h-[78vh] bg-[#f7f6f2] pt-[100px] text-black"
>
    <div class="border-b border-black/10 bg-white">
        <div class="house-container py-10 sm:py-14">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="ui-label text-black/35">Your Selection</p>
                    <h1 class="mt-3 display-serif text-[50px] leading-[.94] sm:text-[64px]">Shopping bag.</h1>
                </div>
                <p class="ui-label text-black/35"><span x-text="$store.commerce.count">0</span> item(s)</p>
            </div>
        </div>
    </div>

    <div class="house-container py-10 lg:py-14">
        <div x-show="$store.commerce.notice" x-text="$store.commerce.notice" class="mb-6 border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"></div>

        <template x-if="$store.commerce.cart.length === 0">
            <div class="grid min-h-[480px] place-items-center border border-black/10 bg-white p-8 text-center">
                <div>
                    <p class="ui-label text-black/30">Your bag is empty</p>
                    <h2 class="mt-4 display-serif text-[44px] leading-[.95] sm:text-[56px]">Begin with a fragrance.</h2>
                    <p class="mx-auto mt-5 max-w-md text-sm leading-7 text-black/48">
                        Explore the full wardrobe or use the fragrance finder for a more considered starting point.
                    </p>
                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('shop') }}" class="btn-solid">Explore fragrances</a>
                        <a href="{{ route('finder') }}" class="btn-outline">Use the finder</a>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="$store.commerce.cart.length > 0" class="grid gap-8 lg:grid-cols-[1.35fr_.65fr]">
            <div class="bg-white">
                <div class="hidden grid-cols-[1fr_130px_130px] border-b border-black/10 px-6 py-4 ui-label text-black/30 sm:grid">
                    <span>Fragrance</span>
                    <span>Quantity</span>
                    <span class="text-right">Total</span>
                </div>

                <template x-for="(item,index) in $store.commerce.cart" :key="item.line_key || `${item.slug}-${item.size}`">
                    <article class="grid gap-5 border-b border-black/10 p-5 sm:grid-cols-[120px_1fr_130px_130px] sm:items-center sm:p-6">
                        <a :href="item.slug ? `/product/${item.slug}` : '#'" class="relative aspect-[4/5] overflow-hidden bg-[#efeee9]">
                            <img
                                x-show="item.image"
                                :src="item.image"
                                :alt="item.name || 'Fragrance'"
                                class="absolute inset-0 h-full w-full object-cover"
                                x-on:error="$el.onerror=null; $el.src='/logo-02.png'; $el.classList.remove('object-cover'); $el.classList.add('object-contain','p-5')"
                            >
                        </a>

                        <div class="min-w-0">
                            <p class="ui-label text-black/30" x-text="item.family || item.audience || 'Fine Fragrance'"></p>
                            <a :href="item.slug ? `/product/${item.slug}` : '#'" class="mt-2 block display-serif text-[31px] leading-[.98]" x-text="item.name || 'Fragrance'"></a>
                            <p class="mt-3 text-xs text-black/45">
                                <span x-show="item.size" x-text="item.size"></span>
                                <span x-show="item.sku"> · <span x-text="item.sku"></span></span>
                            </p>
                            <p x-show="item.available === false" class="mt-3 text-xs text-red-700">Currently unavailable</p>
                            <button type="button" @click="$store.commerce.removeFromCart(index)" class="mt-5 text-[9px] uppercase tracking-[.14em] text-black/38 underline underline-offset-4 transition hover:text-black">
                                Remove
                            </button>
                        </div>

                        <div class="flex w-fit items-center border border-black/15">
                            <button type="button" @click="$store.commerce.updateQty(index, Number(item.qty)-1)" :disabled="Number(item.qty)<=1" class="h-11 w-10 disabled:opacity-25">−</button>
                            <span class="grid h-11 min-w-10 place-items-center border-x border-black/15 text-sm" x-text="item.qty"></span>
                            <button type="button" @click="$store.commerce.updateQty(index, Number(item.qty)+1)" :disabled="Number(item.stock || 0)>0 && Number(item.qty)>=Number(item.stock)" class="h-11 w-10 disabled:opacity-25">+</button>
                        </div>

                        <div class="sm:text-right">
                            <p class="text-sm">PKR <span x-text="(Number(item.price_value || item.price || 0) * Number(item.qty || 1)).toLocaleString()"></span></p>
                            <p class="mt-2 text-[10px] text-black/35">PKR <span x-text="Number(item.price_value || item.price || 0).toLocaleString()"></span> each</p>
                        </div>
                    </article>
                </template>

                <div class="flex flex-wrap items-center justify-between gap-4 p-6">
                    <a href="{{ route('shop') }}" class="text-link">← Continue shopping</a>
                    <button type="button" @click="$store.commerce.validateCart()" class="text-link" :disabled="$store.commerce.syncing">
                        <span x-text="$store.commerce.syncing ? 'Checking…' : 'Refresh bag'"></span>
                    </button>
                </div>
            </div>

            <aside class="h-fit bg-black p-7 text-white lg:sticky lg:top-[120px]">
                <p class="ui-label text-white/40">Order Summary</p>
                <h2 class="mt-3 display-serif text-[38px]">Your selection.</h2>

                <div class="mt-7 space-y-4 border-y border-white/15 py-5 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-white/50">Items</span>
                        <span x-text="$store.commerce.count"></span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-white/50">Subtotal</span>
                        <span>PKR <span x-text="$store.commerce.subtotal.toLocaleString()"></span></span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-white/50">Delivery</span>
                        <span>Calculated at checkout</span>
                    </div>
                </div>

                <div class="mt-5 flex justify-between gap-4 text-lg">
                    <span>Subtotal</span>
                    <span>PKR <span x-text="$store.commerce.subtotal.toLocaleString()"></span></span>
                </div>

                @auth('customer')
                    <a href="{{ route('checkout') }}" class="btn-solid mt-7 w-full bg-white text-black hover:bg-[#d2bd98]" :class="$store.commerce.count===0 ? 'pointer-events-none opacity-40' : ''">
                        Continue to checkout
                    </a>
                @else
                    <a href="{{ route('customer.login', ['redirect' => route('checkout')]) }}" class="btn-solid mt-7 w-full bg-white text-black hover:bg-[#d2bd98]" :class="$store.commerce.count===0 ? 'pointer-events-none opacity-40' : ''">
                        Sign in to checkout
                    </a>
                    <p class="mt-4 text-xs leading-5 text-white/38">Your bag is stored in this browser while you sign in or create an account.</p>
                @endauth

                <div class="mt-7 border-t border-white/15 pt-6">
                    <p class="text-xs leading-6 text-white/42">Current price and stock are checked again before the order is placed.</p>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

<div
    x-data
    x-cloak
    x-show="$store.commerce.cartOpen"
    @keydown.escape.window="$store.commerce.cartOpen=false"
    class="fixed inset-0 z-[95]"
>
    <div class="absolute inset-0 bg-black/40" @click="$store.commerce.cartOpen=false"></div>

    <aside
        x-show="$store.commerce.cartOpen"
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition duration-250 ease-in"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute bottom-0 right-0 top-0 flex w-full max-w-[520px] flex-col bg-[#f7f6f2] text-black"
    >
        <div class="flex h-[76px] items-center justify-between border-b border-black/10 px-6">
            <div>
                <p class="ui-label text-black/35">Your Selection</p>
                <p class="mt-1 text-sm"><span x-text="$store.commerce.count"></span> item(s)</p>
            </div>
            <button @click="$store.commerce.cartOpen=false" class="ui-label">Close</button>
        </div>

        <div x-show="$store.commerce.notice" x-text="$store.commerce.notice" class="border-b border-amber-200 bg-amber-50 px-6 py-3 text-xs text-amber-900"></div>

        <div class="flex-1 overflow-y-auto px-6">
            <template x-if="$store.commerce.cart.length === 0">
                <div class="flex h-full min-h-[340px] items-center justify-center text-center">
                    <div>
                        <p class="display-serif text-5xl">Your bag is empty.</p>
                        <a href="{{ route('shop') }}" class="text-link mt-6">Explore fragrances →</a>
                    </div>
                </div>
            </template>

            <template x-for="(item,index) in $store.commerce.cart" :key="item.line_key || (item.slug + item.size)">
                <div class="grid grid-cols-[96px_1fr] gap-4 border-b border-black/10 py-6" :class="item.available === false ? 'opacity-55' : ''">
                    <div class="relative aspect-[4/5] overflow-hidden bg-[#e9e5dd]">
                        <img :src="item.image" :alt="item.name" class="absolute inset-0 h-full w-full object-cover">
                    </div>
                    <div class="flex flex-col justify-between gap-4">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-[12px] font-medium uppercase tracking-[.03em]" x-text="item.name"></h3>
                                    <p class="mt-2 ui-label text-black/35"><span x-text="item.size || 'Default'"></span><span x-show="item.sku"> · <span x-text="item.sku"></span></span></p>
                                    <p x-show="item.available === false" class="mt-2 text-[10px] uppercase tracking-[.12em] text-red-600">Currently unavailable</p>
                                </div>
                                <button @click="$store.commerce.removeFromCart(index)" class="text-xs text-black/45">Remove</button>
                            </div>
                        </div>

                        <div class="flex items-end justify-between gap-4">
                            <div x-show="item.available !== false" class="grid h-9 grid-cols-3 border border-black/15">
                                <button @click="$store.commerce.updateQty(index, Number(item.qty)-1)" :disabled="Number(item.qty) <= 1" class="w-8 disabled:opacity-25">−</button>
                                <span class="flex w-9 items-center justify-center text-xs" x-text="item.qty"></span>
                                <button @click="$store.commerce.updateQty(index, Number(item.qty)+1)" :disabled="Number(item.stock || 0) > 0 && Number(item.qty) >= Number(item.stock)" class="w-8 disabled:opacity-25">+</button>
                            </div>
                            <div class="text-right">
                                <p class="text-sm">PKR <span x-text="Number(item.price_value ?? String(item.price).replace(/,/g,'')) .toLocaleString()"></span></p>
                                <p x-show="item.stock !== undefined && item.available !== false" class="mt-1 text-[9px] uppercase tracking-[.12em] text-black/35"><span x-text="item.stock"></span> in stock</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="border-t border-black/10 bg-white p-6">
            <div class="flex items-center justify-between text-sm">
                <span>Subtotal</span>
                <span>PKR <span x-text="$store.commerce.subtotal.toLocaleString()"></span></span>
            </div>
            <p class="mt-3 text-xs leading-5 text-black/45">Current product price and stock are revalidated before checkout.</p>
            <a href="{{ route('checkout') }}" @click="$store.commerce.cartOpen=false" class="btn-solid mt-5 w-full" :class="$store.commerce.count === 0 ? 'pointer-events-none opacity-40' : ''">Continue to checkout</a>
        </div>
    </aside>
</div>

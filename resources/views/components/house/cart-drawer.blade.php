<div
    x-cloak
    x-show="$store.commerce.cartOpen"
    @keydown.escape.window="$store.commerce.cartOpen=false"
    class="fixed inset-0 z-[120] z-[90]"
>
    <div class="absolute inset-0 bg-black/45 backdrop-blur-[2px]" @click="$store.commerce.cartOpen=false"></div>

    <aside
        x-show="$store.commerce.cartOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute inset-y-0 right-0 flex w-full max-w-[470px] flex-col bg-[#f7f6f2] text-black shadow-2xl"
    >
        <header class="flex items-end justify-between border-b border-black/10 bg-white px-6 py-6">
            <div>
                <p class="ui-label text-black/35">Your Selection</p>
                <h2 class="mt-2 display-serif text-[38px] leading-none">Shopping bag</h2>
                <p class="mt-2 text-xs text-black/42"><span x-text="$store.commerce.count"></span> item(s)</p>
            </div>
            <button type="button" @click="$store.commerce.cartOpen=false" class="ui-label text-black/45 hover:text-black">Close</button>
        </header>

        <div x-show="$store.commerce.notice" x-text="$store.commerce.notice" class="border-b border-amber-200 bg-amber-50 px-6 py-3 text-xs text-amber-900"></div>

        <div class="min-h-0 flex-1 overflow-y-auto">
            <template x-if="$store.commerce.cart.length === 0">
                <div class="grid min-h-[420px] place-items-center px-7 text-center">
                    <div>
                        <p class="display-serif text-[38px]">Your bag is empty.</p>
                        <a href="{{ route('shop') }}" @click="$store.commerce.cartOpen=false" class="btn-solid mt-6">Explore fragrances</a>
                    </div>
                </div>
            </template>

            <template x-for="(item,index) in $store.commerce.cart" :key="item.line_key || `${item.slug}-${item.size}`">
                <article class="grid grid-cols-[92px_1fr] gap-4 border-b border-black/10 p-5">
                    <a :href="item.slug ? `/product/${item.slug}` : '#'" @click="$store.commerce.cartOpen=false" class="relative aspect-[4/5] overflow-hidden bg-[#eceae4]">
                        <img x-show="item.image" :src="item.image" :alt="item.name || 'Fragrance'" class="absolute inset-0 h-full w-full object-cover">
                    </a>

                    <div class="min-w-0">
                        <p class="ui-label text-black/30" x-text="item.family || 'Fine Fragrance'"></p>
                        <p class="mt-1 display-serif text-[27px] leading-none" x-text="item.name || 'Fragrance'"></p>
                        <p class="mt-2 text-[11px] text-black/42">
                            <span x-show="item.size" x-text="item.size"></span>
                            <span> · PKR <span x-text="Number(item.price_value || item.price || 0).toLocaleString()"></span></span>
                        </p>

                        <div class="mt-4 flex items-center justify-between gap-4">
                            <div class="flex items-center border border-black/15">
                                <button type="button" @click="$store.commerce.updateQty(index, Number(item.qty)-1)" :disabled="Number(item.qty)<=1" class="h-9 w-8 disabled:opacity-25">−</button>
                                <span class="grid h-9 min-w-9 place-items-center border-x border-black/15 text-xs" x-text="item.qty"></span>
                                <button type="button" @click="$store.commerce.updateQty(index, Number(item.qty)+1)" :disabled="Number(item.stock || 0)>0 && Number(item.qty)>=Number(item.stock)" class="h-9 w-8 disabled:opacity-25">+</button>
                            </div>

                            <button type="button" @click="$store.commerce.removeFromCart(index)" class="text-[9px] uppercase tracking-[.13em] text-black/38 hover:text-black">Remove</button>
                        </div>
                    </div>
                </article>
            </template>
        </div>

        <footer x-show="$store.commerce.cart.length > 0" class="border-t border-black/10 bg-white p-6">
            <div class="flex items-center justify-between">
                <span class="ui-label text-black/40">Subtotal</span>
                <span class="text-base">PKR <span x-text="$store.commerce.subtotal.toLocaleString()"></span></span>
            </div>
            <p class="mt-2 text-[11px] leading-5 text-black/38">Shipping and promotions are calculated at checkout.</p>

            <a href="{{ route('cart') }}" @click="$store.commerce.cartOpen=false" class="btn-outline mt-5 w-full">View shopping bag</a>

            @auth('customer')
                <a href="{{ route('checkout') }}" @click="$store.commerce.cartOpen=false" class="btn-solid mt-2 w-full">Checkout</a>
            @else
                <a href="{{ route('customer.login') }}" @click="$store.commerce.cartOpen=false" class="btn-solid mt-2 w-full">Sign in to checkout</a>
            @endauth
        </footer>
    </aside>
</div>

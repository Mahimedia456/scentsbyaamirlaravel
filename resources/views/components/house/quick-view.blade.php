<div
    x-data="{ open:false, product:{ name:'', family:'', price:'', index:'' } }"
    @open-quick-view.window="product=$event.detail; open=true"
    @keydown.escape.window="open=false"
    x-cloak
>
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-[80] bg-black/40" @click="open=false"></div>

    <aside
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed bottom-0 right-0 top-0 z-[90] w-full max-w-[520px] overflow-y-auto bg-[#f7f6f2] text-black"
    >
        <div class="flex h-[68px] items-center justify-between border-b border-black/10 px-5 sm:px-7">
            <span class="ui-label">Quick View</span>
            <button @click="open=false" class="ui-label">Close</button>
        </div>

        <div class="aspect-[4/4.5] bg-[#ece9e2]">
            <div class="flex h-full items-center justify-center">
                <div class="catalog-bottle !w-[34%]">
                    <div class="absolute left-[11%] right-[11%] top-[48%] z-10 text-center">
                        <span class="block text-[7px] font-semibold uppercase">SCENTS BY AAMIR</span>
                        <span class="mt-2 block text-[13px] uppercase" x-text="product.name"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-7">
            <p class="ui-label text-black/40" x-text="product.family"></p>
            <h2 class="mt-3 display-serif text-5xl" x-text="product.name"></h2>
            <p class="mt-4 text-sm">PKR <span x-text="product.price"></span></p>

            <div class="mt-7 border-y border-black/10 py-5">
                <p class="ui-label text-black/40">Size</p>
                <div class="mt-3 flex gap-2">
                    <button class="border border-black bg-black px-4 py-3 text-[10px] uppercase tracking-ui text-white">100 ML</button>
                    <button class="border border-black/15 px-4 py-3 text-[10px] uppercase tracking-ui">50 ML</button>
                </div>
            </div>

            <button class="dark-button mt-6 w-full">Add to bag</button>
            <a href="#" class="mt-4 block text-center ui-label underline underline-offset-4">View full product world</a>
        </div>
    </aside>
</div>

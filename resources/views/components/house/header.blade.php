<div
    x-data="{ mobileMenu:false }"
    x-init="$watch('mobileMenu', value => window.dispatchEvent(new CustomEvent('house:scroll-lock', { detail: { locked: value, source: 'mobile-menu' } })))"
    @keydown.escape.window="mobileMenu=false; if ($store.site) $store.site.megaOpen=false"
    data-house-header
    class="fixed inset-x-0 top-0 z-[85]"
>
    {{-- Announcement --}}
    <div class="flex min-h-[30px] items-center justify-center bg-black px-4 text-center text-[8px] font-medium uppercase tracking-[.18em] text-white sm:text-[9px]">
        Complimentary delivery on selected orders
    </div>

    {{-- Main header --}}
    <header class="header-shell w-full border-b border-black/10 bg-[#f7f6f2]/97 text-black backdrop-blur-xl">
        <div class="house-container grid h-[64px] grid-cols-[1fr_auto_1fr] items-center gap-2 sm:h-[78px] sm:gap-4">
            <div class="flex min-w-0 items-center gap-4 lg:gap-7">
                {{-- Mobile menu --}}
                <button
                    type="button"
                    @click="mobileMenu=true"
                    class="ui-label flex min-h-[44px] items-center gap-3 lg:hidden"
                    aria-label="Open mobile menu"
                    :aria-expanded="mobileMenu"
                >
                    <span class="relative block h-3 w-[18px]" aria-hidden="true">
                        <span class="absolute left-0 top-[2px] h-px w-[18px] bg-black"></span>
                        <span class="absolute bottom-[2px] left-0 h-px w-[18px] bg-black"></span>
                    </span>
                    <span class="hidden sm:inline">Menu</span>
                </button>

                {{-- Desktop mega menu --}}
                <button
                    type="button"
                    @click="$store.site.megaOpen=true"
                    class="ui-label hidden min-h-[44px] items-center gap-3 lg:flex"
                    aria-label="Open menu"
                    :aria-expanded="$store.site.megaOpen"
                >
                    <span class="relative block h-3 w-[18px]" aria-hidden="true">
                        <span class="absolute left-0 top-[2px] h-px w-[18px] bg-black"></span>
                        <span class="absolute bottom-[2px] left-0 h-px w-[18px] bg-black"></span>
                    </span>
                    <span>Menu</span>
                </button>

                <a href="{{ route('search') }}" class="ui-label hidden md:block">Search</a>
            </div>

            <a
                href="{{ route('home') }}"
                class="header-logo flex items-center justify-center overflow-hidden"
                aria-label="Scents by Aamir home"
                style="width:min(250px,46vw);height:50px;"
            >
                <img
                    src="{{ asset('logo-02.png') }}"
                    alt="Scents by Aamir"
                    width="250"
                    height="52"
                    class="block h-full w-full object-contain object-center"
                    decoding="async"
                >
            </a>

            <div class="flex min-w-0 items-center justify-end gap-3 lg:gap-7">
                <a href="{{ route('account') }}" class="ui-label hidden xl:block">Account</a>
                <a href="{{ route('wishlist') }}" class="ui-label hidden md:block">Wishlist</a>
                <a
                    href="{{ route('cart') }}"
                    @click.prevent="$store.commerce.cartOpen=true; window.dispatchEvent(new CustomEvent('sba:open-cart'))"
                    class="house-header-action"
                    aria-label="Open cart"
                >
                    Cart <span class="hidden sm:inline">(<span x-text="$store.commerce.count">0</span>)</span>
                </a>
            </div>
        </div>
    </header>

    {{-- Mobile drawer --}}
    <div
        x-cloak
        x-show="mobileMenu"
        x-transition.opacity.duration.180ms
        class="fixed inset-x-0 bottom-0 top-[94px] z-[95] bg-[#f7f6f2] text-black lg:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Mobile menu"
    >
        <div class="flex h-full flex-col">
            <div class="flex items-center justify-between border-b border-black/10 px-5 py-4 sm:px-7">
                <p class="ui-label text-black/35">Explore the house</p>
                <button type="button" @click="mobileMenu=false" class="ui-label min-h-[40px] px-2">Close</button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7">
                <nav>
                    @foreach([
                        ['Fragrances', route('shop')],
                        ['Collections', route('collections')],
                        ['Find a scent', route('finder')],
                        ['Ingredients', route('ingredients')],
                        ['Journal', route('journal')],
                        ['Gifting', route('gifting')],
                    ] as [$label,$href])
                        <a
                            href="{{ $href }}"
                            @click="mobileMenu=false"
                            class="flex items-center justify-between border-b border-black/10 py-3.5"
                        >
                            <span class="display-serif text-[34px] leading-none sm:text-[39px]">{{ $label }}</span>
                            <span class="text-lg">→</span>
                        </a>
                    @endforeach
                </nav>

                <div class="grid gap-7 py-7 sm:grid-cols-2">
                    <div>
                        <p class="ui-label text-black/30">By material</p>
                        <div class="mt-3 grid grid-cols-2 gap-x-5 gap-y-3 text-[10px] uppercase tracking-[.12em] text-black/62">
                            @foreach([
                                ['Oud','oud'],['Rose','rose'],['Amber','amber'],
                                ['Citrus','citrus'],['Sandalwood','sandalwood'],['Jasmine','jasmine']
                            ] as [$label,$slug])
                                <a href="{{ route('ingredients.show',$slug) }}" @click="mobileMenu=false">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="ui-label text-black/30">Account & service</p>
                        <div class="mt-3 grid grid-cols-2 gap-x-5 gap-y-3 text-[10px] uppercase tracking-[.12em] text-black/62">
                            <a href="{{ route('search') }}" @click="mobileMenu=false">Search</a>
                            <a href="{{ route('account') }}" @click="mobileMenu=false">Account</a>
                            <a href="{{ route('wishlist') }}" @click="mobileMenu=false">Wishlist</a>
                            <a href="{{ route('contact') }}" @click="mobileMenu=false">Support</a>
                        </div>
                    </div>
                </div>

                <a
                    href="{{ route('finder') }}"
                    @click="mobileMenu=false"
                    class="relative mt-1 block min-h-[210px] overflow-hidden bg-black text-white"
                >
                    @if(file_exists(public_path('images/discovery/finder-hero.webp')))
                        <img src="{{ asset('images/discovery/finder-hero.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-50">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-5">
                        <p class="ui-label text-white/45">Fragrance Finder</p>
                        <h3 class="mt-2 max-w-[300px] display-serif text-[32px] leading-[.95]">Start with how you want to feel.</h3>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<x-house.mega-menu />

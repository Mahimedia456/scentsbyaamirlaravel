<div
    x-data="{ menu:false }"
    x-init="$watch('menu', value => window.dispatchEvent(new CustomEvent('house:scroll-lock', { detail: { locked: value, source: 'header-menu' } })))"
    @keydown.escape.window="menu=false"
    data-house-header
    class="fixed inset-x-0 top-0 z-50"
>
    <div class="flex min-h-[30px] items-center justify-center bg-black px-4 text-center text-[8px] font-medium uppercase tracking-[.18em] text-white sm:text-[9px]">
        Complimentary delivery on selected orders
    </div>

    <header class="header-shell w-full border-b border-black/10 bg-[#f7f6f2]/95 text-black backdrop-blur-xl">
        <div class="house-container grid h-[72px] grid-cols-[1fr_auto_1fr] items-center gap-3 sm:h-[78px] sm:gap-4">
            <div class="flex min-w-0 items-center gap-5 lg:gap-7">
                <button @click="menu=!menu" class="ui-label flex min-h-[44px] items-center gap-3" aria-label="Open menu" :aria-expanded="menu">
                    <span class="relative block h-3 w-[18px]" aria-hidden="true"><span class="header-icon-line absolute left-0 top-[2px] h-px w-[18px] bg-black"></span><span class="header-icon-line absolute bottom-[2px] left-0 h-px w-[18px] bg-black"></span></span>
                    <span class="hidden sm:inline">Menu</span>
                </button>
                <a href="{{ route('search') }}" class="ui-label hidden md:block">Search</a>
            </div>

            <a href="{{ route('home') }}" class="header-logo flex items-center justify-center overflow-hidden" aria-label="Scents by Aamir home" style="width:min(250px,42vw);height:52px;">
                <img src="{{ asset('logo-02.png') }}" alt="Scents by Aamir" width="250" height="52" style="display:block;width:100%;height:100%;max-width:250px;max-height:52px;object-fit:contain;object-position:center;" decoding="async">
            </a>

            <div class="flex min-w-0 items-center justify-end gap-4 lg:gap-7">
                <a href="{{ route('account') }}" class="ui-label hidden xl:block">Account</a>
                <a href="{{ route('wishlist') }}" class="ui-label hidden md:block">Wishlist</a>
                <button @click="$store.commerce.cartOpen=true" class="ui-label whitespace-nowrap" aria-label="Open cart">Cart <span class="hidden sm:inline">(<span x-text="$store.commerce.count">0</span>)</span></button>
            </div>
        </div>
    </header>

    <div x-cloak x-show="menu" x-transition.opacity.duration.220ms class="fixed inset-x-0 bottom-0 top-[102px] z-[90] overflow-y-auto bg-[#f7f6f2] text-black shadow-[0_24px_70px_rgba(0,0,0,.18)]" role="dialog" aria-modal="true" aria-label="Main menu">
        <div class="house-container min-h-full py-8 sm:py-10 lg:py-12">
            <div class="flex items-center justify-between border-b border-black/10 pb-6"><p class="ui-label text-black/35">Explore the house</p><button @click="menu=false" class="ui-label min-h-[42px] px-2">Close</button></div>
            <div class="grid gap-10 py-8 lg:grid-cols-[1.05fr_.95fr] lg:gap-14 lg:py-10">
                <div class="min-w-0">
                    <nav class="grid gap-1 sm:grid-cols-2 sm:gap-x-10 lg:grid-cols-1">
                        @if(isset($cmsHeaderNavigation) && $cmsHeaderNavigation->isNotEmpty())
                            @foreach($cmsHeaderNavigation as $item)<a @click="menu=false" href="{{ $item->url ?: '#' }}" target="{{ $item->target ?: '_self' }}" class="menu-display-link">{{ $item->label }}</a>@endforeach
                        @else
                            <a @click="menu=false" href="{{ route('shop') }}" class="menu-display-link">Fragrances</a>
                            <a @click="menu=false" href="{{ route('collections') }}" class="menu-display-link">Collections</a>
                            <a @click="menu=false" href="{{ route('finder') }}" class="menu-display-link">Find a scent</a>
                            <a @click="menu=false" href="{{ route('ingredients') }}" class="menu-display-link">Ingredients</a>
                            <a @click="menu=false" href="{{ route('journal') }}" class="menu-display-link">Journal</a>
                        @endif
                    </nav>
                    <div class="mt-10 grid gap-8 border-t border-black/10 pt-7 sm:grid-cols-3">
                        <div><p class="ui-label text-black/35">By mood</p><div class="mt-4 grid gap-2.5 text-[12px] leading-5 text-black/60"><a href="{{ route('finder') }}">Quiet</a><a href="{{ route('finder') }}">Magnetic</a><a href="{{ route('finder') }}">Warm</a><a href="{{ route('finder') }}">Dark</a></div></div>
                        <div><p class="ui-label text-black/35">By material</p><div class="mt-4 grid gap-2.5 text-[12px] leading-5 text-black/60"><a href="{{ route('ingredients.show','oud') }}">Oud</a><a href="{{ route('ingredients.show','amber') }}">Amber</a><a href="{{ route('ingredients.show','musk') }}">Musk</a><a href="{{ route('ingredients.show','cedar') }}">Woods</a></div></div>
                        <div><p class="ui-label text-black/35">Services</p><div class="mt-4 grid gap-2.5 text-[12px] leading-5 text-black/60"><a href="{{ route('account') }}">Account</a><a href="{{ route('gifting') }}">Gifting</a><a href="{{ route('services') }}">Services</a><a href="{{ route('wishlist') }}">Wishlist</a></div></div>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    <a @click="menu=false" href="{{ route('collections.show','signature-worlds') }}" class="group relative min-h-[360px] overflow-hidden bg-[#d8d3ca]"><img src="{{ config('storefront.campaigns.signature.image') }}" alt="Signature Worlds" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"><div class="absolute inset-0 bg-black/22"></div><div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent p-6 pt-24 text-white"><p class="ui-label text-white/55">House Edit</p><h3 class="mt-2 display-serif text-4xl leading-[.95]">Signature Worlds</h3><span class="mt-5 inline-block ui-label">Explore →</span></div></a>
                    <a @click="menu=false" href="{{ route('finder') }}" class="flex min-h-[360px] flex-col justify-between bg-black p-6 text-white sm:p-7"><p class="ui-label text-white/40">Fragrance Finder</p><div><h3 class="display-serif text-4xl leading-[.95] sm:text-5xl">Start with how you want to feel.</h3><span class="mt-5 inline-block ui-label">Begin →</span></div></a>
                </div>
            </div>
        </div>
    </div>
</div>

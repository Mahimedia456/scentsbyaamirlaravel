<div
    x-cloak
    x-show="megaOpen"
    @keydown.escape.window="megaOpen = false"
    class="fixed inset-x-0 bottom-0 top-[108px] z-[90] hidden lg:block"
>
    <button
        type="button"
        aria-label="Close menu"
        class="absolute inset-0 h-full w-full bg-black/45 backdrop-blur-[2px]"
        @click="megaOpen = false"
    ></button>

    <section
        x-data="{ active: 'fragrances' }"
        class="relative h-full overflow-hidden border-t border-black/10 bg-[#f4f1ea] text-black shadow-2xl"
    >
        <div class="grid h-full min-h-0 grid-cols-[1.03fr_.97fr]">

            {{-- LEFT / NAVIGATION --}}
            <div class="grid min-h-0 grid-rows-[auto_1fr] px-9 py-5 xl:px-12">
                <div class="flex items-center justify-between border-b border-black/10 pb-3">
                    <p class="ui-label text-black/35">Explore the House</p>

                    <button
                        type="button"
                        class="ui-label min-h-[36px] px-2 text-black/45 transition hover:text-black"
                        @click="megaOpen = false"
                    >
                        Close
                    </button>
                </div>

                <div class="grid min-h-0 grid-cols-[1.05fr_.95fr] gap-8 pt-4 xl:gap-11">

                    <nav class="min-h-0">
                        <a
                            href="{{ route('shop') }}"
                            @mouseenter="active='fragrances'"
                            @focus="active='fragrances'"
                            @click="megaOpen=false"
                            class="group flex min-h-[66px] items-center justify-between border-b border-black/10"
                        >
                            <span class="display-serif text-[33px] leading-none xl:text-[39px]">Fragrances</span>
                            <span class="transition group-hover:translate-x-1">→</span>
                        </a>

                        <a
                            href="{{ route('collections') }}"
                            @mouseenter="active='collections'"
                            @focus="active='collections'"
                            @click="megaOpen=false"
                            class="group flex min-h-[66px] items-center justify-between border-b border-black/10"
                        >
                            <span class="display-serif text-[33px] leading-none xl:text-[39px]">Collections</span>
                            <span class="transition group-hover:translate-x-1">→</span>
                        </a>

                        <a
                            href="{{ route('finder') }}"
                            @mouseenter="active='finder'"
                            @focus="active='finder'"
                            @click="megaOpen=false"
                            class="group flex min-h-[66px] items-center justify-between border-b border-black/10"
                        >
                            <span class="display-serif text-[33px] leading-none xl:text-[39px]">Find a scent</span>
                            <span class="transition group-hover:translate-x-1">→</span>
                        </a>

                        <a
                            href="{{ route('ingredients') }}"
                            @mouseenter="active='ingredients'"
                            @focus="active='ingredients'"
                            @click="megaOpen=false"
                            class="group flex min-h-[66px] items-center justify-between border-b border-black/10"
                        >
                            <span class="display-serif text-[33px] leading-none xl:text-[39px]">Ingredients</span>
                            <span class="transition group-hover:translate-x-1">→</span>
                        </a>

                        <a
                            href="{{ route('journal') }}"
                            @mouseenter="active='journal'"
                            @focus="active='journal'"
                            @click="megaOpen=false"
                            class="group flex min-h-[66px] items-center justify-between border-b border-black/10"
                        >
                            <span class="display-serif text-[33px] leading-none xl:text-[39px]">Journal</span>
                            <span class="transition group-hover:translate-x-1">→</span>
                        </a>
                    </nav>

                    <div class="grid content-start gap-7">
                        <div>
                            <p class="ui-label text-black/30">By Material</p>

                            <div class="mt-4 grid grid-cols-2 gap-x-5 gap-y-3 text-[11px]">
                                <a href="{{ route('ingredients.show', 'oud') }}" @click="megaOpen=false" class="transition hover:opacity-50">Oud</a>
                                <a href="{{ route('ingredients.show', 'rose') }}" @click="megaOpen=false" class="transition hover:opacity-50">Rose</a>
                                <a href="{{ route('ingredients.show', 'amber') }}" @click="megaOpen=false" class="transition hover:opacity-50">Amber</a>
                                <a href="{{ route('ingredients.show', 'citrus') }}" @click="megaOpen=false" class="transition hover:opacity-50">Citrus</a>
                                <a href="{{ route('ingredients.show', 'sandalwood') }}" @click="megaOpen=false" class="transition hover:opacity-50">Sandalwood</a>
                                <a href="{{ route('ingredients.show', 'jasmine') }}" @click="megaOpen=false" class="transition hover:opacity-50">Jasmine</a>
                            </div>
                        </div>

                        <div class="border-t border-black/10 pt-6">
                            <p class="ui-label text-black/30">Discover</p>

                            <div class="mt-4 grid gap-3 text-[11px]">
                                <a href="{{ route('gifting') }}" @click="megaOpen=false" class="transition hover:opacity-50">Gifting</a>
                                <a href="{{ route('wishlist') }}" @click="megaOpen=false" class="transition hover:opacity-50">Wishlist</a>
                                <a href="{{ route('contact') }}" @click="megaOpen=false" class="transition hover:opacity-50">Client Services</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT / EDITORIAL VISUALS --}}
            <div class="grid min-h-0 grid-cols-[1.18fr_.82fr] gap-2 bg-black p-2">

                <a
                    href="{{ route('shop') }}"
                    @click="megaOpen=false"
                    class="relative min-h-0 overflow-hidden bg-[#171717]"
                >
                    <img
                        x-show="active === 'fragrances'"
                        src="{{ asset('images/home/hero-01-dark-seduction.webp') }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('logo-02.png') }}';this.className='absolute inset-0 h-full w-full object-contain p-16 opacity-35';"
                    >

                    <img
                        x-cloak
                        x-show="active === 'collections'"
                        src="{{ asset('images/home/banner-floral-charm.webp') }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('logo-02.png') }}';this.className='absolute inset-0 h-full w-full object-contain p-16 opacity-35';"
                    >

                    <img
                        x-cloak
                        x-show="active === 'finder'"
                        src="{{ asset('images/home/banner-ocean-spirit.webp') }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('logo-02.png') }}';this.className='absolute inset-0 h-full w-full object-contain p-16 opacity-35';"
                    >

                    <img
                        x-cloak
                        x-show="active === 'ingredients'"
                        src="{{ asset('images/home/banner-materials.webp') }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('logo-02.png') }}';this.className='absolute inset-0 h-full w-full object-contain p-16 opacity-35';"
                    >

                    <img
                        x-cloak
                        x-show="active === 'journal'"
                        src="{{ asset('images/home/banner-journal.webp') }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('logo-02.png') }}';this.className='absolute inset-0 h-full w-full object-contain p-16 opacity-35';"
                    >

                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-black/10"></div>

                    <div class="absolute inset-x-0 bottom-0 p-6 text-white xl:p-7">
                        <p class="ui-label text-white/45">House Edit</p>

                        <h3
                            class="mt-3 max-w-[440px] display-serif text-[35px] leading-[.96] xl:text-[43px]"
                            x-text="
                                active === 'collections'
                                    ? 'Curated fragrance worlds.'
                                    : active === 'finder'
                                        ? 'Begin with a feeling.'
                                        : active === 'ingredients'
                                            ? 'Explore the materials.'
                                            : active === 'journal'
                                                ? 'Stories from the house.'
                                                : 'Enter the fragrance wardrobe.'
                            "
                        ></h3>
                    </div>
                </a>

                <a
                    href="{{ route('finder') }}"
                    @click="megaOpen=false"
                    class="relative min-h-0 overflow-hidden bg-[#111] text-white"
                >
                    <img
                        src="{{ asset('images/home/banner-ocean-spirit.webp') }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover opacity-55"
                        onerror="this.onerror=null;this.src='{{ asset('logo-02.png') }}';this.className='absolute inset-0 h-full w-full object-contain p-12 opacity-25';"
                    >

                    <div class="absolute inset-0 bg-black/50"></div>

                    <div class="absolute inset-x-0 top-0 p-5">
                        <p class="ui-label text-white/45">Fragrance Finder</p>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 p-5">
                        <h3 class="display-serif text-[33px] leading-[.97] xl:text-[39px]">
                            Start with how you want to feel.
                        </h3>

                        <span class="mt-5 inline-block">→</span>
                    </div>
                </a>
            </div>
        </div>
    </section>
</div>

@php
    $menuImages = [
        'fragrances' => file_exists(public_path('images/catalog/fragrances-hero.webp'))
            ? asset('images/catalog/fragrances-hero.webp')
            : asset('images/collections/collections-hero.webp'),
        'collections' => asset('images/collections/signature-worlds.webp'),
        'finder' => asset('images/discovery/finder-hero.webp'),
        'ingredients' => asset('images/ingredients/ingredients-hero.webp'),
        'oud' => asset('images/ingredients/oud-ingredient.webp'),
        'journal' => asset('images/journal/journal-hero.webp'),
    ];
@endphp

<div
    x-data="{ active:'fragrances', images:@js($menuImages) }"
    x-cloak
    x-show="$store.site.megaOpen"
    x-effect="window.dispatchEvent(new CustomEvent('house:scroll-lock', { detail: { locked: !!$store.site.megaOpen, source: 'desktop-mega' } }))"
    @keydown.escape.window="$store.site.megaOpen=false"
    class="fixed inset-x-0 bottom-0 top-[108px] z-[90] hidden lg:block"
>
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="$store.site.megaOpen=false"></div>

    <section class="sba-mega-panel relative h-full overflow-hidden border-t border-black/10 bg-[#f4f1ea] text-black shadow-2xl">
        <div class="grid h-full min-h-0 grid-cols-[1.04fr_.96fr]">
            {{-- Navigation --}}
            <div class="grid min-h-0 grid-rows-[auto_1fr] px-8 py-5 xl:px-12">
                <div class="flex items-center justify-between border-b border-black/10 pb-3">
                    <p class="ui-label text-black/35">Explore the House</p>
                    <button type="button" @click="$store.site.megaOpen=false" class="ui-label min-h-[36px] px-2 text-black/45 hover:text-black">Close</button>
                </div>

                <div class="grid min-h-0 grid-cols-[1.03fr_.97fr] gap-7 pt-4 xl:gap-10">
                    <nav class="min-h-0">
                        @foreach([
                            ['fragrances','Fragrances',route('shop')],
                            ['collections','Collections',route('collections')],
                            ['finder','Find a scent',route('finder')],
                            ['ingredients','Ingredients',route('ingredients')],
                            ['journal','Journal',route('journal')],
                        ] as [$key,$label,$href])
                            <a
                                href="{{ $href }}"
                                @mouseenter="active='{{ $key }}'"
                                @focus="active='{{ $key }}'"
                                @click="$store.site.megaOpen=false"
                                class="sba-mega-primary group flex items-center justify-between border-b border-black/10"
                            >
                                <span class="display-serif">{{ $label }}</span>
                                <span class="text-base transition group-hover:translate-x-1">→</span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="sba-mega-secondary grid content-start">
                        <div class="sba-mega-group">
                            <p class="ui-label text-black/30">By Mood</p>
                            <div class="sba-mega-link-grid">
                                <a href="{{ route('finder') }}" @click="$store.site.megaOpen=false">Quiet</a>
                                <a href="{{ route('finder') }}" @click="$store.site.megaOpen=false">Magnetic</a>
                                <a href="{{ route('finder') }}" @click="$store.site.megaOpen=false">Fresh</a>
                                <a href="{{ route('finder') }}" @click="$store.site.megaOpen=false">Dark</a>
                            </div>
                        </div>

                        <div class="sba-mega-group">
                            <p class="ui-label text-black/30">By Material</p>
                            <div class="sba-mega-link-grid">
                                @foreach([
                                    ['oud','Oud','oud'],['rose','Rose','rose'],['amber','Amber','amber'],
                                    ['citrus','Citrus','citrus'],['sandalwood','Sandalwood','sandalwood'],['jasmine','Jasmine','jasmine']
                                ] as [$key,$label,$slug])
                                    <a
                                        href="{{ route('ingredients.show',$slug) }}"
                                        @mouseenter="active='{{ $key === 'oud' ? 'oud' : 'ingredients' }}'"
                                        @focus="active='{{ $key === 'oud' ? 'oud' : 'ingredients' }}'"
                                        @click="$store.site.megaOpen=false"
                                    >{{ $label }}</a>
                                @endforeach
                            </div>
                        </div>

                        <div class="sba-mega-group">
                            <p class="ui-label text-black/30">Services</p>
                            <div class="sba-mega-link-grid">
                                <a href="{{ route('gifting') }}" @click="$store.site.megaOpen=false">Gifting</a>
                                <a href="{{ route('account') }}" @click="$store.site.megaOpen=false">Account</a>
                                <a href="{{ route('wishlist') }}" @click="$store.site.megaOpen=false">Wishlist</a>
                                <a href="{{ route('contact') }}" @click="$store.site.megaOpen=false">Support</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Existing Phase imagery only --}}
            <div class="grid min-h-0 grid-cols-[1.14fr_.86fr] gap-2 bg-black p-2">
                <a
                    :href="active === 'collections' ? @js(route('collections')) : active === 'finder' ? @js(route('finder')) : active === 'ingredients' || active === 'oud' ? @js(route('ingredients')) : active === 'journal' ? @js(route('journal')) : @js(route('shop'))"
                    @click="$store.site.megaOpen=false"
                    class="relative min-h-0 overflow-hidden bg-[#d8d3ca]"
                >
                    <template x-for="(src,key) in images" :key="key">
                        <img x-show="active===key" :src="src" alt="" class="absolute inset-0 h-full w-full object-cover" x-transition.opacity>
                    </template>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/62 via-black/5 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                        <p class="ui-label text-white/45">House Edit</p>
                        <h3
                            class="mt-2 max-w-[420px] display-serif text-[34px] leading-[.96] xl:text-[40px]"
                            x-text="active==='finder' ? 'Begin with a feeling.' : active==='collections' ? 'Curated fragrance worlds.' : active==='journal' ? 'Read the house.' : active==='ingredients' || active==='oud' ? 'Explore the materials.' : 'Enter the fragrance wardrobe.'"
                        ></h3>
                    </div>
                </a>

                <a href="{{ route('finder') }}" @click="$store.site.megaOpen=false" class="relative min-h-0 overflow-hidden bg-[#111] text-white">
                    <img src="{{ asset('images/discovery/finder-hero.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-42">
                    <div class="absolute inset-0 bg-black/48"></div>
                    <div class="absolute inset-x-0 top-0 p-5"><p class="ui-label text-white/45">Fragrance Finder</p></div>
                    <div class="absolute inset-x-0 bottom-0 p-5">
                        <h3 class="display-serif text-[34px] leading-[.97] xl:text-[40px]">Start with how you want to feel.</h3>
                        <span class="mt-4 inline-block">→</span>
                    </div>
                </a>
            </div>
        </div>
    </section>
</div>

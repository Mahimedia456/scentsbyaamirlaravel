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
    x-data="{
        active: 'fragrances',
        open: false,
        images: @js($menuImages),
        setActive(key) { this.active = key; }
    }"
    x-effect="open = !!$store.site?.megaOpen"
    x-cloak
    x-show="open"
    @keydown.escape.window="$store.site.megaOpen=false"
    class="fixed inset-x-0 z-[80] top-[100px] lg:top-[106px]"
>
    <div class="absolute inset-0 bg-black/35 backdrop-blur-[2px]" @click="$store.site.megaOpen=false"></div>

    <section class="relative mx-auto max-w-[100vw] overflow-hidden border-y border-black/10 bg-[#f4f1ea] text-black shadow-2xl"
        style="height: min(730px, calc(100dvh - 106px)); min-height: 520px;">
        <div class="grid h-full grid-cols-1 lg:grid-cols-[1.06fr_.94fr]">
            <div class="flex h-full min-h-0 flex-col px-6 py-6 sm:px-8 lg:px-10 lg:py-7 xl:px-14">
                <div class="flex items-center justify-between border-b border-black/10 pb-4">
                    <p class="ui-label text-black/35">Explore the House</p>
                    <button type="button" @click="$store.site.megaOpen=false" class="ui-label text-black/45 hover:text-black">Close</button>
                </div>

                <div class="grid min-h-0 flex-1 grid-cols-1 gap-5 pt-5 lg:grid-cols-[1.05fr_.95fr] xl:gap-8">
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
                                @mouseenter="setActive('{{ $key }}')"
                                @focus="setActive('{{ $key }}')"
                                @click="$store.site.megaOpen=false"
                                class="group flex items-center justify-between border-b border-black/10 py-2.5 sm:py-3"
                            >
                                <span class="display-serif text-[34px] leading-[.94] sm:text-[40px] lg:text-[42px] xl:text-[46px]">{{ $label }}</span>
                                <span class="translate-x-0 text-lg transition group-hover:translate-x-1">→</span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="grid content-start gap-5 lg:gap-6">
                        <div>
                            <p class="ui-label text-black/30">By Mood</p>
                            <div class="mt-3 grid grid-cols-2 gap-x-5 gap-y-2 text-[11px] uppercase tracking-[.12em] text-black/62">
                                <a href="{{ route('shop',['mood'=>'quiet']) }}" @click="$store.site.megaOpen=false">Quiet</a>
                                <a href="{{ route('shop',['mood'=>'bold']) }}" @click="$store.site.megaOpen=false">Bold</a>
                                <a href="{{ route('shop',['mood'=>'fresh']) }}" @click="$store.site.megaOpen=false">Fresh</a>
                                <a href="{{ route('shop',['mood'=>'warm']) }}" @click="$store.site.megaOpen=false">Warm</a>
                            </div>
                        </div>

                        <div>
                            <p class="ui-label text-black/30">By Material</p>
                            <div class="mt-3 grid grid-cols-2 gap-x-5 gap-y-2 text-[11px] uppercase tracking-[.12em] text-black/62">
                                @foreach([
                                    ['oud','Oud','oud'],
                                    ['rose','Rose','rose'],
                                    ['amber','Amber','amber'],
                                    ['citrus','Citrus','citrus'],
                                    ['sandalwood','Sandalwood','sandalwood'],
                                    ['jasmine','Jasmine','jasmine'],
                                ] as [$key,$label,$slug])
                                    <a
                                        href="{{ route('ingredients.show',$slug) }}"
                                        @mouseenter="setActive('{{ $key === 'oud' ? 'oud' : 'ingredients' }}')"
                                        @focus="setActive('{{ $key === 'oud' ? 'oud' : 'ingredients' }}')"
                                        @click="$store.site.megaOpen=false"
                                    >{{ $label }}</a>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="ui-label text-black/30">Services</p>
                            <div class="mt-3 grid grid-cols-2 gap-x-5 gap-y-2 text-[11px] uppercase tracking-[.12em] text-black/62">
                                <a href="{{ route('gifting') }}" @click="$store.site.megaOpen=false">Gifting</a>
                                <a href="{{ route('customer.login') }}" @click="$store.site.megaOpen=false">Account</a>
                                <a href="{{ route('wishlist') }}" @click="$store.site.megaOpen=false">Wishlist</a>
                                <a href="{{ route('contact') }}" @click="$store.site.megaOpen=false">Support</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden h-full min-h-0 gap-3 bg-black p-3 lg:grid lg:grid-cols-[1.15fr_.85fr]">
                <a
                    :href="active === 'collections' ? @js(route('collections')) : active === 'finder' ? @js(route('finder')) : active === 'ingredients' || active === 'oud' ? @js(route('ingredients')) : active === 'journal' ? @js(route('journal')) : @js(route('shop'))"
                    @click="$store.site.megaOpen=false"
                    class="relative min-h-0 overflow-hidden bg-[#ddd8cf]"
                >
                    <template x-for="(src,key) in images" :key="key">
                        <img
                            x-show="active === key"
                            :src="src"
                            alt=""
                            class="absolute inset-0 h-full w-full object-cover"
                            x-transition.opacity
                        >
                    </template>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/58 via-black/5 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-6 text-white">
                        <p class="ui-label text-white/45">House Edit</p>
                        <h3 class="mt-3 display-serif text-[38px] leading-[.95]"
                            x-text="active === 'finder' ? 'Begin with a feeling.' : active === 'collections' ? 'Curated fragrance worlds.' : active === 'journal' ? 'Read the house.' : active === 'ingredients' || active === 'oud' ? 'Explore the materials.' : 'Enter the fragrance wardrobe.'"></h3>
                    </div>
                </a>

                <a href="{{ route('finder') }}" @click="$store.site.megaOpen=false" class="relative min-h-0 overflow-hidden bg-[#111] text-white">
                    <img src="{{ asset('images/discovery/finder-hero.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-45">
                    <div class="absolute inset-0 bg-black/42"></div>
                    <div class="absolute inset-x-0 top-0 p-6">
                        <p class="ui-label text-white/45">Fragrance Finder</p>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 p-6">
                        <h3 class="display-serif text-[38px] leading-[.98] xl:text-[44px]">Start with how you want to feel.</h3>
                        <span class="mt-5 inline-block text-lg">→</span>
                    </div>
                </a>
            </div>
        </div>
    </section>
</div>

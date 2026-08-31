@extends('layouts.store')

@php
    $heroImage = file_exists(public_path('images/discovery/finder-hero.webp'))
        ? asset('images/discovery/finder-hero.webp')
        : (config('storefront.campaigns.finder.image') ?? config('storefront.campaigns.home_hero.image') ?? null);

    $hasResults = collect($recommendations ?? [])->isNotEmpty();
@endphp

@section('title', 'Fragrance Finder — Scents by Aamir')
@section('description', 'Find a Scents by Aamir fragrance by mood, intensity, occasion and who you are shopping for.')

@section('content')

<div
    x-data="{
        step: {{ $hasResults ? 5 : 1 }},
        loading: false,
        mood: @js($answers['mood'] ?? ''),
        intensity: @js($answers['intensity'] ?? ''),
        occasion: @js($answers['occasion'] ?? ''),
        audience: @js($answers['audience'] ?? 'any'),
        controller: null,

        choose(key, value) {
            this[key] = value;

            if (this.step < 4) {
                this.step++;
                window.scrollTo({ top: document.querySelector('#finder-flow')?.offsetTop - 90, behavior: 'smooth' });
                return;
            }

            this.submitFinder();
        },

        previous() {
            if (this.step > 1 && this.step < 5) this.step--;
        },

        async submitFinder() {
            const params = new URLSearchParams({
                mood: this.mood,
                intensity: this.intensity,
                occasion: this.occasion,
                audience: this.audience || 'any',
            });

            const url = @js(route('finder')) + '?' + params.toString();

            if (this.controller) this.controller.abort();
            this.controller = new AbortController();
            this.loading = true;

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: this.controller.signal,
                });

                if (!response.ok) throw new Error('Finder request failed');

                const payload = await response.json();
                this.$refs.results.innerHTML = payload.html;

                if (window.Alpine?.initTree) {
                    window.Alpine.initTree(this.$refs.results);
                }

                window.history.pushState({}, '', payload.url || url);
                this.step = 5;

                this.$nextTick(() => {
                    window.scrollTo({
                        top: this.$refs.results.getBoundingClientRect().top + window.scrollY - 120,
                        behavior: 'smooth'
                    });
                });
            } catch (error) {
                if (error?.name !== 'AbortError') window.location.href = url;
            } finally {
                this.loading = false;
            }
        },

        resetFinder() {
            this.mood = '';
            this.intensity = '';
            this.occasion = '';
            this.audience = 'any';
            this.step = 1;
            window.history.pushState({}, '', @js(route('finder')));
            window.scrollTo({ top: document.querySelector('#finder-flow')?.offsetTop - 90, behavior: 'smooth' });
        }
    }"
    class="bg-black text-white"
>
    {{-- HERO --}}
    <section class="relative overflow-hidden pt-[100px]">
        <div class="absolute inset-x-0 bottom-0 top-[100px]">
            @if($heroImage)
                <img
                    src="{{ $heroImage }}"
                    alt="Scents by Aamir fragrance discovery"
                    class="h-full w-full object-cover object-center"
                    fetchpriority="high"
                >
            @endif
            <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.94)_0%,rgba(0,0,0,.80)_38%,rgba(0,0,0,.22)_70%,rgba(0,0,0,.52)_100%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black/12"></div>
        </div>

        <div class="house-container relative flex min-h-[610px] items-end py-16 sm:min-h-[680px] lg:min-h-[720px] lg:items-center">
            <div class="max-w-[720px]" data-reveal>
                <div class="flex items-center gap-4">
                    <span class="h-px w-10 bg-[#c9ad7a]"></span>
                    <p class="ui-label text-white/55">Guided Discovery</p>
                </div>

                <h1 class="mt-6 max-w-[720px] display-serif text-[50px] leading-[.92] tracking-[-.035em] sm:text-[66px] lg:text-[78px]">
                    Find your fragrance.
                </h1>

                <p class="mt-5 max-w-[600px] display-serif text-[23px] leading-tight italic text-[#d6c19a] sm:text-[28px]">
                    Begin with a feeling. We will narrow the wardrobe.
                </p>

                <p class="mt-6 max-w-xl text-sm leading-7 text-white/62">
                    Four simple choices create a considered edit from the live Scents by Aamir catalogue.
                </p>

                <a href="#finder-flow" class="btn-solid mt-8 bg-white text-black hover:bg-[#d2bd98]">
                    Start the finder
                </a>
            </div>
        </div>
    </section>

    {{-- FINDER FLOW --}}
    <section id="finder-flow" class="house-container py-14 lg:py-20">
        <div class="mb-10 flex items-end justify-between gap-6 border-b border-white/15 pb-7">
            <div>
                <p class="ui-label text-white/35">Fragrance Finder</p>
                <h2 class="mt-3 display-serif text-[42px] leading-none sm:text-[52px]">
                    <span x-show="step===1">How should it feel?</span>
                    <span x-cloak x-show="step===2">How present should it be?</span>
                    <span x-cloak x-show="step===3">Where will you wear it?</span>
                    <span x-cloak x-show="step===4">Who are you shopping for?</span>
                    <span x-cloak x-show="step===5">Your fragrance edit.</span>
                </h2>
            </div>

            <div class="text-right">
                <p x-show="step<5" class="ui-label text-white/35">
                    Step <span x-text="String(step).padStart(2,'0')"></span> / 04
                </p>
                <button
                    x-cloak
                    x-show="step>1 && step<5"
                    type="button"
                    @click="previous()"
                    class="mt-3 text-[9px] uppercase tracking-[.16em] text-white/45 transition hover:text-white"
                >
                    ← Back
                </button>
            </div>
        </div>

        {{-- STEP 1 --}}
        <div x-show="step===1">
            <div class="grid gap-px bg-white/15 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['Quiet','Soft, skin-close, considered'],
                    ['Magnetic','Warm, addictive, memorable'],
                    ['Fresh','Bright, mineral, energetic'],
                    ['Warm','Comforting, ambered, enveloping'],
                    ['Dark','Smoky, resinous, nocturnal'],
                    ['Celebratory','Radiant, expressive, dressed-up'],
                ] as [$label,$copy])
                    <button
                        type="button"
                        @click="choose('mood', @js($label))"
                        class="group min-h-[190px] bg-black p-7 text-left transition duration-300 hover:bg-white hover:text-black"
                    >
                        <span class="display-serif text-[38px]">{{ $label }}</span>
                        <span class="mt-5 block max-w-[240px] text-xs leading-6 opacity-45">{{ $copy }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- STEP 2 --}}
        <div x-cloak x-show="step===2">
            <div class="grid gap-px bg-white/15 sm:grid-cols-3">
                @foreach([
                    ['Soft','Present at close range'],
                    ['Moderate','Balanced and noticeable'],
                    ['Strong','Distinctive with a larger trail'],
                ] as [$label,$copy])
                    <button
                        type="button"
                        @click="choose('intensity', @js($label))"
                        class="group min-h-[240px] bg-black p-7 text-left transition duration-300 hover:bg-white hover:text-black"
                    >
                        <span class="display-serif text-[40px]">{{ $label }}</span>
                        <span class="mt-5 block text-xs leading-6 opacity-45">{{ $copy }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- STEP 3 --}}
        <div x-cloak x-show="step===3">
            <div class="grid gap-px bg-white/15 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['Everyday','Easy, versatile, repeatable'],
                    ['Evening','Deeper and more atmospheric'],
                    ['Formal','Polished and composed'],
                    ['Gifting','A confident house choice'],
                ] as [$label,$copy])
                    <button
                        type="button"
                        @click="choose('occasion', @js($label))"
                        class="group min-h-[220px] bg-black p-7 text-left transition duration-300 hover:bg-white hover:text-black"
                    >
                        <span class="display-serif text-[36px]">{{ $label }}</span>
                        <span class="mt-5 block text-xs leading-6 opacity-45">{{ $copy }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- STEP 4 --}}
        <div x-cloak x-show="step===4">
            <div class="grid gap-px bg-white/15 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['any','Open to all'],
                    ['men','Men'],
                    ['women','Women'],
                    ['unisex','Unisex'],
                ] as [$value,$label])
                    <button
                        type="button"
                        @click="choose('audience', @js($value))"
                        class="group min-h-[210px] bg-black p-7 text-left transition duration-300 hover:bg-white hover:text-black"
                    >
                        <span class="display-serif text-[38px]">{{ $label }}</span>
                        <span class="mt-5 block text-xs leading-6 opacity-45">
                            {{ $value === 'any' ? 'Let the fragrance lead.' : 'Use the catalogue classification.' }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- RESULTS --}}
        <div x-cloak x-show="step===5" class="relative">
            <div
                x-show="loading"
                class="absolute inset-0 z-10 flex min-h-[420px] items-center justify-center bg-black/78 backdrop-blur-sm"
            >
                <p class="ui-label text-white/60">Composing your edit…</p>
            </div>

            <div x-ref="results">
                @if($hasResults)
                    @include('store.partials.finder-results')
                @endif
            </div>
        </div>
    </section>

    {{-- DISCOVERY LINKS --}}
    <section class="border-t border-white/12">
        <div class="house-container grid sm:grid-cols-3">
            @foreach([
                ['Browse by material', route('ingredients'), 'Oud, rose, amber, citrus and more'],
                ['Browse by family', route('families'), 'Fresh, floral, woody, amber and oud'],
                ['Browse everything', route('shop'), 'The complete fragrance wardrobe'],
            ] as [$title,$href,$copy])
                <a href="{{ $href }}" class="group border-b border-white/12 p-7 transition hover:bg-white hover:text-black sm:border-r sm:border-b-0">
                    <p class="ui-label opacity-35">Discovery</p>
                    <h3 class="mt-4 display-serif text-[34px]">{{ $title }}</h3>
                    <p class="mt-4 text-xs leading-6 opacity-45">{{ $copy }}</p>
                    <span class="mt-6 inline-block transition-transform group-hover:translate-x-1">→</span>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection

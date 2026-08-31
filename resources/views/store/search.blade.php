@extends('layouts.store')

@section('title', ($q ? 'Search: '.$q : 'Search').' — Scents by Aamir')
@section('description', 'Search Scents by Aamir by fragrance name, material, family, audience or mood.')

@section('content')
<section
    x-data="{
        query: @js($q),
        loading: false,
        timer: null,
        controller: null,

        scheduleSearch() {
            clearTimeout(this.timer);

            this.timer = setTimeout(() => {
                this.runSearch();
            }, 320);
        },

        async runSearch() {
            const value = this.query.trim();
            const url = new URL(@js(route('search')), window.location.origin);

            if (value) url.searchParams.set('q', value);

            if (this.controller) this.controller.abort();
            this.controller = new AbortController();
            this.loading = true;

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: this.controller.signal
                });

                if (!response.ok) throw new Error('Search request failed');

                const payload = await response.json();

                if (this.$refs.results) {
                    this.$refs.results.innerHTML = payload.html;

                    if (window.Alpine?.initTree) {
                        window.Alpine.initTree(this.$refs.results);
                    }
                }

                window.history.replaceState({}, '', value ? `${url.pathname}?q=${encodeURIComponent(value)}` : url.pathname);
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    window.location.href = url.toString();
                }
            } finally {
                this.loading = false;
            }
        },

        useSuggestion(value) {
            this.query = value;
            this.runSearch();
        }
    }"
    class="min-h-[78vh] bg-[#f7f6f2] pt-[108px] text-black"
>
    <div class="house-container py-12 lg:py-18">
        <div class="grid gap-8 border-b border-black/10 pb-9 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="ui-label text-black/35">Catalog Discovery</p>

                <h1 class="mt-4 display-serif text-[50px] leading-[.94] sm:text-[66px] lg:text-[76px]">
                    Search the house.
                </h1>

                <p class="mt-5 max-w-xl text-sm leading-7 text-black/48">
                    Search by fragrance name, note, material, fragrance family or who the scent is for.
                </p>
            </div>

            <a href="{{ route('finder') }}" class="text-link">Need guidance? Use the finder →</a>
        </div>

        <div class="relative mt-10 max-w-5xl">
            <div class="flex min-h-[76px] items-center border-b border-black">
                <svg viewBox="0 0 24 24" class="mr-4 h-5 w-5 fill-none stroke-current text-black/35" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m20 20-4-4"></path>
                </svg>

                <input
                    x-model="query"
                    @input="scheduleSearch()"
                    @keydown.enter.prevent="runSearch()"
                    autofocus
                    type="search"
                    autocomplete="off"
                    class="min-h-[74px] flex-1 border-0 bg-transparent px-0 text-[19px] outline-none placeholder:text-black/28 focus:ring-0 sm:text-[22px]"
                    placeholder="Search fragrance, oud, floral, women, fresh…"
                >

                <div class="ml-4 flex min-w-[110px] justify-end">
                    <span x-cloak x-show="loading" class="ui-label text-black/35">Searching…</span>

                    <button
                        x-show="!loading && query"
                        type="button"
                        @click="query=''; runSearch()"
                        class="ui-label text-black/35 transition hover:text-black"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2">
                <span class="ui-label mr-2 text-black/28">Try</span>

                @foreach(['Dark Seduction','Smoky Chic','Oud','Fresh','Floral','Men','Women','Unisex'] as $suggestion)
                    <button
                        type="button"
                        @click="useSuggestion(@js($suggestion))"
                        class="rounded-full border border-black/10 bg-white px-4 py-2 text-[9px] uppercase tracking-[.13em] text-black/48 transition hover:border-black/30 hover:text-black"
                    >
                        {{ $suggestion }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="relative mt-14">
            <div
                x-cloak
                x-show="loading"
                x-transition.opacity
                class="pointer-events-none absolute inset-x-0 top-0 z-20 flex h-14 items-center justify-center"
            >
                <span class="rounded-full border border-black/10 bg-[#f7f6f2]/95 px-5 py-3 ui-label text-black/45 shadow-sm backdrop-blur">
                    Searching the fragrance wardrobe…
                </span>
            </div>

            <div
                x-ref="results"
                :class="loading ? 'opacity-35' : 'opacity-100'"
                class="transition-opacity duration-200"
            >
                @include('store.partials.search-results')
            </div>
        </div>
    </div>
</section>
@endsection

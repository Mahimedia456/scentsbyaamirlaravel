@extends('layouts.store')

@php
    $posts = collect($posts ?? []);
    $featured = $posts->first();

    $journalHero = file_exists(public_path('images/journal/journal-hero.webp'))
        ? asset('images/journal/journal-hero.webp')
        : (config('storefront.campaigns.light_studies.image') ?? config('storefront.campaigns.signature.image') ?? null);

    $imageUrl = function ($path) {
        if (!$path) return null;
        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) return $path;
        return asset('storage/'.$path);
    };
@endphp

@section('title','Journal — Scents by Aamir')
@section('description','Materials, memory, rituals and the culture surrounding fragrance.')

@section('content')

<section class="relative overflow-hidden bg-black pt-[100px] text-white">
    <div class="absolute inset-x-0 bottom-0 top-[100px]">
        @if($journalHero)
            <img src="{{ $journalHero }}" alt="Scents by Aamir Journal" class="h-full w-full object-cover" fetchpriority="high">
        @endif
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,0,.93)_0%,rgba(0,0,0,.78)_38%,rgba(0,0,0,.20)_70%,rgba(0,0,0,.54)_100%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/10"></div>
    </div>

    <div class="house-container relative flex min-h-[600px] items-end py-16 sm:min-h-[660px] lg:min-h-[700px] lg:items-center">
        <div class="max-w-[720px]">
            <div class="flex items-center gap-4">
                <span class="h-px w-10 bg-[#c9ad7a]"></span>
                <p class="ui-label text-white/55">House Journal</p>
            </div>

            <h1 class="mt-6 display-serif text-[54px] leading-[.9] tracking-[-.035em] sm:text-[70px] lg:text-[82px]">
                Journal
            </h1>

            <p class="mt-5 max-w-[620px] display-serif text-[23px] italic leading-tight text-[#d6c19a] sm:text-[29px]">
                Materials, memory, rituals and the culture surrounding fragrance.
            </p>

            <p class="mt-6 max-w-xl text-sm leading-7 text-white/60">
                Essays and house notes that move beyond product: how materials feel, how fragrance is worn, and why scent remains connected to memory.
            </p>

            <a href="#journal-stories" class="btn-solid mt-8 bg-white text-black hover:bg-[#d2bd98]">
                Explore stories
            </a>
        </div>
    </div>
</section>

<section class="border-b border-black/10 bg-[#f7f6f2] text-black">
    <div class="house-container flex gap-8 overflow-x-auto py-6">
        @foreach(['All Stories','Materials','Ritual','House Notes','Culture'] as $label)
            <span class="whitespace-nowrap ui-label {{ $loop->first ? 'text-black' : 'text-black/35' }}">{{ $label }}</span>
        @endforeach
    </div>
</section>

<section id="journal-stories" class="bg-white text-black">
    <div class="house-container py-12 lg:py-18">
        @if($featured)
            <a
                href="{{ route('journal.show',$featured->slug) }}"
                class="group grid overflow-hidden bg-[#0d0d0d] text-white lg:grid-cols-[1.18fr_.82fr]"
            >
                <div class="relative min-h-[520px] overflow-hidden lg:min-h-[700px]">
                    @if($featured->featured_image_path)
                        <img
                            src="{{ $imageUrl($featured->featured_image_path) }}"
                            alt="{{ $featured->title }}"
                            class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.02]"
                        >
                    @elseif($journalHero)
                        <img src="{{ $journalHero }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-75">
                    @endif
                    <div class="absolute inset-0 bg-black/12"></div>
                </div>

                <div class="flex min-h-[480px] items-end p-8 sm:p-12 lg:min-h-[700px] lg:p-14">
                    <div>
                        <p class="ui-label text-[#d0b47b]">
                            {{ $featured->eyebrow ?: 'House Journal' }}
                            ·
                            {{ $featured->published_at?->format('d M Y') ?: 'Editorial' }}
                        </p>

                        <h2 class="mt-5 max-w-xl display-serif text-[48px] leading-[.92] sm:text-[60px]">
                            {{ $featured->title }}
                        </h2>

                        <p class="mt-6 max-w-lg text-sm leading-7 text-white/55">
                            {{ $featured->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($featured->content ?? ''), 180) }}
                        </p>

                        <span class="mt-8 inline-block ui-label">Read story →</span>
                    </div>
                </div>
            </a>

            @if($posts->count() > 1)
                <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($posts->skip(1) as $article)
                        <a href="{{ route('journal.show',$article->slug) }}" class="group border border-black/10 bg-[#f7f6f2]">
                            <div class="relative aspect-[16/11] overflow-hidden bg-black/5">
                                @if($article->featured_image_path)
                                    <img
                                        src="{{ $imageUrl($article->featured_image_path) }}"
                                        alt="{{ $article->title }}"
                                        loading="lazy"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"
                                    >
                                @elseif($journalHero)
                                    <img src="{{ $journalHero }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-60">
                                @endif
                            </div>

                            <div class="p-6 sm:p-7">
                                <p class="ui-label text-black/35">{{ $article->eyebrow ?: 'Journal' }}</p>
                                <h3 class="mt-3 display-serif text-[38px] leading-[.95]">{{ $article->title }}</h3>
                                <p class="mt-4 line-clamp-3 text-sm leading-6 text-black/50">
                                    {{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->content ?? ''), 130) }}
                                </p>
                                <span class="mt-6 inline-block ui-label">Read →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @else
            <div class="border border-black/10 bg-[#f7f6f2] px-7 py-20 text-center">
                <p class="ui-label text-black/35">House Journal</p>
                <h2 class="mt-4 display-serif text-[46px] leading-tight sm:text-[56px]">The journal is being composed.</h2>
                <p class="mx-auto mt-5 max-w-lg text-sm leading-7 text-black/48">
                    Published journal entries from the admin panel will appear here automatically.
                </p>
            </div>
        @endif
    </div>
</section>

<section class="bg-[#efebe3] text-black">
    <div class="house-container grid gap-px bg-black/10 sm:grid-cols-3">
        @foreach([
            ['Material Library', 'Explore oud, rose, amber, citrus and the raw materials behind scent.', route('ingredients')],
            ['Our House', 'Read the point of view behind Scents by Aamir.', route('about')],
            ['Fragrance Finder', 'Begin with mood and discover a more personal edit.', route('finder')],
        ] as [$title,$copy,$href])
            <a href="{{ $href }}" class="group bg-[#efebe3] p-7 transition hover:bg-white sm:min-h-[260px]">
                <div class="flex h-full flex-col justify-between">
                    <span class="ui-label text-black/30">Continue exploring</span>
                    <div class="mt-16">
                        <h3 class="display-serif text-[36px]">{{ $title }}</h3>
                        <p class="mt-4 text-xs leading-6 text-black/48">{{ $copy }}</p>
                        <span class="mt-6 inline-block transition-transform group-hover:translate-x-1">→</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

@endsection

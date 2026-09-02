@extends('layouts.store')

@php
    $imagePath = $article->featured_image_path ?? null;
    $featuredImage = !$imagePath
        ? null
        : (
            str_starts_with($imagePath,'http') || str_starts_with($imagePath,'/')
                ? $imagePath
                : asset('storage/'.$imagePath)
        );

    $fallbackEditorial = file_exists(public_path('images/journal/article-fallback.webp'))
        ? asset('images/journal/article-fallback.webp')
        : (config('storefront.campaigns.signature.image') ?? null);

    $hero = $featuredImage ?: $fallbackEditorial;
@endphp

@section('title',($article->meta_title ?: $article->title).' — Scents by Aamir')
@section('description',$article->meta_description ?: $article->excerpt)

@section('content')

<article class="bg-white pt-[100px] text-black">
    <header class="house-container py-14 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-[1.35fr_.65fr] lg:items-end">
            <div>
                <p class="ui-label text-black/35">
                    {{ $article->eyebrow ?: 'House Journal' }}
                    ·
                    {{ $article->published_at?->format('d M Y') ?: 'Editorial' }}
                </p>

                <h1 class="mt-5 max-w-5xl display-serif text-[50px] leading-[.93] tracking-[-.035em] sm:text-[66px] lg:text-[76px]">
                    {{ $article->title }}
                </h1>
            </div>

            <div>
                <p class="max-w-lg text-sm leading-7 text-black/52">
                    {{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->content ?? ''), 190) }}
                </p>
            </div>
        </div>
    </header>

    @if($hero)
        <div class="house-container">
            <div class="relative min-h-[60vh] overflow-hidden lg:min-h-[76vh]">
                <img src="{{ $hero }}" alt="{{ $article->title }}" class="absolute inset-0 h-full w-full object-cover">
            </div>
        </div>
    @endif

    <section class="house-container grid gap-12 py-16 lg:grid-cols-[.4fr_1.6fr] lg:py-24">
        <aside>
            <div class="lg:sticky lg:top-[130px]">
                <p class="ui-label text-black/35">House Journal</p>

                <div class="mt-6 space-y-3 text-[10px] uppercase tracking-[.14em] text-black/40">
                    <p>{{ $article->eyebrow ?: 'Editorial' }}</p>
                    <p>{{ $article->published_at?->format('F Y') ?: 'Scents by Aamir' }}</p>
                </div>

                <a href="{{ route('journal') }}" class="text-link mt-8 inline-block">← Back to Journal</a>
            </div>
        </aside>

        <div>
            <div class="max-w-3xl">
                @foreach(preg_split('/\R{2,}/', trim((string) $article->content)) as $paragraph)
                    @if(trim($paragraph) !== '')
                        <p class="mb-7 text-[17px] leading-8 text-black/70">
                            {!! nl2br(e(trim($paragraph))) !!}
                        </p>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-t border-black/10 bg-[#f7f6f2]">
        <div class="house-container grid gap-8 py-14 sm:grid-cols-[1fr_auto] sm:items-center lg:py-16">
            <div>
                <p class="ui-label text-black/35">Continue reading</p>
                <p class="mt-3 display-serif text-[38px] leading-[.95] sm:text-[48px]">More stories from the house.</p>
            </div>
            <a href="{{ route('journal') }}" class="btn-solid">Back to Journal</a>
        </div>
    </section>
</article>

@endsection

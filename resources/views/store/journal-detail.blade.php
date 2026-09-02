@extends('layouts.store')

@php
    $imageUrl = function ($path) {
        if (!$path) return null;
        if (str_starts_with($path,'http') || str_starts_with($path,'/')) return $path;
        return asset('storage/'.$path);
    };

    $featuredImage = $imageUrl($article->featured_image_path ?? null);
    $socialImage = $imageUrl($article->og_image_path ?? null) ?: $featuredImage;
    $fallbackEditorial = file_exists(public_path('images/journal/article-fallback.webp'))
        ? asset('images/journal/article-fallback.webp')
        : (config('storefront.campaigns.signature.image') ?? null);
    $hero = $featuredImage ?: $fallbackEditorial;
    $categories = collect($article->categories ?? [])->filter()->values();
    $tags = collect($article->tags ?? [])->filter()->values();
    $articleCanonical = $article->canonical_url ?: route('journal.show',$article->slug);
@endphp

@section('title',$article->meta_title ?: $article->title)
@section('description',$article->meta_description ?: $article->excerpt)
@section('canonical',$articleCanonical)
@section('og_title',$article->og_title ?: $article->meta_title ?: $article->title)
@section('og_description',$article->og_description ?: $article->meta_description ?: $article->excerpt)
@section('og_image',$socialImage ?: '')
@section('og_type','article')

@section('content')
<article class="bg-white pt-[100px] text-black">
    <header class="house-container py-14 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-[1.35fr_.65fr] lg:items-end">
            <div class="min-w-0">
                <p class="ui-label text-black/35">
                    {{ $article->eyebrow ?: 'House Journal' }}
                    ·
                    {{ $article->published_at?->format('d M Y') ?: 'Editorial' }}
                </p>

                <h1 class="mt-5 max-w-5xl break-words display-serif text-[50px] leading-[.93] tracking-[-.035em] sm:text-[66px] lg:text-[76px]">
                    {{ $article->title }}
                </h1>
            </div>

            <div class="min-w-0">
                <p class="max-w-lg break-words text-sm leading-7 text-black/52">
                    {{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->content ?? ''), 190) }}
                </p>
                @if($article->author_name)
                    <p class="mt-4 ui-label text-black/30">By {{ $article->author_name }}</p>
                @endif
            </div>
        </div>
    </header>

    @if($hero)
        <div class="house-container">
            <div class="relative min-h-[52vh] overflow-hidden sm:min-h-[60vh] lg:min-h-[76vh]">
                <img src="{{ $hero }}" alt="{{ $article->title }}" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
            </div>
        </div>
    @endif

    <section class="house-container grid gap-12 py-16 lg:grid-cols-[.4fr_1.6fr] lg:py-24">
        <aside class="min-w-0">
            <div class="lg:sticky lg:top-[130px]">
                <p class="ui-label text-black/35">House Journal</p>

                <div class="mt-6 space-y-3 text-[10px] uppercase tracking-[.14em] text-black/40">
                    <p>{{ $article->eyebrow ?: 'Editorial' }}</p>
                    <p>{{ $article->published_at?->format('F Y') ?: 'Scents by Aamir' }}</p>
                    @if($article->author_name)<p>{{ $article->author_name }}</p>@endif
                </div>

                @if($categories->isNotEmpty())
                    <div class="mt-8 flex flex-wrap gap-2">
                        @foreach($categories as $category)
                            <span class="border border-black/10 px-3 py-2 text-[9px] uppercase tracking-[.12em] text-black/45">{{ $category }}</span>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('journal') }}" class="text-link mt-8 inline-block">← Back to Journal</a>
            </div>
        </aside>

        <div class="min-w-0">
            @if(trim((string)$article->content) !== '')
                @if(preg_match('/<\/?[a-z][\s\S]*>/i', (string)$article->content))
                    <div class="journal-rich-content max-w-3xl break-words text-[17px] leading-8 text-black/70
                        [&_p]:mb-7 [&_h2]:mb-5 [&_h2]:mt-12 [&_h2]:font-serif [&_h2]:text-[38px] [&_h2]:leading-[1.05]
                        [&_h3]:mb-4 [&_h3]:mt-10 [&_h3]:font-serif [&_h3]:text-[30px] [&_h3]:leading-tight
                        [&_ul]:mb-7 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6 [&_ol]:mb-7 [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6
                        [&_blockquote]:my-10 [&_blockquote]:border-l [&_blockquote]:border-black/20 [&_blockquote]:pl-6 [&_blockquote]:font-serif [&_blockquote]:text-[27px] [&_blockquote]:italic [&_blockquote]:leading-[1.25]
                        [&_figure]:my-10 [&_img]:my-10 [&_img]:h-auto [&_img]:max-w-full [&_img]:object-cover [&_figcaption]:-mt-6 [&_figcaption]:mb-10 [&_figcaption]:text-xs [&_figcaption]:text-black/40
                        [&_a]:underline [&_a]:underline-offset-4 [&_strong]:font-semibold">
                        {!! $article->content !!}
                    </div>
                @else
                    <div class="max-w-3xl">
                        @foreach(preg_split('/\R{2,}/', trim((string) $article->content)) as $paragraph)
                            @if(trim($paragraph) !== '')
                                <p class="mb-7 text-[17px] leading-8 text-black/70">{!! nl2br(e(trim($paragraph))) !!}</p>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endif

            @if($tags->isNotEmpty())
                <div class="mt-12 flex max-w-3xl flex-wrap gap-2 border-t border-black/10 pt-7">
                    @foreach($tags as $tag)
                        <span class="text-[9px] uppercase tracking-[.12em] text-black/35">#{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
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

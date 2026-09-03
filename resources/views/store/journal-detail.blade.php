@extends('layouts.store')

@php
    $resolveJournalImage = function ($path) {
        if (!$path) return null;

        $path = str_replace('\\', '/', trim((string) $path));

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/media/')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return route('store.media', ['path' => ltrim($path, '/')]);
    };

    $imagePath = data_get($article, 'featured_image_path');
    $featuredImage = $resolveJournalImage($imagePath);

    $fallbackEditorial = file_exists(public_path('images/journal/article-fallback.webp'))
        ? asset('images/journal/article-fallback.webp')
        : (config('storefront.campaigns.signature.image') ?? null);

    $hero = $featuredImage ?: $fallbackEditorial;

    // Older imports may already contain /storage/journal/... inline image URLs.
    // Rewrite only Journal media to the Laravel media endpoint at render time,
    // so no public/storage symlink is required.
    $articleContent = (string) data_get($article, 'content', '');
    $articleContent = preg_replace_callback(
        '~(?:https?://[^"\'\s>]+)?/storage/(journal/[^"\'\s>]+)~i',
        fn ($m) => route('store.media', ['path' => $m[1]]),
        $articleContent
    ) ?? $articleContent;
@endphp

@section('title',data_get($article,'meta_title') ?: (data_get($article,'title','Journal').' — Scents by Aamir'))
@section('description',data_get($article,'meta_description') ?: data_get($article,'excerpt'))

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
                    @if(data_get($article,'author_name'))<p>By {{ data_get($article,'author_name') }}</p>@endif
                </div>

                @if(!empty(data_get($article,'wordpress_categories', [])))
                    <div class="mt-6">
                        <p class="ui-label text-black/25">Categories</p>
                        <div class="mt-2 flex flex-wrap gap-x-2 gap-y-1 text-[10px] uppercase tracking-[.11em] text-black/42">
                            @foreach(data_get($article,'wordpress_categories', []) as $category)
                                <span>{{ $category['name'] ?? '' }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty(data_get($article,'wordpress_tags', [])))
                    <div class="mt-5">
                        <p class="ui-label text-black/25">Tags</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach(data_get($article,'wordpress_tags', []) as $tag)
                                <span class="border border-black/10 px-2 py-1 text-[9px] uppercase tracking-[.1em] text-black/42">{{ $tag['name'] ?? '' }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <a href="{{ route('journal') }}" class="text-link mt-8 inline-block">← Back to Journal</a>
            </div>
        </aside>

        <div class="min-w-0">
            <div class="journal-article max-w-3xl text-[17px] leading-8 text-black/70
                [&_p]:mb-7
                [&_h2]:mb-5 [&_h2]:mt-12 [&_h2]:font-serif [&_h2]:text-[36px] [&_h2]:leading-[1]
                [&_h3]:mb-4 [&_h3]:mt-10 [&_h3]:font-serif [&_h3]:text-[29px] [&_h3]:leading-tight
                [&_h4]:mb-4 [&_h4]:mt-10 [&_h4]:font-serif [&_h4]:text-[27px] [&_h4]:leading-tight [&_h4]:text-black
                [&_h5]:mb-4 [&_h5]:mt-10 [&_h5]:font-serif [&_h5]:text-[27px] [&_h5]:leading-tight [&_h5]:tracking-[-.015em] [&_h5]:text-black
                [&_h6]:mb-3 [&_h6]:mt-8 [&_h6]:font-serif [&_h6]:text-[22px] [&_h6]:leading-tight [&_h6]:text-black
                [&_ul]:mb-7 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6
                [&_ol]:mb-7 [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6
                [&_blockquote]:my-9 [&_blockquote]:border-l [&_blockquote]:border-black/20 [&_blockquote]:pl-6 [&_blockquote]:font-serif [&_blockquote]:text-[24px] [&_blockquote]:italic [&_blockquote]:leading-9
                [&_figure]:my-10
                [&_img]:h-auto [&_img]:max-w-full [&_img]:object-cover
                [&_figcaption]:mt-3 [&_figcaption]:text-xs [&_figcaption]:leading-5 [&_figcaption]:text-black/40
                [&_a]:underline [&_a]:underline-offset-4
                [&_hr]:my-10 [&_hr]:border-black/10
                [&_strong]:font-semibold">
                {!! $articleContent !!}
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

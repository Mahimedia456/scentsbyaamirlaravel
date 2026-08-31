@extends('layouts.store')

@php
    $isDark = ($page['theme'] ?? 'light') === 'dark';
@endphp

@section('title', ($page['meta_title'] ?? $page['title']).' — Scents by Aamir')
@section('description', $page['meta_description'] ?? $page['intro'] ?? '')

@section('content')
<section class="{{ $isDark ? 'bg-[#11100f] text-white' : 'bg-[#f7f6f2] text-black' }} pt-[108px]">
    <div class="house-container py-16 lg:py-24">
        <p class="ui-label {{ $isDark ? 'text-white/35' : 'text-black/35' }}">{{ $page['eyebrow'] ?? 'House Information' }}</p>
        <div class="mt-5 grid gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
            <h1 class="display-serif max-w-6xl text-6xl leading-[.88] sm:text-8xl lg:text-[8rem]">
                {{ $page['title'] }}
            </h1>
            <p class="max-w-xl text-sm leading-7 {{ $isDark ? 'text-white/55' : 'text-black/52' }}">
                {{ $page['intro'] }}
            </p>
        </div>
    </div>
</section>

<section class="{{ $isDark ? 'bg-black text-white' : 'bg-white text-black' }}">
    <div class="house-container py-14 lg:py-20">
        @if(($page['source'] ?? 'config') === 'db')
            <article class="mx-auto max-w-4xl text-base leading-8 {{ $isDark ? 'text-white/70' : 'text-black/65' }}">{!! $page['content'] !!}</article>
        @else
        <div class="divide-y {{ $isDark ? 'divide-white/15 border-white/15' : 'divide-black/10 border-black/10' }} border-y">
            @foreach($page['sections'] as $index => $section)
                <article class="grid gap-5 py-9 lg:grid-cols-[100px_.7fr_1.3fr] lg:items-start">
                    <span class="ui-label {{ $isDark ? 'text-white/25' : 'text-black/25' }}">
                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h2 class="display-serif text-4xl leading-[.95] sm:text-5xl">{{ $section['title'] }}</h2>
                    <p class="max-w-2xl text-sm leading-7 {{ $isDark ? 'text-white/55' : 'text-black/50' }}">{{ $section['copy'] }}</p>
                </article>
            @endforeach
        </div>
        @endif
    </div>
</section>

<section class="{{ $isDark ? 'bg-[#f7f6f2] text-black' : 'bg-[#11100f] text-white' }}">
    <div class="house-container grid gap-8 py-14 sm:grid-cols-[1fr_auto] sm:items-center">
        <div>
            <p class="ui-label {{ $isDark ? 'text-black/35' : 'text-white/35' }}">Need more?</p>
            <h2 class="mt-3 display-serif text-4xl sm:text-5xl">The house is here to help.</h2>
        </div>
        <a href="{{ route('contact') }}" class="{{ $isDark ? 'btn-solid' : 'btn-outline border-white text-white hover:bg-white hover:text-black' }}">
            Contact support
        </a>
    </div>
</section>
@endsection

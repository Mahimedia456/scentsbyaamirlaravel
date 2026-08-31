@props([
    'eyebrow',
    'title',
    'copy' => null,
    'href' => '#',
    'tone' => 'sage',
    'tall' => false,
])

@php
    $backgrounds = [
        'sage' => 'bg-[radial-gradient(circle_at_24%_22%,rgba(244,192,111,.85),transparent_20%),radial-gradient(circle_at_78%_28%,rgba(111,119,181,.78),transparent_24%),radial-gradient(circle_at_56%_70%,rgba(137,194,186,.72),transparent_28%),linear-gradient(135deg,#a7b9aa,#7ea8a4)]',
        'rose' => 'bg-[radial-gradient(circle_at_24%_30%,rgba(235,169,173,.88),transparent_22%),radial-gradient(circle_at_72%_28%,rgba(103,76,90,.55),transparent_24%),radial-gradient(circle_at_56%_72%,rgba(211,190,164,.72),transparent_28%),linear-gradient(135deg,#d2b6b0,#a9827f)]',
        'sand' => 'bg-[radial-gradient(circle_at_28%_24%,rgba(239,221,176,.92),transparent_22%),radial-gradient(circle_at_75%_25%,rgba(177,139,102,.58),transparent_24%),linear-gradient(135deg,#d8c8ae,#b8a388)]',
        'night' => 'bg-[radial-gradient(circle_at_22%_24%,rgba(124,106,82,.56),transparent_22%),radial-gradient(circle_at_76%_30%,rgba(62,70,86,.74),transparent_26%),linear-gradient(135deg,#181818,#050505)]',
    ];
@endphp

<a href="{{ $href }}" class="campaign-panel group block {{ $tall ? 'min-h-[720px]' : 'min-h-[520px]' }} {{ $backgrounds[$tone] ?? $backgrounds['sage'] }}">
    <div class="absolute inset-0 transition duration-700 group-hover:scale-[1.03]" data-parallax></div>
    <div class="absolute inset-0 bg-black/[0.05]"></div>

    <div class="campaign-copy">
        <p class="ui-label text-white/60">{{ $eyebrow }}</p>
        <h3 class="mt-3 display-serif max-w-xl text-5xl leading-[.92] sm:text-6xl">{{ $title }}</h3>
        @if($copy)
            <p class="mt-4 max-w-md text-sm leading-6 text-white/70">{{ $copy }}</p>
        @endif
        <span class="mt-6 inline-block ui-label">Discover →</span>
    </div>
</a>

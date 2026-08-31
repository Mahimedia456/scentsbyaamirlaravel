@props([
    'slug',
    'eyebrow',
    'title',
    'copy',
    'tone' => 'sand',
    'size' => 'default',
])

@php
    $tones = [
        'sand' => 'bg-[radial-gradient(circle_at_28%_20%,rgba(240,219,171,.90),transparent_23%),radial-gradient(circle_at_72%_72%,rgba(149,115,88,.48),transparent_28%),linear-gradient(135deg,#d8c8ae,#a99679)]',
        'night' => 'bg-[radial-gradient(circle_at_30%_22%,rgba(89,78,62,.52),transparent_24%),radial-gradient(circle_at_72%_70%,rgba(49,57,74,.72),transparent_30%),linear-gradient(135deg,#181818,#050505)]',
        'sage' => 'bg-[radial-gradient(circle_at_25%_24%,rgba(216,180,102,.72),transparent_21%),radial-gradient(circle_at_74%_30%,rgba(115,135,132,.75),transparent_25%),linear-gradient(135deg,#9cac9e,#708b87)]',
        'rose' => 'bg-[radial-gradient(circle_at_24%_22%,rgba(235,175,178,.78),transparent_21%),radial-gradient(circle_at_72%_68%,rgba(146,103,109,.55),transparent_28%),linear-gradient(135deg,#d7b6b3,#a98481)]',
    ];

    $height = $size === 'large' ? 'min-h-[720px]' : 'min-h-[520px]';
@endphp

<a href="{{ route('collections.show', $slug) }}"
   class="group relative block overflow-hidden {{ $height }} {{ $tones[$tone] ?? $tones['sand'] }} text-white">
    <div class="absolute inset-0 bg-black/[0.06]"></div>
    <div class="absolute inset-x-0 bottom-0 h-[48%] bg-gradient-to-t from-black/70 to-transparent"></div>

    <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8 lg:p-10">
        <p class="ui-label text-white/60">{{ $eyebrow }}</p>
        <h2 class="mt-3 display-serif text-5xl leading-[.9] sm:text-6xl">{{ $title }}</h2>
        <p class="mt-4 max-w-lg text-sm leading-6 text-white/68">{{ $copy }}</p>
        <span class="mt-6 inline-block ui-label transition group-hover:translate-x-1">Explore collection →</span>
    </div>
</a>

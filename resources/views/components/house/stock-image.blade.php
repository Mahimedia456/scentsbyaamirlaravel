@props([
    'src',
    'alt' => '',
    'position' => 'center',
    'class' => '',
    'overlay' => false,
])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-[#dedbd4] '.$class]) }}>
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="lazy"
        decoding="async"
        referrerpolicy="no-referrer"
        class="absolute inset-0 h-full w-full object-cover transition duration-700"
        style="object-position: {{ $position }};"
        onerror="this.style.display='none'"
    >
    @if($overlay)
        <div class="absolute inset-0 bg-black/20"></div>
    @endif
    {{ $slot }}
</div>

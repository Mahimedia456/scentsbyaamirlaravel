@props(['product'])

@php
    $world = $product['world'];
    $layout = $world['layout'] ?? 'offset';
@endphp

<section style="background:{{ $product['theme']['background'] }}; color:{{ $product['theme']['ink'] }};">
    @if($layout === 'cinema')
        <div class="relative min-h-[82vh] overflow-hidden">
            <img src="{{ $product['world_image'] }}" alt="{{ $product['name'] }} world" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="house-container relative flex min-h-[82vh] items-end py-12 text-white lg:py-16">
                <div class="grid w-full gap-8 lg:grid-cols-[1.25fr_.75fr] lg:items-end">
                    <div>
                        <p class="ui-label text-white/55">{{ $world['kicker'] }}</p>
                        <h2 class="mt-4 display-serif max-w-5xl text-6xl leading-[.88] sm:text-8xl">{{ $world['statement'] }}</h2>
                    </div>
                    <p class="ui-label text-white/55 lg:text-right">{{ $world['mood'] }}</p>
                </div>
            </div>
        </div>
    @elseif($layout === 'light')
        <div class="house-container grid gap-4 py-12 lg:grid-cols-[.88fr_1.12fr] lg:py-16">
            <div class="flex min-h-[620px] items-end p-8 sm:p-12" style="background:{{ $product['theme']['surface'] }};">
                <div>
                    <p class="ui-label opacity-45">{{ $world['kicker'] }}</p>
                    <h2 class="mt-4 display-serif text-6xl leading-[.9] sm:text-7xl">{{ $world['statement'] }}</h2>
                    <p class="mt-8 ui-label opacity-45">{{ $world['mood'] }}</p>
                </div>
            </div>
            <div class="relative min-h-[620px] overflow-hidden">
                <img loading="lazy" decoding="async" src="{{ $product['world_image'] }}" alt="{{ $product['name'] }} atmosphere" class="absolute inset-0 h-full w-full object-cover">
            </div>
        </div>
    @else
        <div class="house-container py-12 lg:py-16">
            <div class="grid gap-4 lg:grid-cols-[1.2fr_.8fr]">
                <div class="relative min-h-[700px] overflow-hidden">
                    <img loading="lazy" decoding="async" src="{{ $product['world_image'] }}" alt="{{ $product['name'] }} atmosphere" class="absolute inset-0 h-full w-full object-cover">
                </div>
                <div class="flex min-h-[500px] items-end p-8 sm:p-12" style="background:{{ $product['theme']['surface'] }};">
                    <div>
                        <p class="ui-label opacity-45">{{ $world['kicker'] }}</p>
                        <h2 class="mt-4 display-serif text-6xl leading-[.9]">{{ $world['statement'] }}</h2>
                        <p class="mt-8 ui-label opacity-45">{{ $world['mood'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>

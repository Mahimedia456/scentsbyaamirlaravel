@php
    $answers = $answers ?? [];
    $recommendations = collect($recommendations ?? []);
@endphp

<div class="grid gap-10 lg:grid-cols-[.58fr_1.42fr]">
    <div>
        <p class="ui-label text-white/40">Your Direction</p>
        <h2 class="mt-4 max-w-md display-serif text-[48px] leading-[.94] sm:text-[58px]">A considered edit.</h2>

        <div class="mt-8 divide-y divide-white/12 border-y border-white/12">
            @foreach([
                ['Mood', $answers['mood'] ?? '—'],
                ['Intensity', $answers['intensity'] ?? '—'],
                ['Occasion', $answers['occasion'] ?? '—'],
                ['For', ($answers['audience'] ?? 'Any') === 'any' ? 'Any' : ucfirst($answers['audience'] ?? 'Any')],
            ] as [$label, $value])
                <div class="grid grid-cols-[110px_1fr] gap-4 py-4">
                    <span class="ui-label text-white/32">{{ $label }}</span>
                    <span class="text-sm text-white/72">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        <button
            type="button"
            @click="resetFinder()"
            class="btn-outline mt-8 border-white/35 text-white hover:bg-white hover:text-black"
        >
            Start again
        </button>
    </div>

    <div>
        <div class="mb-6 flex items-end justify-between gap-5 border-b border-white/12 pb-5">
            <div>
                <p class="ui-label text-white/35">Recommended for you</p>
                <h3 class="mt-2 display-serif text-[36px] leading-none">Four fragrances to begin with.</h3>
            </div>
            <span class="ui-label text-white/30">{{ $recommendations->count() }} matches</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @forelse($recommendations as $recommendation)
                @php
                    $product = $recommendation['product'];
                    $matched = collect($recommendation['matched'] ?? []);
                @endphp

                <a
                    href="{{ route('product.show', $product['slug']) }}"
                    class="group bg-white text-black"
                >
                    <div class="relative aspect-[4/5] overflow-hidden bg-[#efeee9]">
                        @if($product['image'] ?? null)
                            <img
                                src="{{ $product['image'] }}"
                                alt="{{ $product['display_name'] ?? $product['name'] }}"
                                class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"
                                loading="lazy"
                            >
                        @endif

                        <div class="absolute inset-x-0 top-0 flex items-center justify-between p-4">
                            <span class="border border-black/10 bg-white/90 px-3 py-2 text-[8px] uppercase tracking-[.16em] backdrop-blur">
                                {{ $product['audience'] ?? 'Unisex' }}
                            </span>
                            <span class="border border-black/10 bg-white/90 px-3 py-2 text-[8px] uppercase tracking-[.16em] backdrop-blur">
                                Match {{ $loop->iteration }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <p class="ui-label text-black/35">{{ $product['family'] ?? 'Fine Fragrance' }}</p>
                        <h4 class="mt-2 display-serif text-[36px] leading-[.96]">{{ $product['display_name'] ?? $product['name'] }}</h4>

                        @if($matched->isNotEmpty())
                            <p class="mt-4 text-[10px] uppercase tracking-[.12em] text-black/38">
                                Matches: {{ $matched->join(' · ') }}
                            </p>
                        @endif

                        <div class="mt-5 flex items-center justify-between border-t border-black/10 pt-4">
                            <span class="text-sm">PKR {{ $product['price'] }}</span>
                            <span class="text-sm transition-transform group-hover:translate-x-1">→</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full border border-white/15 p-8">
                    <p class="display-serif text-4xl">Complete the finder to see your edit.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

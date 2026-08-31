@props([
    'product' => null,
    'slug' => null,
    'name' => null,
    'family' => null,
    'price' => null,
    'index' => null,
    'tone' => 'light',
    'badge' => null,
    'image' => null,
])

@php
    if (is_array($product)) {
        $slug = $slug ?: ($product['slug'] ?? null);
        $name = $name ?: ($product['name'] ?? 'Fragrance');
        $family = $family ?: ($product['family'] ?? 'Fine Fragrance');
        $price = $price ?: ($product['price'] ?? '0');
        $badge = $badge ?? ($product['badge'] ?? null);
        $image = $image ?: ($product['image'] ?? null);
        $productId = $product['id'] ?? null;
        $priceValue = $product['price_value'] ?? null;
    } else {
        $productId = null;
        $priceValue = null;
    }

    $slug = $slug ?: strtolower(str_replace(' ', '-', (string) $name));
    $productData = config('storefront.products.'.$slug, []);
    $name = $name ?: ($productData['name'] ?? 'Fragrance');
    $family = $family ?: ($productData['family'] ?? 'Fine Fragrance');
    $price = $price ?: ($productData['price'] ?? '0');
    $image = $image ?: ($productData['image'] ?? null);
    $badge = $badge ?? ($productData['badge'] ?? null);
@endphp

<article class="group" data-hover-tilt>
    <a href="{{ route('product.show', $slug) }}" class="block">
        <div class="{{ $tone === 'dark' ? 'product-stage-dark' : 'product-stage' }}">
            @if($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $name }}"
                    loading="lazy"
                    decoding="async"
                    referrerpolicy="no-referrer"
                    class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"
                    onerror="this.style.display='none'"
                >
                <div class="absolute inset-0 {{ $tone === 'dark' ? 'bg-black/25' : 'bg-white/10' }}"></div>
            @else
                <div class="absolute inset-0 editorial-grid opacity-40"></div>
            @endif

            @if($badge)
                <span class="absolute left-3 top-3 z-20 bg-white px-3 py-2 text-[8px] uppercase tracking-[.16em] text-black">{{ $badge }}</span>
            @endif

            <button
                type="button"
                @click.prevent.stop="$store.commerce.toggleWishlist({
                    product_id: @js($productId),
                    slug: @js($slug),
                    name: @js($name),
                    family: @js($family),
                    price: @js($price),
                    price_value: @js($priceValue),
                    image: @js($image)
                })"
                class="absolute right-3 top-3 z-20 text-[13px] {{ $tone === 'dark' ? 'text-white' : 'text-black' }}"
                aria-label="Wishlist"
            >
                <span x-text="$store.commerce.inWishlist(@js($productId ?? $slug)) ? '♥' : '♡'">♡</span>
            </button>

            <div class="absolute inset-x-0 bottom-0 translate-y-full bg-black py-3 text-center text-[8px] uppercase tracking-[.16em] text-white transition duration-300 group-hover:translate-y-0">
                View Product
            </div>
        </div>

        <div class="grid grid-cols-[1fr_auto] gap-4 pt-4">
            <div>
                <h3 class="text-[12px] font-medium uppercase tracking-[.03em]">{{ $name }}</h3>
                <p class="mt-1.5 text-[9px] uppercase tracking-[.14em] text-black/42">{{ $family }}</p>
            </div>
            <p class="text-[11px]">PKR {{ $price }}</p>
        </div>
    </a>
</article>

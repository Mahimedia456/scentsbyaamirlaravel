<section class="border-y border-black/10 bg-[#f7f6f2] text-black" aria-label="House services">
    <div class="house-container grid divide-y divide-black/10 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4">
        @foreach([
            ['Complimentary Delivery','On selected orders'],
            ['Gift Presentation','House wrapping available'],
            ['Secure Checkout','Payment-ready architecture'],
            ['Fragrance Guidance','Find by mood and material'],
        ] as [$title,$copy])
            <div class="px-5 py-7 sm:px-7">
                <p class="ui-label">{{ $title }}</p>
                <p class="mt-2 text-xs leading-5 text-black/45">{{ $copy }}</p>
            </div>
        @endforeach
    </div>
</section>

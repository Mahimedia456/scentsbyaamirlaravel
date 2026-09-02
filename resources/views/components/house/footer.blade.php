<footer class="bg-[#080808] text-white">
    <div class="house-container py-14 sm:py-16 lg:py-20">
        <div class="grid gap-12 border-b border-white/15 pb-12 lg:grid-cols-[1.1fr_.9fr] lg:items-end lg:pb-16">
            <div>
                <p class="ui-label text-white/35">Scents by Aamir</p>
                <img
                    src="{{ asset('logo.png') }}"
                    alt="Scents by Aamir"
                    width="560"
                    height="131"
                    loading="lazy"
                    decoding="async"
                    class="mt-7 block h-auto w-full max-w-[560px] object-contain object-left brightness-0 invert"
                >
            </div>

            <div class="lg:justify-self-end lg:max-w-xl">
                <p class="ui-label text-white/35">Private Notes</p>
                <h2 class="mt-4 display-serif text-4xl leading-[.95] sm:text-5xl">New worlds, launches and house stories.</h2>
                <form method="POST" action="{{ route('newsletter.store') }}" class="mt-7 grid grid-cols-[1fr_auto] border-b border-white/35">
                    @csrf
                    <input type="hidden" name="source" value="footer">
                    <label class="sr-only" for="footer-email">Email address</label>
                    <input id="footer-email" name="email" type="email" required placeholder="Email address"
                           class="min-h-[54px] border-0 bg-transparent px-0 text-sm text-white placeholder:text-white/35 focus:ring-0">
                    <button type="submit" class="min-w-[54px] text-xl" aria-label="Subscribe">→</button>
                </form>
                @if(session('newsletter_success'))<p class="mt-3 text-xs text-white/55">{{ session('newsletter_success') }}</p>@endif
            </div>
        </div>

        <div class="grid gap-x-8 gap-y-10 py-12 sm:grid-cols-2 lg:grid-cols-4 lg:py-14">
            <div>
                <p class="ui-label text-white/32">Shop</p>
                <nav class="mt-5 grid gap-3 text-[11px] leading-5 text-white/68">
                    <a href="{{ route('shop') }}">All Fragrances</a>
                    <a href="{{ route('collections') }}">Collections</a>
                    <a href="{{ route('finder') }}">Fragrance Finder</a>
                    <a href="{{ route('ingredients') }}">Ingredients</a>
                    <a href="{{ route('journal') }}">Journal</a>
                </nav>
            </div>

            <div>
                <p class="ui-label text-white/32">Customer Care</p>
                <nav class="mt-5 grid gap-3 text-[11px] leading-5 text-white/68">
                    <a href="{{ route('contact') }}">Contact & Support</a>
                    <a href="{{ route('shipping') }}">Shipping</a>
                    <a href="{{ route('returns') }}">Returns</a>
                    <a href="{{ route('track-order') }}">Track My Order</a>
                    <a href="{{ route('account') }}">My Account</a>
                </nav>
            </div>

            <div>
                <p class="ui-label text-white/32">House Services</p>
                <nav class="mt-5 grid gap-3 text-[11px] leading-5 text-white/68">
                    <a href="{{ route('gifting') }}">Gifting</a>
                    <a href="{{ route('finder') }}">Guided Selection</a>
                    <a href="{{ route('gift-wrapping') }}">Gift Wrapping</a>
                    <a href="{{ route('personalized-message') }}">Personalized Message</a>
                    <a href="{{ route('checkout') }}">Secure Checkout</a>
                </nav>
            </div>

            <div>
                <p class="ui-label text-white/32">Legal & Social</p>
                <nav class="mt-5 grid gap-3 text-[11px] leading-5 text-white/68">
                    <a href="{{ route('privacy') }}">Privacy Policy</a>
                    <a href="{{ route('terms') }}">Terms & Conditions</a>
                    <a href="{{ route('cookies') }}">Cookie Policy</a>
                    <a href="{{ route('accessibility') }}">Accessibility</a>

                    <div class="mt-3 flex flex-wrap gap-x-3 gap-y-2 text-white/38">
                        <a href="{{ route('social','instagram') }}">Instagram</a>
                        <span>·</span>
                        <a href="{{ route('social','facebook') }}">Facebook</a>
                        <span>·</span>
                        <a href="{{ route('social','tiktok') }}">TikTok</a>
                    </div>
                </nav>
            </div>
        </div>

        <div class="grid gap-3 border-t border-white/15 pt-6 text-[9px] uppercase tracking-[.12em] text-white/38 sm:grid-cols-2 lg:grid-cols-3 lg:items-center">
            <span>© {{ date('Y') }} Scents by Aamir</span>
            <span class="lg:text-center">Pakistan · English</span>
            <span class="sm:col-span-2 lg:col-span-1 lg:text-right">Modern Fine Fragrance</span>
        </div>
    </div>
</footer>

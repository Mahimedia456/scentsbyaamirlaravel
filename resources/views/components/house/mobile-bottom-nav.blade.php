<nav
    class="fixed inset-x-0 bottom-0 z-[84] border-t border-black/10 bg-[#f7f6f2]/98 pb-[env(safe-area-inset-bottom)] text-black backdrop-blur-xl lg:hidden"
    aria-label="Mobile quick navigation"
>
    <div class="grid h-[62px] grid-cols-5">
        <a href="{{ route('home') }}" class="flex min-w-0 flex-col items-center justify-center gap-1 text-[8px] uppercase tracking-[.12em]" aria-label="Home">
            <svg class="h-[19px] w-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 10.7 12 3l9 7.7v9.1a1.2 1.2 0 0 1-1.2 1.2H4.2A1.2 1.2 0 0 1 3 19.8z"/><path d="M9 21v-7h6v7"/></svg>
            <span>Home</span>
        </a>

        <a href="{{ route('shop') }}" class="flex min-w-0 flex-col items-center justify-center gap-1 text-[8px] uppercase tracking-[.12em]" aria-label="Shop">
            <svg class="h-[19px] w-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 8h16l-1 13H5z"/><path d="M8 9V7a4 4 0 0 1 8 0v2"/></svg>
            <span>Shop</span>
        </a>

        <a href="{{ route('wishlist') }}" class="relative flex min-w-0 flex-col items-center justify-center gap-1 text-[8px] uppercase tracking-[.12em]" aria-label="Wishlist">
            <span class="relative">
                <svg class="h-[19px] w-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M20.8 4.6a5.4 5.4 0 0 0-7.6 0L12 5.8l-1.2-1.2a5.4 5.4 0 1 0-7.6 7.6L12 21l8.8-8.8a5.4 5.4 0 0 0 0-7.6Z"/></svg>
                <span x-show="$store.commerce.wishlistCount > 0" x-text="$store.commerce.wishlistCount" class="absolute -right-2.5 -top-2 grid min-h-[15px] min-w-[15px] place-items-center rounded-full bg-black px-1 text-[8px] leading-none text-white"></span>
            </span>
            <span>Saved</span>
        </a>

        <a href="{{ route('account') }}" class="flex min-w-0 flex-col items-center justify-center gap-1 text-[8px] uppercase tracking-[.12em]" aria-label="Account">
            <svg class="h-[19px] w-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></svg>
            <span>Account</span>
        </a>

        <a href="{{ route('cart') }}" class="relative flex min-w-0 flex-col items-center justify-center gap-1 text-[8px] uppercase tracking-[.12em]" aria-label="Shopping bag">
            <span class="relative">
                <svg class="h-[19px] w-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 8h14l1 13H4z"/><path d="M9 9V7a3 3 0 0 1 6 0v2"/></svg>
                <span x-show="$store.commerce.count > 0" x-text="$store.commerce.count" class="absolute -right-2.5 -top-2 grid min-h-[15px] min-w-[15px] place-items-center rounded-full bg-black px-1 text-[8px] leading-none text-white"></span>
            </span>
            <span>Bag</span>
        </a>
    </div>
</nav>

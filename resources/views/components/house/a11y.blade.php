<a href="#main-content"
   class="fixed left-4 top-4 z-[120] -translate-y-24 bg-black px-4 py-3 text-[10px] uppercase tracking-[.14em] text-white transition focus:translate-y-0">
    Skip to content
</a>

<div
    x-data="{ online: navigator.onLine }"
    @online.window="online=true"
    @offline.window="online=false"
    x-cloak
    x-show="!online"
    class="fixed inset-x-0 bottom-0 z-[110] bg-black px-5 py-3 text-center text-[10px] uppercase tracking-[.14em] text-white"
    role="status"
    aria-live="polite"
>
    You are offline. Some imagery and checkout actions may be unavailable.
</div>

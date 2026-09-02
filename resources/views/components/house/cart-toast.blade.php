<div
    x-cloak
    x-show="$store.commerce.toastOpen"
    x-transition.opacity.duration.160ms
    class="fixed inset-x-4 bottom-[78px] z-[96] mx-auto max-w-md border border-black/10 bg-white p-4 text-black shadow-2xl sm:bottom-6 sm:left-auto sm:right-6 sm:mx-0 sm:w-[360px]"
    role="status"
    aria-live="polite"
>
    <div class="flex items-center gap-3">
        <span class="grid h-10 w-10 shrink-0 place-items-center bg-black text-white">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 8h14l1 13H4z"/><path d="M9 9V7a3 3 0 0 1 6 0v2"/><path d="m8.5 14 2 2 5-5"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="ui-label text-black/35">Shopping bag</p>
            <p class="mt-1 text-sm" x-text="$store.commerce.toastMessage"></p>
        </div>
        <button type="button" @click="$store.commerce.toastOpen=false" class="grid h-9 w-9 shrink-0 place-items-center" aria-label="Close notification">×</button>
    </div>
</div>

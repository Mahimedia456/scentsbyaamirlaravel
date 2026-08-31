@props(['phase', 'title', 'notes', 'index'])

<div class="grid gap-6 border-t border-black/10 py-7 sm:grid-cols-[90px_1fr_auto] sm:items-start">
    <span class="ui-label text-black/35">{{ $index }}</span>
    <div>
        <p class="ui-label text-black/38">{{ $phase }}</p>
        <h3 class="mt-2 display-serif text-4xl">{{ $title }}</h3>
        <p class="mt-3 text-sm leading-6 text-black/52">{{ $notes }}</p>
    </div>
    <span class="text-lg">→</span>
</div>

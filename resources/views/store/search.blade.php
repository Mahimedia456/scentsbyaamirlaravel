@extends('layouts.store')
@section('title', ($q ? 'Search: '.$q : 'Search').' — Scents by Aamir')
@section('content')
<section class="min-h-[75vh] bg-[#f7f6f2] pt-[108px] text-black"><div class="house-container py-14 lg:py-20">
<p class="ui-label text-black/35">Catalog Discovery</p><h1 class="mt-4 display-serif text-6xl sm:text-8xl">Search the house.</h1>
<form method="GET" action="{{ route('search') }}" class="mt-10 flex max-w-3xl border-b border-black"><input autofocus name="q" value="{{ $q }}" class="min-h-[64px] flex-1 border-0 bg-transparent px-0 text-xl focus:ring-0" placeholder="Fragrance, note, material or family"><button class="px-5 text-xs uppercase tracking-[.18em]">Search →</button></form>
@if($q)<div class="mt-14 flex items-end justify-between border-b border-black/10 pb-5"><h2 class="display-serif text-4xl">Results for “{{ $q }}”</h2><span class="ui-label text-black/35">{{ $results->count() }} found</span></div>
@if($results->isNotEmpty())<div class="mt-8 grid gap-x-4 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">@foreach($results as $product)<x-house.product-card :product="$product" />@endforeach</div>@else<div class="py-20 text-center"><p class="display-serif text-5xl">No fragrance found.</p><p class="mt-4 text-sm text-black/50">Try another name, note, material or fragrance family.</p></div>@endif
@else<p class="mt-8 max-w-xl text-sm leading-7 text-black/50">Search our live fragrance catalog by name, fragrance family, notes, story or materials.</p>@endif
</div></section>@endsection

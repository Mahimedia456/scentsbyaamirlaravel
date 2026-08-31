@extends('layouts.store')
@section('title', $title . ' — Scents by Aamir')

@section('content')
<section class="min-h-[70vh] bg-[#f7f6f2] pt-[110px]">
    <div class="house-container flex min-h-[70vh] items-center py-16">
        <div>
            <p class="ui-label text-black/40">Scents by Aamir</p>
            <h1 class="mt-4 display-serif text-7xl sm:text-9xl">{{ $title }}</h1>
            <p class="mt-6 max-w-xl text-sm leading-7 text-black/50">This page is wired and will be developed in its dedicated phase.</p>
            <a href="{{ route('home') }}" class="line-button mt-8">Back Home</a>
        </div>
    </div>
</section>
@endsection

@extends('layouts.store')

@section('title', 'Page Not Found — Scents by Aamir')

@section('content')
<section class="flex min-h-screen items-center bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container w-full py-20">
        <p class="ui-label text-black/35">404</p>
        <h1 class="mt-4 display-serif max-w-5xl text-7xl leading-[.86] sm:text-9xl">This trail ends here.</h1>
        <p class="mt-7 max-w-xl text-sm leading-7 text-black/50">The page may have moved, or the fragrance world you requested does not exist.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('home') }}" class="btn-solid">Return home</a>
            <a href="{{ route('shop') }}" class="btn-outline">Explore fragrances</a>
        </div>
    </div>
</section>
@endsection

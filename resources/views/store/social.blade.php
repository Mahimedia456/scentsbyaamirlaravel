@extends('layouts.store')

@section('title', ucfirst($platform).' — Scents by Aamir')

@section('content')
<section class="flex min-h-[78vh] items-center bg-black pt-[108px] text-white">
    <div class="house-container w-full py-20">
        <p class="ui-label text-white/35">Social</p>
        <h1 class="mt-4 display-serif text-7xl leading-[.88] sm:text-9xl">{{ ucfirst($platform) }}</h1>
        <p class="mt-6 max-w-xl text-sm leading-7 text-white/50">
            The official {{ ucfirst($platform) }} destination will be connected from admin settings when the production social URLs are available.
        </p>
        <a href="{{ route('home') }}" class="btn-outline mt-8 border-white text-white hover:bg-white hover:text-black">Return to the house</a>
    </div>
</section>
@endsection

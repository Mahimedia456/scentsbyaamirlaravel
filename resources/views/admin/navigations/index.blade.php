@extends('admin.layouts.app')
@section('title','Navigation') @section('header','Navigation')
@section('content')
<div class="mb-6 flex justify-end"><a href="{{ route('admin.navigations.create') }}" class="bg-black px-5 py-3 text-xs uppercase tracking-[.16em] text-white">New navigation</a></div><div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">@foreach($navigations as $nav)<a href="{{ route('admin.navigations.edit',$nav) }}" class="border border-black/10 bg-white p-6 hover:border-black"><p class="text-lg font-medium">{{ $nav->name }}</p><p class="mt-2 text-xs text-black/45">Key: {{ $nav->key }} · {{ $nav->items_count }} items · {{ $nav->is_active?'Active':'Inactive' }}</p></a>@endforeach</div>
@endsection

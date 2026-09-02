@extends('layouts.store')
@section('title','Notifications — Scents by Aamir')
@section('content')
<section class="min-h-screen overflow-x-hidden bg-[#f7f6f2] pt-[100px] text-black">
    <div class="house-container py-10 sm:py-12 lg:py-16">
        <p class="ui-label text-black/35">Private Account</p>

        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between sm:gap-6">
            <h1 class="min-w-0 break-words display-serif text-[50px] leading-none sm:text-7xl lg:text-8xl">Notifications</h1>
            <a href="{{ route('account') }}" class="text-link self-start whitespace-nowrap sm:self-auto">Back to account</a>
        </div>

        <div class="mt-8 space-y-3 sm:mt-10">
            @forelse($notifications as $notification)
                <article class="min-w-0 border border-black/10 bg-white p-5 sm:p-6 {{ $notification->read_at ? 'opacity-70' : '' }}">
                    <div class="flex min-w-0 flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="break-words ui-label text-black/35">{{ str_replace('_',' ', $notification->type) }}</p>
                            <h2 class="mt-2 break-words text-lg font-medium">{{ $notification->title }}</h2>
                            <p class="mt-2 break-words text-sm leading-6 text-black/55">{{ $notification->message }}</p>
                            <p class="mt-3 text-xs text-black/35">{{ $notification->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.read',$notification) }}" class="shrink-0">
                                @csrf
                                <button class="text-link">Mark read</button>
                            </form>
                        @endif
                    </div>

                    @if($notification->order_id)
                        <a href="{{ route('orders.show',$notification->order_id) }}" class="mt-5 inline-block text-link">View order →</a>
                    @endif
                </article>
            @empty
                <div class="border border-black/10 bg-white p-8 text-sm text-black/50">
                    No notifications yet. Order and payment updates will appear here.
                </div>
            @endforelse
        </div>

        <div class="mt-8 overflow-x-auto">{{ $notifications->links() }}</div>
    </div>
</section>
@endsection

@extends('layouts.store')

@section('title','My Orders — Scents by Aamir')

@section('content')
<section class="min-h-screen overflow-x-hidden bg-[#f7f6f2] pt-[100px] text-black">
    <div class="border-b border-black/10 bg-white">
        <div class="house-container py-10 sm:py-14">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="ui-label text-black/35">Private Account</p>
                    <h1 class="mt-3 break-words display-serif text-[48px] leading-[.94] sm:text-[64px]">Your orders.</h1>
                </div>
                <a href="{{ route('account') }}" class="text-link self-start sm:self-auto">Back to account →</a>
            </div>
        </div>
    </div>

    <div class="house-container py-8 sm:py-10 lg:py-14">
        @forelse($orders as $order)
            <article x-data="{open:false}" class="border-b border-black/10 py-6 sm:py-7">
                <button type="button" @click="open=!open" class="grid w-full min-w-0 gap-4 text-left sm:grid-cols-2 lg:grid-cols-4">
                    <div class="min-w-0">
                        <p class="ui-label text-black/35">Order</p>
                        <p class="mt-2 break-all text-sm">{{ $order->order_number ?: '#'.$order->id }}</p>
                    </div>
                    <div>
                        <p class="ui-label text-black/35">Date</p>
                        <p class="mt-2 text-sm">{{ optional($order->placed_at ?? $order->created_at)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="ui-label text-black/35">Status</p>
                        <p class="mt-2 text-sm">{{ ucfirst(str_replace('_',' ', $order->status ?? 'pending')) }}</p>
                    </div>
                    <div>
                        <p class="ui-label text-black/35">Total</p>
                        <p class="mt-2 text-sm">{{ $order->currency ?: 'PKR' }} {{ number_format((float)($order->grand_total ?? 0)) }}</p>
                    </div>
                </button>

                <div x-cloak x-show="open" class="mt-6 min-w-0">
                    <div class="mb-4 grid gap-3 bg-white p-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <p class="ui-label text-black/35">Payment</p>
                            <p class="mt-1 text-sm">{{ $order->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Cash on Delivery' }}</p>
                        </div>
                        <div>
                            <p class="ui-label text-black/35">Payment status</p>
                            <p class="mt-1 text-sm">{{ ucfirst(str_replace('_',' ', $order->payment_status ?? 'pending')) }}</p>
                        </div>
                        @if($order->tracking_number)
                            <div class="min-w-0">
                                <p class="ui-label text-black/35">Tracking</p>
                                <p class="mt-1 break-all text-sm">{{ $order->tracking_number }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($order->items as $item)
                            <div class="min-w-0 bg-white p-5">
                                <p class="break-words text-sm font-medium">{{ $item->product_name }}</p>
                                <p class="mt-1 break-words ui-label text-black/35">{{ $item->sku ?: '—' }} · Qty {{ $item->quantity ?? 1 }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if($order->payment_rejection_reason)
                        <p class="mt-4 break-words border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            Payment verification note: {{ $order->payment_rejection_reason }}
                        </p>
                    @endif
                </div>

                <a href="{{ route('orders.show',$order) }}" class="mt-4 inline-block text-link">View order →</a>
            </article>
        @empty
            <div class="py-20 text-center">
                <h2 class="display-serif text-4xl">No orders yet.</h2>
                <a href="{{ route('shop') }}" class="btn-solid mt-6">Explore fragrances</a>
            </div>
        @endforelse

        @if(method_exists($orders,'links'))
            <div class="mt-10 overflow-x-auto">{{ $orders->links() }}</div>
        @endif
    </div>
</section>
@endsection

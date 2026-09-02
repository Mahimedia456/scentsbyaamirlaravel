@extends('layouts.store')

@section('title','My Account — Scents by Aamir')
@section('description','Manage your Scents by Aamir profile, delivery address and order history.')

@section('content')
@php
    $address = $customer->addresses->firstWhere('is_default', true) ?? $customer->addresses->first();
@endphp

<section class="min-h-screen overflow-x-hidden bg-[#f7f6f2] pt-[100px] text-black">
    <div class="border-b border-black/10 bg-white">
        <div class="house-container py-10 sm:py-14">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="ui-label text-black/35">Private Account</p>
                    <h1 class="mt-3 display-serif text-[50px] leading-[.94] sm:text-[64px]">Welcome, {{ $customer->first_name }}.</h1>
                    <p class="mt-4 text-sm text-black/45">{{ $customer->email }}</p>
                </div>

                <form method="POST" action="{{ route('customer.logout') }}">
                    @csrf
                    <button class="text-link">Sign out</button>
                </form>
            </div>
        </div>
    </div>

    <div class="house-container py-10 lg:py-14">
        @if(session('success'))
            <div class="mb-7 border border-black/10 bg-white p-4 text-sm">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-7 border border-red-200 bg-red-50 p-5 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach([
                ['Orders', $orderCount, route('orders')],
                ['In progress', $openOrderCount, route('orders')],
                ['Notifications', $unreadNotifications, route('notifications')],
            ] as [$label,$value,$href])
                <a href="{{ $href }}" class="group bg-black p-6 text-white transition hover:bg-[#1b1b1b]">
                    <p class="ui-label text-white/35">{{ $label }}</p>
                    <div class="mt-6 flex items-end justify-between">
                        <span class="display-serif text-[48px] leading-none">{{ $value }}</span>
                        <span class="transition-transform group-hover:translate-x-1">→</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[240px_1fr]">
            <aside class="h-fit bg-white p-5 lg:sticky lg:top-[120px]">
                <p class="mb-4 px-4 ui-label text-black/30">Account</p>
                <a class="block bg-black px-4 py-3 ui-label text-white" href="{{ route('account') }}">Overview</a>
                <a class="block px-4 py-3 ui-label hover:bg-black/5" href="{{ route('orders') }}">Orders</a>
                <a class="block px-4 py-3 ui-label hover:bg-black/5" href="{{ route('wishlist') }}">Wishlist</a>
                <a class="block px-4 py-3 ui-label hover:bg-black/5" href="{{ route('notifications') }}">Notifications</a>
                <a class="block px-4 py-3 ui-label hover:bg-black/5" href="{{ route('cart') }}">Shopping bag</a>
            </aside>

            <div class="grid gap-8">
                <section class="bg-white p-7 sm:p-9">
                    <div class="flex flex-wrap items-end justify-between gap-5 border-b border-black/10 pb-6">
                        <div>
                            <p class="ui-label text-black/35">Recent Orders</p>
                            <h2 class="mt-3 display-serif text-[40px] leading-none">Your latest purchases.</h2>
                        </div>
                        <a href="{{ route('orders') }}" class="text-link">View all →</a>
                    </div>

                    @forelse($recentOrders as $order)
                        <a href="{{ route('orders.show', $order) }}" class="grid gap-4 border-b border-black/10 py-5 sm:grid-cols-[1fr_auto_auto] sm:items-center">
                            <div>
                                <p class="text-sm font-medium">Order {{ $order->order_number ?? '#'.$order->id }}</p>
                                <p class="mt-1 text-xs text-black/42">{{ optional($order->placed_at ?? $order->created_at)->format('d M Y') }} · {{ $order->items->sum('quantity') }} item(s)</p>
                            </div>
                            <span class="ui-label text-black/40">{{ ucfirst(str_replace('_',' ', $order->status ?? 'processing')) }}</span>
                            <span class="text-sm">PKR {{ number_format((float)($order->grand_total ?? 0)) }} →</span>
                        </a>
                    @empty
                        <div class="py-10">
                            <p class="display-serif text-[34px]">No orders yet.</p>
                            <a href="{{ route('shop') }}" class="text-link mt-4 inline-block">Explore fragrances →</a>
                        </div>
                    @endforelse
                </section>

                <form method="POST" action="{{ route('account.update') }}" class="bg-white p-7 sm:p-9">
                    @csrf
                    @method('PUT')

                    <p class="ui-label text-black/35">Profile</p>
                    <h2 class="mt-3 display-serif text-[40px] leading-none">Personal details.</h2>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <label>
                            <span class="ui-label text-black/35">First name</span>
                            <input name="first_name" value="{{ old('first_name',$customer->first_name) }}" required class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label>
                            <span class="ui-label text-black/35">Last name</span>
                            <input name="last_name" value="{{ old('last_name',$customer->last_name) }}" class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label class="sm:col-span-2">
                            <span class="ui-label text-black/35">Email</span>
                            <input name="email" type="email" value="{{ old('email',$customer->email) }}" required class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label class="sm:col-span-2">
                            <span class="ui-label text-black/35">Phone</span>
                            <input name="phone" value="{{ old('phone',$customer->phone) }}" class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label class="sm:col-span-2 flex items-start gap-3 border-t border-black/10 pt-5 text-xs leading-5 text-black/50">
                            <input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in',$customer->marketing_opt_in)) class="mt-1">
                            <span>Receive occasional house news, launches and editorial updates.</span>
                        </label>
                    </div>

                    <button class="btn-solid mt-6">Save profile</button>
                </form>

                <form id="delivery-address" method="POST" action="{{ route('account.address') }}" class="scroll-mt-[130px] bg-white p-7 sm:p-9">
                    @csrf
                    <p class="ui-label text-black/35">Default Delivery Address</p>
                    <h2 class="mt-3 display-serif text-[40px] leading-none">Where we deliver.</h2>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-black/45">This address is used automatically during checkout and can be updated whenever needed.</p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <input type="hidden" name="country_code" value="PK">
                        <label>
                            <span class="ui-label text-black/35">First name</span>
                            <input name="first_name" value="{{ old('first_name',$address?->first_name ?? $customer->first_name) }}" required class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label>
                            <span class="ui-label text-black/35">Last name</span>
                            <input name="last_name" value="{{ old('last_name',$address?->last_name ?? $customer->last_name) }}" class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label class="sm:col-span-2">
                            <span class="ui-label text-black/35">Phone</span>
                            <input name="phone" value="{{ old('phone',$address?->phone ?? $customer->phone) }}" class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label class="sm:col-span-2">
                            <span class="ui-label text-black/35">Address</span>
                            <input name="address_line_1" value="{{ old('address_line_1',$address?->address_line_1) }}" required class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label class="sm:col-span-2">
                            <span class="ui-label text-black/35">Apartment / suite</span>
                            <input name="address_line_2" value="{{ old('address_line_2',$address?->address_line_2) }}" class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label>
                            <span class="ui-label text-black/35">City</span>
                            <input name="city" value="{{ old('city',$address?->city) }}" required class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label>
                            <span class="ui-label text-black/35">Province / region</span>
                            <input name="region" value="{{ old('region',$address?->region) }}" class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                        <label>
                            <span class="ui-label text-black/35">Postal code</span>
                            <input name="postal_code" value="{{ old('postal_code',$address?->postal_code) }}" class="mt-2 min-h-[52px] w-full border border-black/15 px-4">
                        </label>
                    </div>

                    <button class="btn-solid mt-6">Save delivery address</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

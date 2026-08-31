@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('content')
<div class="mb-8 flex flex-col gap-4 border-b border-black/10 pb-8 md:flex-row md:items-end md:justify-between">
    <div>
        <p class="text-[10px] uppercase tracking-[.2em] text-black/45">Overview</p>
        <h2 class="mt-3 max-w-2xl text-3xl font-medium tracking-tight md:text-4xl">Your commerce control room.</h2>
        <p class="mt-3 text-sm text-black/50">Core database and administration foundation is active. Catalog and order modules follow in the next phases.</p>
    </div>
    <div class="inline-flex items-center gap-2 self-start border border-black/10 bg-white px-4 py-2.5 text-xs">
        <span class="h-2 w-2 rounded-full {{ $databaseOnline ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
        Database {{ $databaseOnline ? 'connected' : 'offline' }}
    </div>
</div>

<div class="grid gap-px overflow-hidden border border-black/10 bg-black/10 sm:grid-cols-2 xl:grid-cols-4">
    @php($cards = [
        ['Products', number_format($stats['products']), 'Catalog records'],
        ['Orders', number_format($stats['orders']), 'All-time orders'],
        ['Customers', number_format($stats['customers']), 'Customer profiles'],
        ['Revenue', 'PKR '.number_format($stats['revenue']), 'Non-cancelled orders'],
    ])
    @foreach($cards as [$label,$value,$hint])
        <article class="bg-white p-6 xl:p-7">
            <p class="text-[10px] uppercase tracking-[.2em] text-black/45">{{ $label }}</p>
            <p class="mt-5 text-3xl font-medium tracking-tight">{{ $value }}</p>
            <p class="mt-2 text-xs text-black/40">{{ $hint }}</p>
        </article>
    @endforeach
</div>

<div class="mt-8 grid gap-8 xl:grid-cols-[1.45fr_.55fr]">
    <section class="border border-black/10 bg-white">
        <div class="flex items-center justify-between border-b border-black/10 px-6 py-5">
            <div><p class="text-[10px] uppercase tracking-[.2em] text-black/45">Commerce</p><h3 class="mt-1 text-base font-medium">Recent orders</h3></div>
            <span class="text-[10px] uppercase tracking-[.16em] text-black/35">Phase 01</span>
        </div>
        @if($recentOrders->isEmpty())
            <div class="px-6 py-14 text-center"><p class="text-sm">No orders yet.</p><p class="mt-2 text-xs text-black/45">Order management will be enabled in the commerce phase.</p></div>
        @else
            <div class="overflow-x-auto"><table class="w-full text-left text-xs"><thead class="border-b border-black/10 bg-[#faf9f6] text-[10px] uppercase tracking-[.14em] text-black/45"><tr><th class="px-6 py-3">Order</th><th class="px-6 py-3">Customer</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-right">Total</th></tr></thead><tbody>@foreach($recentOrders as $order)<tr class="border-b border-black/5"><td class="px-6 py-4 font-medium">{{ $order->order_number }}</td><td class="px-6 py-4">{{ $order->customer_name ?: 'Guest' }}</td><td class="px-6 py-4 capitalize">{{ $order->status }}</td><td class="px-6 py-4 text-right">{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
    <section class="bg-black p-7 text-white">
        <p class="text-[10px] uppercase tracking-[.2em] text-white/45">System status</p>
        <h3 class="mt-3 text-xl font-medium">Foundation ready</h3>
        <div class="mt-8 space-y-5 text-xs">
            @foreach([['Admin authentication', true],['Database migrations', true],['API health endpoint', true],['Product CRUD', false],['Order management', false]] as [$name,$ready])
                <div class="flex items-center justify-between border-b border-white/10 pb-4"><span class="text-white/65">{{ $name }}</span><span class="text-[9px] uppercase tracking-[.14em] {{ $ready ? 'text-emerald-300' : 'text-white/30' }}">{{ $ready ? 'Ready' : 'Next' }}</span></div>
            @endforeach
        </div>
        <div class="mt-8 border border-white/15 p-4"><p class="text-[10px] uppercase tracking-[.16em] text-white/40">API check</p><code class="mt-2 block text-xs text-white/70">GET /api/v1/health</code></div>
    </section>
</div>
@endsection

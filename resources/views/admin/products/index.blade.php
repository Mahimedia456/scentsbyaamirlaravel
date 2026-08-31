@extends('admin.layouts.app')
@section('title','Products')
@section('content')
<div class="space-y-6">
  <div class="flex items-end justify-between gap-4"><div><p class="text-xs uppercase tracking-[.28em] text-neutral-500">Catalog</p><h1 class="mt-2 text-3xl font-semibold">Products</h1></div><a href="{{ route('admin.products.create') }}" class="rounded-full bg-black px-5 py-3 text-sm font-medium text-white">New product</a></div>
  @if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
  <form class="grid gap-3 rounded-3xl border border-neutral-200 bg-white p-4 md:grid-cols-[1fr_220px_220px_auto]">
    <input name="q" value="{{ request('q') }}" placeholder="Search product or SKU" class="rounded-2xl border-neutral-200">
    <select name="status" class="rounded-2xl border-neutral-200"><option value="">All statuses</option>@foreach(['draft','active','archived'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
    <select name="category_id" class="rounded-2xl border-neutral-200"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category_id')===(string)$category->id)>{{ $category->name }}</option>@endforeach</select>
    <button class="rounded-2xl border border-neutral-300 px-5">Filter</button>
  </form>
  <div class="overflow-hidden rounded-3xl border border-neutral-200 bg-white">
    <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-neutral-50 text-left text-xs uppercase tracking-wider text-neutral-500"><tr><th class="px-5 py-4">Product</th><th class="px-5 py-4">Category</th><th class="px-5 py-4">Price</th><th class="px-5 py-4">Stock</th><th class="px-5 py-4">Variants</th><th class="px-5 py-4">Status</th><th class="px-5 py-4"></th></tr></thead><tbody class="divide-y divide-neutral-100">@forelse($products as $product)<tr><td class="px-5 py-4"><div class="font-medium">{{ $product->name }}</div><div class="text-xs text-neutral-500">{{ $product->sku ?: 'No SKU' }}</div></td><td class="px-5 py-4">{{ $product->category?->name ?: '—' }}</td><td class="px-5 py-4">PKR {{ number_format((float)$product->base_price) }}</td><td class="px-5 py-4">{{ $product->stock }}</td><td class="px-5 py-4">{{ $product->variants_count }}</td><td class="px-5 py-4"><span class="rounded-full bg-neutral-100 px-3 py-1 text-xs">{{ ucfirst($product->status) }}</span></td><td class="px-5 py-4 text-right"><a class="font-medium underline" href="{{ route('admin.products.edit',$product) }}">Edit</a></td></tr>@empty<tr><td colspan="7" class="px-5 py-12 text-center text-neutral-500">No products found.</td></tr>@endforelse</tbody></table></div>
    <div class="border-t border-neutral-100 p-4">{{ $products->links() }}</div>
  </div>
</div>
@endsection

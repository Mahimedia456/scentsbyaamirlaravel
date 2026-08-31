@extends('admin.layouts.app')
@section('title','Products')
@section('header','Products')
@section('eyebrow','Catalog management')

@section('content')
<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px">
    <div>
        <h2 style="margin:0;font-size:27px;letter-spacing:-.035em">Product catalog</h2>
        <p class="admin-muted" style="margin:7px 0 0;font-size:12px">Manage merchandising, pricing, availability, images, publishing and SEO.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary">+ Create product</a>
</div>

<div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:14px">
    @foreach([
        ['All',$summary['all']],
        ['Active',$summary['active']],
        ['Draft',$summary['draft']],
        ['Archived',$summary['archived']],
        ['Featured',$summary['featured']],
    ] as [$label,$value])
        <div class="admin-card" style="padding:15px">
            <div class="admin-eyebrow">{{ $label }}</div>
            <div style="margin-top:7px;font-size:21px;font-weight:720">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<section class="admin-card">
    <form method="GET" action="{{ route('admin.products.index') }}" style="display:grid;grid-template-columns:minmax(220px,1.5fr) repeat(4,minmax(130px,.55fr)) auto;gap:9px;padding:14px;border-bottom:1px solid #e4e7ec">
        <input class="admin-field" type="search" name="q" value="{{ request('q') }}" placeholder="Search name, SKU or slug">
        <select class="admin-field" name="status">
            <option value="">All status</option>
            @foreach(['active','draft','archived'] as $status)
                <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select class="admin-field" name="category_id">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string)request('category_id')===(string)$category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select class="admin-field" name="availability">
            <option value="">All availability</option>
            <option value="in_stock" @selected(request('availability')==='in_stock')>In stock</option>
            <option value="out_of_stock" @selected(request('availability')==='out_of_stock')>Out of stock</option>
        </select>
        <select class="admin-field" name="featured">
            <option value="">Featured + standard</option>
            <option value="yes" @selected(request('featured')==='yes')>Featured only</option>
            <option value="no" @selected(request('featured')==='no')>Not featured</option>
        </select>
        <button class="admin-btn">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.products.bulk') }}" data-product-bulk>
        @csrf
        <div style="display:flex;align-items:center;gap:9px;flex-wrap:wrap;padding:12px 14px;border-bottom:1px solid #e4e7ec;background:#fafbfc">
            <select class="admin-field" name="action" style="width:220px" required>
                <option value="">Bulk action…</option>
                <option value="activate">Publish / activate</option>
                <option value="draft">Move to draft</option>
                <option value="archive">Archive</option>
                <option value="feature">Mark featured</option>
                <option value="unfeature">Remove featured</option>
                <option value="mark_in_stock">Mark in stock</option>
                <option value="mark_out_of_stock">Mark out of stock</option>
                <option value="delete">Delete permanently</option>
            </select>
            <button class="admin-btn" data-admin-confirm="Apply this bulk action to all selected products?">Apply</button>
            <span class="admin-muted" style="font-size:10px">Select products below. Delete is permanent.</span>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th style="width:34px"><input type="checkbox" data-product-select-all></th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Availability</th>
                    <th>Media</th>
                    <th style="text-align:right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    @php
                        $available = $product->track_inventory
                            ? (int)$product->stock > 0
                            : (bool)$product->is_in_stock;
                        $primary = $product->images->firstWhere('is_primary',true) ?? $product->images->first();
                    @endphp
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $product->id }}" data-product-select></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:11px;min-width:250px">
                                <div style="width:44px;height:52px;border:1px solid #e4e7ec;border-radius:8px;background:#f7f8fa;overflow:hidden;display:grid;place-items:center">
                                    @if($primary)
                                        <img src="{{ route('store.media',['path'=>$primary->path]) }}" alt="" style="width:100%;height:100%;object-fit:cover">
                                    @else
                                        <span style="font-size:9px;color:#9097a1">NO IMG</span>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('admin.products.edit',$product) }}" style="font-size:12px;font-weight:700">{{ $product->name }}</a>
                                    <div class="admin-muted" style="margin-top:4px;font-size:9px">{{ $product->sku ?: 'No SKU' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status {{ $product->status==='active' ? 'success' : ($product->status==='archived' ? '' : 'warning') }}">{{ ucfirst($product->status) }}</span></td>
                        <td style="font-weight:650">PKR {{ number_format((float)$product->base_price) }}</td>
                        <td>{{ $product->size_label ?: '—' }}</td>
                        <td>
                            <span class="admin-status {{ $available ? 'success' : 'warning' }}">{{ $available ? 'In stock' : 'Out of stock' }}</span>
                            @if($product->track_inventory)<div class="admin-muted" style="margin-top:4px;font-size:9px">{{ (int)$product->stock }} units</div>@endif
                        </td>
                        <td>{{ $product->images_count }} image{{ $product->images_count===1?'':'s' }}</td>
                        <td style="text-align:right">
                            <div style="display:flex;justify-content:flex-end;gap:6px">
                                <a href="{{ route('admin.products.edit',$product) }}" class="admin-btn">Edit</a>
                                <button form="duplicate-{{ $product->id }}" class="admin-btn">Duplicate</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="padding:48px;text-align:center" class="admin-muted">No products match the current filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>
</section>

@foreach($products as $product)
<form id="duplicate-{{ $product->id }}" method="POST" action="{{ route('admin.products.duplicate',$product) }}" hidden>@csrf</form>
@endforeach

@if($products->hasPages())<div style="margin-top:18px">{{ $products->links() }}</div>@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    const all = document.querySelector('[data-product-select-all]');
    const boxes = [...document.querySelectorAll('[data-product-select]')];
    all?.addEventListener('change', () => boxes.forEach(box => box.checked = all.checked));
});
</script>
<style>@media(max-width:1100px){.admin-page>div:nth-child(2){grid-template-columns:repeat(2,minmax(0,1fr))!important}.admin-card>form:first-child{grid-template-columns:1fr 1fr!important}} @media(max-width:650px){.admin-page>div:nth-child(2){grid-template-columns:1fr!important}.admin-card>form:first-child{grid-template-columns:1fr!important}}</style>
@endsection

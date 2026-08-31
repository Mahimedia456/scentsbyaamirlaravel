@extends('admin.layouts.app')
@section('title','Inventory')
@section('header','Inventory')
@section('eyebrow','Operations / stock control')
@section('content')
<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px">
<div><h2 style="margin:0;font-size:27px;letter-spacing:-.035em">Inventory command center</h2><p class="admin-muted" style="margin:7px 0 0;font-size:12px">Availability, tracked quantity, movement history and controlled stock adjustments.</p></div>
<a class="admin-btn" href="{{ route('admin.inventory.export',request()->only('reason')) }}">Export movement CSV</a>
</div>
<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px">
@foreach([['Tracked',$summary['tracked']],['Simple availability',$summary['untracked']],['Low stock',$summary['low']],['Out of stock',$summary['out']]] as [$l,$v])<div class="admin-card" style="padding:15px"><div class="admin-eyebrow">{{ $l }}</div><div style="font-size:21px;font-weight:720;margin-top:7px">{{ number_format($v) }}</div></div>@endforeach
</div>
<div class="admin-section-grid">
<section class="admin-card">
<form method="GET" style="display:grid;grid-template-columns:minmax(200px,1fr) 160px 160px auto;gap:8px;padding:14px;border-bottom:1px solid #e4e7ec">
<input class="admin-field" name="q" value="{{ request('q') }}" placeholder="Search product or SKU">
<select class="admin-field" name="mode"><option value="">All inventory modes</option><option value="tracked" @selected(request('mode')==='tracked')>Tracked quantity</option><option value="simple" @selected(request('mode')==='simple')>Simple availability</option></select>
<select class="admin-field" name="state"><option value="">All stock states</option><option value="in" @selected(request('state')==='in')>In stock</option><option value="low" @selected(request('state')==='low')>Low stock</option><option value="out" @selected(request('state')==='out')>Out of stock</option></select>
<button class="admin-btn">Filter</button>
</form>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Product</th><th>Mode</th><th>Available</th><th style="text-align:right">Quantity</th><th style="text-align:right">Action</th></tr></thead><tbody>
@forelse($products as $product)
@php($available=$product->track_inventory ? (int)$product->stock>0 : (bool)$product->is_in_stock)
<tr><td><a href="{{ route('admin.products.edit',$product) }}" style="font-weight:700">{{ $product->name }}</a><div class="admin-muted" style="font-size:9px;margin-top:4px">{{ $product->sku ?: 'No SKU' }} · {{ $product->size_label ?: '—' }}</div></td>
<td>{{ $product->track_inventory?'Tracked':'Simple' }}</td><td><span class="admin-status {{ $available?'success':'warning' }}">{{ $available?'In stock':'Out of stock' }}</span></td>
<td style="text-align:right;font-weight:700">{{ $product->track_inventory?(int)$product->stock:'—' }}</td>
<td style="text-align:right">@if(!$product->track_inventory)<form method="POST" action="{{ route('admin.inventory.availability',$product) }}">@csrf<input type="hidden" name="is_in_stock" value="{{ $available?0:1 }}"><button class="admin-btn">{{ $available?'Mark out':'Mark in' }}</button></form>@else<a class="admin-btn" href="#stock-adjustment">Adjust</a>@endif</td></tr>
@endforelse
</tbody></table></div>
<div style="padding:14px">{{ $products->links() }}</div>
</section>
<aside style="display:grid;gap:14px">
<form id="stock-adjustment" method="POST" action="{{ route('admin.inventory.adjust') }}" class="admin-card" style="padding:20px">@csrf<div class="admin-eyebrow">Manual movement</div><h3 style="font-size:16px;margin:7px 0 17px">Stock adjustment</h3>
<div style="display:grid;gap:10px"><select required name="product_id" class="admin-field"><option value="">Select product</option>@foreach($adjustableProducts as $p)<option value="{{ $p->id }}">{{ $p->name }}{{ $p->sku?' · '.$p->sku:'' }}</option>@endforeach</select><input class="admin-field" name="product_variant_id" type="number" placeholder="Variant ID (optional)"><input class="admin-field" required name="quantity_change" type="number" placeholder="+10 received or -2 damaged"><select class="admin-field" name="reason">@foreach(['manual','received','damage','correction','return','reserved','cycle_count'] as $r)<option value="{{ $r }}">{{ ucfirst(str_replace('_',' ',$r)) }}</option>@endforeach</select><input class="admin-field" name="reference" placeholder="Reference / PO / ticket"><textarea class="admin-field" style="padding-top:10px;min-height:80px" name="note" placeholder="Internal note"></textarea><button class="admin-btn admin-btn-primary">Apply adjustment</button></div></form>
<section class="admin-card"><div class="admin-card-header"><div><div class="admin-eyebrow">Movement ledger</div><div style="font-size:14px;font-weight:700;margin-top:4px">Recent changes</div></div></div><div style="max-height:620px;overflow:auto">
@forelse($adjustments as $a)<div style="padding:14px 18px;border-bottom:1px solid #edf0f3"><div style="display:flex;justify-content:space-between;gap:12px"><div style="font-size:11px;font-weight:700">{{ optional($a->product)->name ?: 'Deleted product' }}</div><strong style="font-size:12px;color:{{ $a->quantity_change<0?'#b42318':'#16794b' }}">{{ $a->quantity_change>0?'+':'' }}{{ $a->quantity_change }}</strong></div><div class="admin-muted" style="font-size:9px;margin-top:4px">{{ str_replace('_',' ',$a->reason) }} · after {{ $a->quantity_after }} · {{ optional($a->created_at)->format('d M H:i') }}</div>@if($a->reference)<div class="admin-muted" style="font-size:9px;margin-top:3px">Ref: {{ $a->reference }}</div>@endif</div>@empty<div class="admin-muted" style="padding:35px;text-align:center;font-size:11px">No stock movements yet.</div>@endforelse
</div></section>
</aside></div>
<style>@media(max-width:850px){.admin-page>div:nth-child(2){grid-template-columns:repeat(2,minmax(0,1fr))!important}}@media(max-width:600px){.admin-page>div:nth-child(2){grid-template-columns:1fr!important}.admin-card>form:first-child{grid-template-columns:1fr!important}}</style>
@endsection

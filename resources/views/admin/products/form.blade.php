@extends('admin.layouts.app')
@section('title',$product->exists ? 'Edit Product' : 'Create Product')
@section('header',$product->exists ? 'Edit Product' : 'Create Product')
@section('eyebrow','Catalog / products')

@section('content')
@php
    $editing = $product->exists;
    $oldVariants = old('variants', $editing ? $product->variants->toArray() : []);
    $selectedCollections = collect(old('collections', $editing ? $product->collections->pluck('id')->all() : []))->map(fn($id)=>(int)$id)->all();
@endphp

<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.products.update',$product) : route('admin.products.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:18px">
        <div>
            <a href="{{ route('admin.products.index') }}" class="admin-muted" style="font-size:10px">← Back to products</a>
            <h2 style="margin:7px 0 0;font-size:27px;letter-spacing:-.035em">{{ $editing ? $product->name : 'New product' }}</h2>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @if($editing && $product->status==='active')
                <a href="{{ route('product.show',$product->slug) }}" target="_blank" class="admin-btn">View live ↗</a>
            @endif
            <button class="admin-btn admin-btn-primary">{{ $editing ? 'Save changes' : 'Create product' }}</button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,1.35fr) minmax(300px,.65fr);gap:14px;align-items:start">
        <div style="display:grid;gap:14px">
            <section class="admin-card">
                <div class="admin-card-header"><div><div class="admin-eyebrow">Core details</div><div style="margin-top:4px;font-size:14px;font-weight:700">Identity & merchandising</div></div></div>
                <div style="padding:20px;display:grid;gap:15px">
                    <label style="font-size:11px;font-weight:680">Product name
                        <input class="admin-field" style="margin-top:7px" name="name" required value="{{ old('name',$product->name) }}">
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <label style="font-size:11px;font-weight:680">Slug
                            <input class="admin-field" style="margin-top:7px" name="slug" value="{{ old('slug',$product->slug) }}" placeholder="Auto-generated when empty">
                        </label>
                        <label style="font-size:11px;font-weight:680">SKU
                            <input class="admin-field" style="margin-top:7px" name="sku" value="{{ old('sku',$product->sku) }}">
                        </label>
                    </div>
                    <label style="font-size:11px;font-weight:680">Subtitle
                        <input class="admin-field" style="margin-top:7px" name="subtitle" value="{{ old('subtitle',$product->subtitle) }}">
                    </label>
                    <label style="font-size:11px;font-weight:680">Description
                        <textarea class="admin-field" style="margin-top:7px;padding-top:11px;min-height:140px" name="description">{{ old('description',$product->description) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header"><div><div class="admin-eyebrow">Fragrance content</div><div style="margin-top:4px;font-size:14px;font-weight:700">Story, notes & wear</div></div></div>
                <div style="padding:20px;display:grid;gap:15px">
                    @foreach([['story','Story'],['notes','Notes'],['wear','Wear']] as [$field,$label])
                        <label style="font-size:11px;font-weight:680">{{ $label }}
                            <textarea class="admin-field" style="margin-top:7px;padding-top:11px;min-height:110px" name="{{ $field }}">{{ old($field,$product->{$field}) }}</textarea>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header">
                    <div><div class="admin-eyebrow">Media gallery</div><div style="margin-top:4px;font-size:14px;font-weight:700">Product imagery</div></div>
                    <span class="admin-muted" style="font-size:10px">Existing images are preserved unless removed</span>
                </div>
                <div style="padding:20px">
                    @if($editing && $product->images->isNotEmpty())
                        <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:18px">
                            @foreach($product->images as $image)
                                <label style="position:relative;border:1px solid #e4e7ec;border-radius:11px;overflow:hidden;background:#f8f9fa">
                                    <img src="{{ route('store.media',['path'=>$image->path]) }}" alt="" style="width:100%;aspect-ratio:4/5;object-fit:cover;display:block">
                                    <div style="padding:9px;font-size:9px">
                                        <label style="display:flex;gap:6px;align-items:center"><input type="radio" name="primary_image_id" value="{{ $image->id }}" @checked($image->is_primary)> Primary</label>
                                        <label style="display:flex;gap:6px;align-items:center;margin-top:7px;color:#9d2018"><input type="checkbox" name="removed_images[]" value="{{ $image->id }}"> Remove</label>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <label style="display:block;padding:22px;border:1px dashed #cfd4dc;border-radius:11px;background:#fafbfc;text-align:center;font-size:11px;font-weight:680">
                        Add product images
                        <input type="file" name="image_uploads[]" multiple accept=".jpg,.jpeg,.png,.webp" style="display:block;margin:12px auto 0;font-size:10px">
                        <span class="admin-muted" style="display:block;margin-top:7px;font-size:9px;font-weight:400">JPG, PNG or WEBP · max 8 MB each · up to 12 files</span>
                    </label>
                </div>
            </section>

            <section class="admin-card">
                <details @if(count($oldVariants)) open @endif>
                    <summary style="cursor:pointer;padding:20px;font-size:13px;font-weight:700">Advanced variants <span class="admin-muted" style="font-size:10px;font-weight:400">— leave empty for simple products</span></summary>
                    <div style="padding:0 20px 20px;display:grid;gap:10px">
                        @forelse($oldVariants as $i=>$v)
                            <div style="display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:8px;padding:12px;border:1px solid #e7e9ed;border-radius:10px">
                                <input class="admin-field" name="variants[{{ $i }}][name]" value="{{ $v['name'] ?? '' }}" placeholder="Name">
                                <input class="admin-field" name="variants[{{ $i }}][size_label]" value="{{ $v['size_label'] ?? '' }}" placeholder="Size">
                                <input class="admin-field" name="variants[{{ $i }}][sku]" value="{{ $v['sku'] ?? '' }}" placeholder="SKU">
                                <input class="admin-field" name="variants[{{ $i }}][price]" value="{{ $v['price'] ?? '' }}" placeholder="Price">
                                <input class="admin-field" name="variants[{{ $i }}][stock]" value="{{ $v['stock'] ?? 0 }}" placeholder="Stock">
                                <label style="display:flex;align-items:center;gap:6px;font-size:10px"><input type="checkbox" name="variants[{{ $i }}][is_active]" value="1" @checked($v['is_active'] ?? true)> Active</label>
                            </div>
                        @empty
                            <p class="admin-muted" style="margin:0;font-size:11px">This product currently uses simple-product inventory and does not need a variant.</p>
                        @endforelse
                    </div>
                </details>
            </section>
        </div>

        <aside style="display:grid;gap:14px;position:sticky;top:96px">
            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Publishing</div>
                <div style="display:grid;gap:13px;margin-top:15px">
                    <label style="font-size:11px;font-weight:680">Status
                        <select class="admin-field" style="margin-top:7px" name="status">
                            @foreach(['draft','active','archived'] as $status)
                                <option value="{{ $status }}" @selected(old('status',$product->status ?: 'draft')===$status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label style="display:flex;gap:8px;align-items:center;font-size:11px"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$product->is_featured))> Featured product</label>
                    <label style="font-size:11px;font-weight:680">Category
                        <select class="admin-field" style="margin-top:7px" name="category_id">
                            <option value="">No category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string)old('category_id',$product->category_id)===(string)$category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Commerce</div>
                <div style="display:grid;gap:13px;margin-top:15px">
                    <label style="font-size:11px;font-weight:680">Price
                        <input class="admin-field" style="margin-top:7px" name="base_price" type="number" step="0.01" min="0" value="{{ old('base_price',$product->base_price) }}">
                    </label>
                    <label style="font-size:11px;font-weight:680">Compare at price
                        <input class="admin-field" style="margin-top:7px" name="compare_at_price" type="number" step="0.01" min="0" value="{{ old('compare_at_price',$product->compare_at_price) }}">
                    </label>
                    <label style="font-size:11px;font-weight:680">Display size
                        <input class="admin-field" style="margin-top:7px" name="size_label" value="{{ old('size_label',$product->size_label ?: '50 ML') }}">
                    </label>
                    <label style="display:flex;gap:8px;align-items:flex-start;font-size:11px"><input type="checkbox" name="track_inventory" value="1" @checked(old('track_inventory',$product->track_inventory))><span><strong>Track numeric inventory</strong><br><span class="admin-muted" style="font-size:9px">Orders decrement stock automatically.</span></span></label>
                    <label style="font-size:11px;font-weight:680">Stock quantity
                        <input class="admin-field" style="margin-top:7px" name="stock" type="number" min="0" value="{{ old('stock',$product->stock ?? 0) }}">
                    </label>
                    <label style="display:flex;gap:8px;align-items:flex-start;font-size:11px"><input type="checkbox" name="is_in_stock" value="1" @checked(old('is_in_stock',$product->is_in_stock))><span><strong>Available / in stock</strong><br><span class="admin-muted" style="font-size:9px">Used for untracked simple products.</span></span></label>
                </div>
            </section>

            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Collections</div>
                <div style="display:grid;gap:8px;margin-top:14px;max-height:220px;overflow:auto">
                    @foreach($collections as $collection)
                        <label style="display:flex;gap:8px;align-items:center;font-size:11px"><input type="checkbox" name="collections[]" value="{{ $collection->id }}" @checked(in_array($collection->id,$selectedCollections,true))> {{ $collection->name }}</label>
                    @endforeach
                </div>
            </section>

            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Search appearance</div>
                <div style="display:grid;gap:13px;margin-top:15px">
                    <label style="font-size:11px;font-weight:680">Meta title
                        <input class="admin-field" style="margin-top:7px" name="meta_title" value="{{ old('meta_title',$product->meta_title) }}">
                    </label>
                    <label style="font-size:11px;font-weight:680">Meta description
                        <textarea class="admin-field" style="margin-top:7px;padding-top:10px;min-height:90px" name="meta_description">{{ old('meta_description',$product->meta_description) }}</textarea>
                    </label>
                </div>
            </section>

            @if($editing)
                <section class="admin-card" style="padding:20px;border-color:#efc4c0">
                    <div class="admin-eyebrow" style="color:#9d2018">Danger zone</div>
                    <p class="admin-muted" style="font-size:10px;line-height:1.55">Deleting a product removes its Laravel-managed gallery files and catalog record.</p>
                    <button form="delete-product" type="submit" class="admin-btn" style="width:100%;color:#9d2018" data-admin-confirm="Permanently delete this product?">Delete product</button>
                </section>
            @endif
        </aside>
    </div>
</form>

@if($editing)
<form id="delete-product" method="POST" action="{{ route('admin.products.destroy',$product) }}" hidden>@csrf @method('DELETE')</form>
@endif

<style>
@media(max-width:1000px){form>div:nth-child(2){grid-template-columns:1fr!important} form aside{position:static!important}}
@media(max-width:760px){.admin-card div[style*="grid-template-columns:repeat(4"]{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
</style>
@endsection

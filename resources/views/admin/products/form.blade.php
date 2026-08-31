@extends('admin.layouts.app')
@section('title',$product->exists ? 'Edit Product' : 'New Product')
@section('content')
@php $editing=$product->exists; $oldVariants=old('variants',$editing?$product->variants->toArray():[['name'=>'50 ml','size_label'=>'50 ml','sku'=>'','price'=>'','stock'=>0,'is_active'=>1]]); $oldImages=old('images',$editing?$product->images->toArray():[['path'=>'','alt_text'=>'','is_primary'=>1]]); @endphp
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.products.update',$product) : route('admin.products.store') }}" class="space-y-6">@csrf @if($editing)@method('PUT')@endif
<div class="flex items-end justify-between gap-4"><div><p class="text-xs uppercase tracking-[.28em] text-neutral-500">Catalog</p><h1 class="mt-2 text-3xl font-semibold">{{ $editing ? 'Edit product' : 'Create product' }}</h1></div><button class="rounded-full bg-black px-6 py-3 text-sm font-medium text-white">Save product</button></div>
@if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
<div class="grid gap-6 xl:grid-cols-[1fr_340px]">
<div class="space-y-6">
<section class="rounded-3xl border border-neutral-200 bg-white p-6"><h2 class="text-lg font-semibold">Product identity</h2><div class="mt-5 grid gap-4 md:grid-cols-2"><label class="md:col-span-2">Name<input name="name" value="{{ old('name',$product->name) }}" class="mt-2 w-full rounded-2xl border-neutral-200"></label><label>Slug<input name="slug" value="{{ old('slug',$product->slug) }}" class="mt-2 w-full rounded-2xl border-neutral-200"></label><label>SKU<input name="sku" value="{{ old('sku',$product->sku) }}" class="mt-2 w-full rounded-2xl border-neutral-200"></label><label class="md:col-span-2">Subtitle<input name="subtitle" value="{{ old('subtitle',$product->subtitle) }}" class="mt-2 w-full rounded-2xl border-neutral-200"></label><label class="md:col-span-2">Description<textarea name="description" rows="5" class="mt-2 w-full rounded-2xl border-neutral-200">{{ old('description',$product->description) }}</textarea></label></div></section>
<section class="rounded-3xl border border-neutral-200 bg-white p-6"><h2 class="text-lg font-semibold">Fragrance editorial</h2><div class="mt-5 grid gap-4"><label>Story<textarea name="story" rows="4" class="mt-2 w-full rounded-2xl border-neutral-200">{{ old('story',$product->story) }}</textarea></label><label>Notes<textarea name="notes" rows="4" class="mt-2 w-full rounded-2xl border-neutral-200">{{ old('notes',$product->notes) }}</textarea></label><label>Wear<textarea name="wear" rows="4" class="mt-2 w-full rounded-2xl border-neutral-200">{{ old('wear',$product->wear) }}</textarea></label></div></section>
<section class="rounded-3xl border border-neutral-200 bg-white p-6"><div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Variants</h2><span class="text-xs text-neutral-500">Size, SKU, price and stock</span></div><div class="mt-5 space-y-3">@foreach($oldVariants as $i=>$v)<div class="grid gap-3 rounded-2xl bg-neutral-50 p-4 md:grid-cols-6"><input name="variants[{{ $i }}][name]" value="{{ $v['name'] ?? '' }}" placeholder="Name" class="rounded-xl border-neutral-200"><input name="variants[{{ $i }}][size_label]" value="{{ $v['size_label'] ?? '' }}" placeholder="50 ml" class="rounded-xl border-neutral-200"><input name="variants[{{ $i }}][sku]" value="{{ $v['sku'] ?? '' }}" placeholder="SKU" class="rounded-xl border-neutral-200"><input name="variants[{{ $i }}][price]" value="{{ $v['price'] ?? '' }}" placeholder="Price" class="rounded-xl border-neutral-200"><input name="variants[{{ $i }}][stock]" value="{{ $v['stock'] ?? 0 }}" placeholder="Stock" class="rounded-xl border-neutral-200"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="variants[{{ $i }}][is_active]" value="1" @checked($v['is_active'] ?? true)> Active</label></div>@endforeach</div></section>
<section class="rounded-3xl border border-neutral-200 bg-white p-6">
<div class="flex flex-wrap items-start justify-between gap-4">
    <div><h2 class="text-lg font-semibold">Product images</h2><p class="mt-1 max-w-2xl text-sm leading-6 text-neutral-500">Upload JPG, PNG or WebP images directly. Up to 12 files, 8 MB each. The first uploaded image becomes primary when no primary image is already selected.</p></div>
    <span class="rounded-full bg-neutral-100 px-3 py-1 text-[10px] font-medium uppercase tracking-[.18em] text-neutral-500">Direct upload</span>
</div>
<label class="mt-5 flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-3xl border border-dashed border-neutral-300 bg-neutral-50 px-6 text-center transition hover:border-black hover:bg-white">
    <span class="text-3xl font-light">＋</span>
    <span class="mt-3 text-sm font-medium">Choose product images</span>
    <span class="mt-1 text-xs text-neutral-500">You can select multiple images at once</span>
    <input id="product-image-upload" type="file" name="image_uploads[]" accept="image/jpeg,image/png,image/webp" multiple class="sr-only">
</label>
<div id="product-image-preview" class="mt-4 hidden grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"></div>

@if($editing && $product->images->isNotEmpty())
<div class="mt-7">
    <div class="mb-3 flex items-center justify-between"><p class="text-sm font-medium">Current images</p><p class="text-xs text-neutral-400">Keep path to retain image</p></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach($oldImages as $i=>$img)
        @php
            $imagePath = $img['path'] ?? '';
            $previewUrl = null;
            if ($imagePath) {
                if (\Illuminate\Support\Str::startsWith($imagePath, ['http://','https://'])) $previewUrl = $imagePath;
                elseif (\Illuminate\Support\Str::startsWith($imagePath, ['/storage/','storage/'])) $previewUrl = route('store.media',['path'=>preg_replace('#^/?storage/#','',$imagePath)]);
                elseif (\Illuminate\Support\Str::startsWith($imagePath, '/')) $previewUrl = asset(ltrim($imagePath,'/'));
                else $previewUrl = route('store.media',['path'=>ltrim($imagePath,'/')]);
            }
        @endphp
        <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-50">
            <div class="aspect-square bg-white">@if($previewUrl)<img src="{{ $previewUrl }}" alt="" class="h-full w-full object-contain p-3">@endif</div>
            <div class="space-y-3 p-3">
                <input name="images[{{ $i }}][path]" value="{{ $imagePath }}" class="w-full rounded-xl border-neutral-200 text-xs" placeholder="Image path">
                <input name="images[{{ $i }}][alt_text]" value="{{ $img['alt_text'] ?? '' }}" class="w-full rounded-xl border-neutral-200 text-xs" placeholder="Alt text">
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="images[{{ $i }}][is_primary]" value="1" @checked($img['is_primary'] ?? false)> Primary image</label>
            </div>
        </div>
    @endforeach
    </div>
</div>
@else
    @foreach($oldImages as $i=>$img)
        @if(!empty($img['path']))
            <input type="hidden" name="images[{{ $i }}][path]" value="{{ $img['path'] }}">
            <input type="hidden" name="images[{{ $i }}][alt_text]" value="{{ $img['alt_text'] ?? '' }}">
            @if($img['is_primary'] ?? false)<input type="hidden" name="images[{{ $i }}][is_primary]" value="1">@endif
        @endif
    @endforeach
@endif

<details class="mt-6 border-t border-neutral-200 pt-5">
    <summary class="cursor-pointer text-xs font-medium uppercase tracking-[.16em] text-neutral-500">Advanced: external image URL/path</summary>
    <div class="mt-4 grid gap-3 rounded-2xl bg-neutral-50 p-4 md:grid-cols-[1fr_1fr_auto]">
        <input name="images[99][path]" value="{{ old('images.99.path') }}" placeholder="https://... or storage path" class="rounded-xl border-neutral-200">
        <input name="images[99][alt_text]" value="{{ old('images.99.alt_text') }}" placeholder="Alt text" class="rounded-xl border-neutral-200">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="images[99][is_primary]" value="1"> Primary</label>
    </div>
</details>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('product-image-upload');
    const preview = document.getElementById('product-image-preview');
    if (!input || !preview) return;
    input.addEventListener('change', () => {
        preview.innerHTML = '';
        const files = Array.from(input.files || []);
        preview.classList.toggle('hidden', files.length === 0);
        preview.classList.toggle('grid', files.length > 0);
        files.forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'overflow-hidden rounded-2xl border border-neutral-200 bg-white';
            const img = document.createElement('img');
            img.className = 'aspect-square w-full object-contain p-3';
            img.src = URL.createObjectURL(file);
            img.onload = () => URL.revokeObjectURL(img.src);
            const meta = document.createElement('div');
            meta.className = 'border-t border-neutral-100 px-3 py-2 text-[11px] text-neutral-500';
            meta.textContent = (index === 0 ? 'Primary candidate · ' : '') + file.name;
            card.append(img, meta);
            preview.appendChild(card);
        });
    });
});
</script>
</section>
</div>
<aside class="space-y-6"><section class="rounded-3xl border border-neutral-200 bg-white p-6"><h2 class="font-semibold">Publishing</h2><div class="mt-4 space-y-4"><label>Status<select name="status" class="mt-2 w-full rounded-2xl border-neutral-200">@foreach(['draft','active','archived'] as $s)<option value="{{ $s }}" @selected(old('status',$product->status ?: 'draft')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></label><label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$product->is_featured))> Featured product</label></div></section>
<section class="rounded-3xl border border-neutral-200 bg-white p-6"><h2 class="font-semibold">Classification</h2><label class="mt-4 block">Category<select name="category_id" class="mt-2 w-full rounded-2xl border-neutral-200"><option value="">No category</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected((string)old('category_id',$product->category_id)===(string)$c->id)>{{ $c->name }}</option>@endforeach</select></label><div class="mt-4"><div class="mb-2 text-sm">Collections</div>@foreach($collections as $c)<label class="mb-2 flex items-center gap-2 text-sm"><input type="checkbox" name="collections[]" value="{{ $c->id }}" @checked(in_array($c->id,old('collections',$editing?$product->collections->pluck('id')->all():[])))> {{ $c->name }}</label>@endforeach</div></section>
<section class="rounded-3xl border border-neutral-200 bg-white p-6"><h2 class="font-semibold">Base commerce</h2><div class="mt-4 grid gap-4"><label>Base price<input name="base_price" type="number" step="0.01" value="{{ old('base_price',$product->base_price) }}" class="mt-2 w-full rounded-2xl border-neutral-200"></label><label>Compare at price<input name="compare_at_price" type="number" step="0.01" value="{{ old('compare_at_price',$product->compare_at_price) }}" class="mt-2 w-full rounded-2xl border-neutral-200"></label><label>Stock<input name="stock" type="number" value="{{ old('stock',$product->stock ?? 0) }}" class="mt-2 w-full rounded-2xl border-neutral-200"></label></div></section>
<section class="rounded-3xl border border-neutral-200 bg-white p-6"><h2 class="font-semibold">SEO</h2><div class="mt-4 grid gap-4"><label>Meta title<input name="meta_title" value="{{ old('meta_title',$product->meta_title) }}" class="mt-2 w-full rounded-2xl border-neutral-200"></label><label>Meta description<textarea name="meta_description" rows="4" class="mt-2 w-full rounded-2xl border-neutral-200">{{ old('meta_description',$product->meta_description) }}</textarea></label></div></section></aside>
</div></form>
@endsection

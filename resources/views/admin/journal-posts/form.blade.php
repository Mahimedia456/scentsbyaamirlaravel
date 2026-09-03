@extends('admin.layouts.app')
@section('title',$post->exists?'Edit Article':'Create Article')
@section('header',$post->exists?'Edit Article':'Create Article')
@section('eyebrow','CMS / editorial')

@section('content')
@php
    $imagePath = $post->featured_image_path ?? null;

    $resolveJournalImage = function ($path) {
        if (!$path) return null;

        $path = str_replace('\\', '/', trim((string) $path));

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/media/')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return route('store.media', ['path' => ltrim($path, '/')]);
    };

    $imageUrl = $resolveJournalImage($imagePath);
@endphp

@if($post->exists && $post->wordpress_id)
<div class="admin-card" style="padding:14px 18px;margin-bottom:14px">
    <div class="admin-eyebrow">WordPress Journal Import</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:10px;font-size:11px">
        <div><strong>WordPress ID</strong><br>{{ $post->wordpress_id }}</div>
        @if($post->author_name)<div><strong>Author</strong><br>{{ $post->author_name }}</div>@endif
        @if($post->imported_at)<div><strong>Imported</strong><br>{{ $post->imported_at->format('d M Y, h:i A') }}</div>@endif
        @if($post->source_url)<div style="min-width:0"><strong>Original article</strong><br><a href="{{ $post->source_url }}" target="_blank" rel="noopener" style="word-break:break-all">Open WordPress source</a></div>@endif
    </div>
    <p class="admin-muted" style="margin-top:10px;font-size:10px">Categories, tags and images are imported. Comments are intentionally not imported.</p>
</div>
@endif

<form method="POST" enctype="multipart/form-data" action="{{ $post->exists?route('admin.journal-posts.update',$post):route('admin.journal-posts.store') }}">
    @csrf
    @if($post->exists) @method('PUT') @endif

    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:18px">
        <div>
            <a href="{{ route('admin.journal-posts.index') }}" class="admin-muted" style="font-size:10px">← Back</a>
            <h2 style="font-size:27px;margin:7px 0 0">{{ $post->exists?$post->title:'New article' }}</h2>
        </div>
        <button class="admin-btn admin-btn-primary">Save article</button>
    </div>

    @if($errors->any())
        <div class="admin-card" style="padding:14px 18px;margin-bottom:14px;border-color:#ef4444">
            <strong>Please correct the following:</strong>
            <ul style="margin:8px 0 0;padding-left:18px;font-size:12px">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:14px;align-items:start">
        <section class="admin-card" style="padding:20px">
            <div class="admin-eyebrow">Editorial content</div>
            <div style="display:grid;gap:11px;margin-top:15px">
                <input class="admin-field" required name="title" value="{{ old('title',$post->title) }}" placeholder="Article title">
                <input class="admin-field" name="slug" value="{{ old('slug',$post->slug) }}" placeholder="Slug">
                <input class="admin-field" name="eyebrow" value="{{ old('eyebrow',$post->eyebrow) }}" placeholder="Eyebrow / section">
                <input class="admin-field" name="category_names" value="{{ old('category_names', collect($post->wordpress_categories ?? [])->pluck('name')->filter()->implode(', ')) }}" placeholder="Categories (comma separated)">
                <input class="admin-field" name="tag_names" value="{{ old('tag_names', collect($post->wordpress_tags ?? [])->pluck('name')->filter()->implode(', ')) }}" placeholder="Tags (comma separated)">
                <textarea class="admin-field" style="padding-top:10px;min-height:100px" name="excerpt" placeholder="Excerpt">{{ old('excerpt',$post->excerpt) }}</textarea>
                <textarea class="admin-field" style="padding-top:10px;min-height:420px;font-family:ui-monospace,monospace;font-size:11px" name="content" placeholder="Article content / HTML">{{ old('content',$post->content) }}</textarea>
            </div>
        </section>

        <aside style="display:grid;gap:14px;position:sticky;top:96px">
            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Featured image</div>

                <div style="margin-top:14px;border:1px solid #e4e7ec;background:#f8fafc;overflow:hidden;aspect-ratio:16/10;display:flex;align-items:center;justify-content:center">
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $post->title ?: 'Journal featured image' }}" style="width:100%;height:100%;object-fit:cover;display:block">
                    @else
                        <span class="admin-muted" style="font-size:11px">No featured image</span>
                    @endif
                </div>

                <div style="display:grid;gap:10px;margin-top:12px">
                    <label style="font-size:11px;font-weight:700">Upload / replace image</label>
                    <input class="admin-field" type="file" name="featured_image" accept="image/jpeg,image/png,image/webp">
                    <p class="admin-muted" style="font-size:10px;margin:0">JPG, PNG or WEBP. Maximum 8 MB. Uploaded files are stored in Laravel public storage.</p>

                    @if($imagePath)
                        <label style="display:flex;align-items:center;gap:8px;font-size:11px;margin-top:4px">
                            <input type="checkbox" name="remove_featured_image" value="1">
                            Remove current featured image
                        </label>
                    @endif
                </div>
            </section>

            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Publishing</div>
                <div style="display:grid;gap:10px;margin-top:14px">
                    <select class="admin-field" name="status">
                        @foreach(['draft','published','archived'] as $s)
                            <option value="{{ $s }}" @selected(old('status',$post->status?:'draft')===$s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <input class="admin-field" type="datetime-local" name="published_at" value="{{ old('published_at',optional($post->published_at)->format('Y-m-d\TH:i')) }}">
                </div>
            </section>

            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">SEO</div>
                <div style="display:grid;gap:10px;margin-top:14px">
                    <input class="admin-field" name="meta_title" value="{{ old('meta_title',$post->meta_title) }}" placeholder="Meta title">
                    <textarea class="admin-field" style="padding-top:10px;min-height:100px" name="meta_description" placeholder="Meta description">{{ old('meta_description',$post->meta_description) }}</textarea>
                </div>
            </section>
        </aside>
    </div>
</form>
@endsection

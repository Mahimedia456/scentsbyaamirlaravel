@extends('admin.layouts.app')
@section('title',$post->exists?'Edit Journal Post':'New Journal Post')
@section('header','Journal')
@section('eyebrow','CMS / editorial')

@section('content')
<form method="POST" action="{{ $post->exists?route('admin.journal-posts.update',$post):route('admin.journal-posts.store') }}">
    @csrf
    @if($post->exists) @method('PUT') @endif

    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:18px">
        <div>
            <a href="{{ route('admin.journal-posts.index') }}" class="admin-muted" style="font-size:10px">← Back</a>
            <h2 style="font-size:27px;margin:7px 0 0">{{ $post->exists?$post->title:'New article' }}</h2>
            @if($post->source==='wordpress')
                <div style="display:flex;gap:7px;align-items:center;margin-top:7px;flex-wrap:wrap">
                    <span class="admin-status success">Imported from WordPress</span>
                    @if($post->wordpress_id)<span class="admin-muted" style="font-size:9px">WP #{{ $post->wordpress_id }}</span>@endif
                    @if($post->imported_at)<span class="admin-muted" style="font-size:9px">Imported {{ $post->imported_at->format('d M Y H:i') }}</span>@endif
                </div>
            @endif
        </div>
        <button class="admin-btn admin-btn-primary">Save article</button>
    </div>

    @if($errors->any())
        <div class="admin-card" style="padding:14px;margin-bottom:14px;border-color:#fecaca;background:#fff7f7;color:#991b1b">
            @foreach($errors->all() as $error)<div style="font-size:10px;margin:3px 0">{{ $error }}</div>@endforeach
        </div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:14px;align-items:start">
        <section class="admin-card" style="padding:20px">
            <div class="admin-eyebrow">Editorial content</div>
            <div style="display:grid;gap:11px;margin-top:15px">
                <input class="admin-field" required name="title" value="{{ old('title',$post->title) }}" placeholder="Article title">
                <input class="admin-field" name="slug" value="{{ old('slug',$post->slug) }}" placeholder="Slug">
                <input class="admin-field" name="eyebrow" value="{{ old('eyebrow',$post->eyebrow) }}" placeholder="Eyebrow / section">
                <input class="admin-field" name="author_name" value="{{ old('author_name',$post->author_name) }}" placeholder="Author">
                <input class="admin-field" name="categories_text" value="{{ old('categories_text',implode(', ',(array)$post->categories)) }}" placeholder="Categories, comma separated">
                <input class="admin-field" name="tags_text" value="{{ old('tags_text',implode(', ',(array)$post->tags)) }}" placeholder="Tags, comma separated">
                <textarea class="admin-field" style="padding-top:10px;min-height:100px" name="excerpt" placeholder="Excerpt">{{ old('excerpt',$post->excerpt) }}</textarea>
                <textarea class="admin-field" style="padding-top:10px;min-height:520px;font-family:ui-monospace,monospace;font-size:11px" name="content" placeholder="Article HTML / content">{{ old('content',$post->content) }}</textarea>
            </div>
        </section>

        <aside style="display:grid;gap:14px;position:sticky;top:96px">
            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">Publishing</div>
                <div style="display:grid;gap:10px;margin-top:14px">
                    <select class="admin-field" name="status">
                        @foreach(['draft','published','archived'] as $s)<option value="{{ $s }}" @selected(old('status',$post->status?:'draft')===$s)>{{ ucfirst($s) }}</option>@endforeach
                    </select>
                    <input class="admin-field" type="datetime-local" name="published_at" value="{{ old('published_at',optional($post->published_at)->format('Y-m-d\TH:i')) }}">
                    <input class="admin-field" name="featured_image_path" value="{{ old('featured_image_path',$post->featured_image_path) }}" placeholder="Featured media path">
                </div>
            </section>

            @if($post->source==='wordpress')
                <section class="admin-card" style="padding:20px">
                    <div class="admin-eyebrow">WordPress source</div>
                    <div class="admin-muted" style="font-size:10px;line-height:1.7;margin-top:12px;word-break:break-all">
                        {{ $post->wordpress_url ?: 'No source URL stored.' }}
                    </div>
                    @if($post->wordpress_modified_at)
                        <div class="admin-muted" style="font-size:9px;margin-top:8px">WP modified {{ $post->wordpress_modified_at->format('d M Y H:i') }}</div>
                    @endif
                    @if($post->wordpress_url)
                        <a href="{{ $post->wordpress_url }}" target="_blank" rel="noopener noreferrer" class="admin-btn" style="margin-top:12px">Open original</a>
                    @endif
                </section>
            @endif

            <section class="admin-card" style="padding:20px">
                <div class="admin-eyebrow">SEO</div>
                <div style="display:grid;gap:10px;margin-top:14px">
                    <input class="admin-field" name="meta_title" value="{{ old('meta_title',$post->meta_title) }}" placeholder="Meta title">
                    <textarea class="admin-field" style="padding-top:10px;min-height:90px" name="meta_description" placeholder="Meta description">{{ old('meta_description',$post->meta_description) }}</textarea>
                    <input class="admin-field" name="canonical_url" value="{{ old('canonical_url',$post->canonical_url) }}" placeholder="Canonical URL">
                    <input class="admin-field" name="og_title" value="{{ old('og_title',$post->og_title) }}" placeholder="Open Graph title">
                    <textarea class="admin-field" style="padding-top:10px;min-height:90px" name="og_description" placeholder="Open Graph description">{{ old('og_description',$post->og_description) }}</textarea>
                    <input class="admin-field" name="og_image_path" value="{{ old('og_image_path',$post->og_image_path) }}" placeholder="Open Graph image path">
                </div>
            </section>
        </aside>
    </div>
</form>
@endsection

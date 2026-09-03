@extends('admin.layouts.app')
@section('title','Journal')
@section('header','Journal')
@section('eyebrow','CMS / editorial')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:18px">
    <div>
        <h2 style="font-size:27px;margin:0">Journal</h2>
        <p class="admin-muted" style="font-size:12px;margin:7px 0 0">Editorial publishing, featured imagery, categories, tags and search appearance.</p>
    </div>
    <a class="admin-btn admin-btn-primary" href="{{ route('admin.journal-posts.create') }}">+ New article</a>
</div>
<section class="admin-card">
    <form method="GET" style="display:flex;gap:8px;padding:14px;border-bottom:1px solid #e4e7ec">
        <input class="admin-field" name="q" value="{{ request('q') }}" placeholder="Search journal">
        <button class="admin-btn">Search</button>
    </form>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Article</th><th>Image</th><th>Section</th><th>Status</th><th>Published</th><th style="text-align:right">Actions</th></tr></thead>
            <tbody>
            @forelse($posts as $p)
                @php
                    $path = $p->featured_image_path;
                    $thumb = null;

                    if ($path) {
                        $path = str_replace('\\', '/', trim((string) $path));

                        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/media/')) {
                            $thumb = $path;
                        } else {
                            if (str_starts_with($path, '/storage/')) {
                                $path = substr($path, strlen('/storage/'));
                            } elseif (str_starts_with($path, 'storage/')) {
                                $path = substr($path, strlen('storage/'));
                            }

                            $thumb = route('store.media', ['path' => ltrim($path, '/')]);
                        }
                    }
                @endphp
                <tr>
                    <td>
                        <a style="font-weight:700" href="{{ route('admin.journal-posts.edit',$p) }}">{{ $p->title }}</a>
                        <div class="admin-muted" style="font-size:9px;margin-top:4px">/{{ $p->slug }} @if($p->wordpress_id) · WordPress import @endif</div>
                    </td>
                    <td style="width:92px">
                        @if($thumb)
                            <img src="{{ $thumb }}" alt="" style="width:72px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #e4e7ec">
                        @else
                            <span class="admin-muted" style="font-size:9px">No image</span>
                        @endif
                    </td>
                    <td>
                        {{ $p->eyebrow ?: 'Editorial' }}
                        @if(!empty($p->wordpress_categories))<div class="admin-muted" style="font-size:9px;margin-top:4px">{{ collect($p->wordpress_categories)->pluck('name')->filter()->implode(', ') }}</div>@endif
                    </td>
                    <td><span class="admin-status {{ $p->status==='published'?'success':'warning' }}">{{ ucfirst($p->status) }}</span></td>
                    <td>{{ optional($p->published_at)->format('d M Y H:i') ?: '—' }}</td>
                    <td style="text-align:right"><div style="display:flex;justify-content:flex-end;gap:6px"><a class="admin-btn" href="{{ route('admin.journal-posts.edit',$p) }}">Edit</a><form method="POST" action="{{ route('admin.journal-posts.duplicate',$p) }}">@csrf<button class="admin-btn">Duplicate</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="admin-muted" style="padding:45px;text-align:center">No articles.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<div style="margin-top:18px">{{ $posts->links() }}</div>
@endsection

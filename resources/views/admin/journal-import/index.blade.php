@extends('admin.layouts.app')
@section('title','WordPress Journal Import')
@section('header','WordPress Journal Import')
@section('eyebrow','CMS / migration')

@section('content')
@php($stats = session('journal_import_stats'))
<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:18px">
    <div>
        <a href="{{ route('admin.journal-posts.index') }}" class="admin-muted" style="font-size:10px">← Journal</a>
        <h2 style="font-size:27px;margin:7px 0 0">Import WordPress stories</h2>
        <p class="admin-muted" style="font-size:12px;margin:7px 0 0;max-width:760px;line-height:1.7">
            One-time migration from the legacy WordPress site into Laravel. Published posts, featured images, inline article images,
            categories, tags, author and available Yoast SEO fields are copied into this store.
        </p>
    </div>
</div>

@if(session('error'))
    <div class="admin-card" style="padding:16px;margin-bottom:14px;border-color:#fecaca;background:#fff7f7;color:#991b1b">{{ session('error') }}</div>
@endif

@if($stats)
    <section class="admin-card" style="padding:18px;margin-bottom:14px">
        <div class="admin-eyebrow">Last run</div>
        <div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;margin-top:14px">
            @foreach([['Found',$stats['found']],['Created',$stats['created']],['Updated',$stats['updated']],['Skipped',$stats['skipped']],['Images',$stats['images']]] as [$label,$value])
                <div style="padding:14px;background:#f8fafc;border:1px solid #edf0f3">
                    <div class="admin-muted" style="font-size:9px">{{ $label }}</div>
                    <div style="font-size:22px;font-weight:750;margin-top:4px">{{ $value }}</div>
                </div>
            @endforeach
        </div>
        @if(!empty($stats['warnings']))
            <div style="margin-top:14px;padding:12px;border:1px solid #fde68a;background:#fffbeb">
                <div style="font-size:10px;font-weight:700">Warnings</div>
                @foreach($stats['warnings'] as $warning)<div style="font-size:10px;margin-top:5px;word-break:break-word">{{ $warning }}</div>@endforeach
            </div>
        @endif
    </section>
@endif

<div style="display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:14px;align-items:start">
    <section class="admin-card" style="padding:20px">
        <div class="admin-eyebrow">Import controls</div>
        <form method="POST" action="{{ route('admin.journal-import.run') }}" style="display:grid;gap:14px;margin-top:16px">
            @csrf
            <label style="font-size:11px;font-weight:680">Mode
                <select name="mode" class="admin-field" style="margin-top:7px">
                    <option value="dry-run">Dry run — preview only</option>
                    <option value="import">Import new WordPress posts</option>
                    <option value="update">Update previously imported posts</option>
                </select>
            </label>

            <label style="font-size:11px;font-weight:680">Limit (optional)
                <input name="limit" type="number" min="1" max="500" value="{{ old('limit') }}" class="admin-field" style="margin-top:7px" placeholder="Leave empty for all posts">
            </label>

            <label style="display:flex;align-items:center;gap:9px;font-size:11px">
                <input type="hidden" name="images" value="0">
                <input type="checkbox" name="images" value="1" checked>
                Download featured + inline images into Laravel storage
            </label>

            <button class="admin-btn admin-btn-primary" style="justify-self:start">Run import</button>
        </form>
    </section>

    <aside class="admin-card" style="padding:20px;position:sticky;top:96px">
        <div class="admin-eyebrow">Source</div>
        <div style="font-size:11px;line-height:1.7;margin-top:12px;word-break:break-all">{{ $sourceUrl }}</div>
        <div class="admin-muted" style="font-size:10px;line-height:1.7;margin-top:12px">
            For a full image-heavy import, SSH/Artisan is more reliable than a browser request because shared hosting may impose HTTP execution limits.
        </div>
        <code style="display:block;margin-top:12px;padding:10px;background:#111;color:#fff;font-size:9px;white-space:normal;word-break:break-all">php artisan wordpress:import-journal --dry-run</code>
        <code style="display:block;margin-top:8px;padding:10px;background:#111;color:#fff;font-size:9px;white-space:normal;word-break:break-all">php artisan wordpress:import-journal</code>
    </aside>
</div>
@endsection

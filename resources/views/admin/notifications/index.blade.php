@extends('admin.layouts.app')
@section('title','Notifications')
@section('header','Notifications')
@section('eyebrow','Operations center')

@section('content')
<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:20px">
    <div>
        <h2 style="margin:0;font-size:26px;letter-spacing:-.03em">Notification center</h2>
        <p class="admin-muted" style="margin:7px 0 0;font-size:12px">System, commerce, customer and operational alerts that need admin attention.</p>
    </div>
    <form method="POST" action="{{ route('admin.notifications.read-all') }}">
        @csrf
        <button class="admin-btn">Mark all as read</button>
    </form>
</div>

<section class="admin-card">
    @forelse($notifications as $notification)
        <article class="admin-notification-item">
            <span class="admin-notification-marker {{ $notification->type }}"></span>
            <div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <div class="admin-notification-title">{{ $notification->title }}</div>
                    @if(!$notification->read_at)<span class="admin-status warning">Unread</span>@endif
                </div>
                @if($notification->message)
                    <div class="admin-notification-message">{{ $notification->message }}</div>
                @endif
                <div class="admin-muted" style="margin-top:7px;font-size:9px">{{ $notification->updated_at->diffForHumans() }}</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="admin-btn admin-btn-primary">{{ $notification->action_label ?: 'Open' }}</a>
                    @endif
                    @if(!$notification->read_at)
                        <form method="POST" action="{{ route('admin.notifications.read',$notification) }}">
                            @csrf
                            <button class="admin-btn">Mark read</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.notifications.dismiss',$notification) }}">
                        @csrf
                        @method('DELETE')
                        <button class="admin-btn" data-admin-confirm="Dismiss this notification?">Dismiss</button>
                    </form>
                </div>
            </div>
            <span class="admin-status {{ $notification->type === 'success' ? 'success' : ($notification->type === 'warning' || $notification->type === 'danger' ? 'warning' : '') }}">
                {{ ucfirst($notification->type) }}
            </span>
        </article>
    @empty
        <div style="padding:62px 20px;text-align:center">
            <div style="font-size:15px;font-weight:680">No active notifications</div>
            <p class="admin-muted" style="margin:8px 0 0;font-size:12px">System-generated alerts will appear here automatically.</p>
        </div>
    @endforelse
</section>

@if($notifications->hasPages())
    <div style="margin-top:18px">{{ $notifications->links() }}</div>
@endif
@endsection

@extends('admin.layouts.app')
@section('title','Dashboard')
@section('header','Dashboard')
@section('eyebrow','Executive overview')

@section('content')
@php
    $maxRevenue = max(1, (float) $salesSeries->max('revenue'));
    $revenue30Share = $stats['revenue'] > 0 ? ($stats['revenue_30d'] / $stats['revenue']) * 100 : 0;
@endphp

<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:22px">
    <div>
        <h2 style="margin:0;font-size:28px;letter-spacing:-.035em;font-weight:700">Commerce control center</h2>
        <p class="admin-muted" style="margin:8px 0 0;font-size:13px">Live commercial performance and operational priorities in one view.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <span class="admin-status {{ $databaseOnline ? 'success' : 'warning' }}">Database {{ $databaseOnline ? 'online' : 'offline' }}</span>
        <span class="admin-status {{ $mailConfigured ? 'success' : 'warning' }}">Email {{ $mailConfigured ? 'configured' : 'needs SMTP' }}</span>
        <a href="{{ route('admin.notifications.index') }}" class="admin-status {{ $unreadNotifications ? 'warning' : 'success' }}">{{ $unreadNotifications }} unread alert{{ $unreadNotifications === 1 ? '' : 's' }}</a>
    </div>
</div>

<div class="admin-kpi-grid">
    @foreach([
        ['Revenue · 30 days','PKR '.number_format($stats['revenue_30d']),number_format($revenue30Share,1).'% of recorded revenue'],
        ['Orders · 30 days',number_format($stats['orders_30d']),number_format($stats['pending_orders']).' pending / processing'],
        ['Average order value','PKR '.number_format($stats['aov']),'Across non-cancelled orders'],
        ['New customers · 30 days',number_format($stats['new_customers_30d']),number_format($stats['customers']).' total customers'],
    ] as [$label,$value,$hint])
        <article class="admin-card admin-kpi">
            <div class="admin-eyebrow">{{ $label }}</div>
            <div class="admin-kpi-value">{{ $value }}</div>
            <div class="admin-muted" style="margin-top:6px;font-size:11px">{{ $hint }}</div>
        </article>
    @endforeach
</div>

<div class="admin-section-grid" style="margin-top:14px">
    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <div class="admin-eyebrow">Revenue trend</div>
                <div style="margin-top:4px;font-size:14px;font-weight:700">Last 30 days</div>
            </div>
            <a href="{{ route('admin.analytics.index') }}" class="admin-btn">Full analytics</a>
        </div>
        <div style="padding:20px">
            <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:14px">
                <div>
                    <div class="admin-muted" style="font-size:10px">Period revenue</div>
                    <div style="margin-top:5px;font-size:22px;font-weight:720;letter-spacing:-.03em">PKR {{ number_format($stats['revenue_30d']) }}</div>
                </div>
                <div class="admin-muted" style="font-size:10px">{{ $salesSeries->first()['label'] ?? '' }} — {{ $salesSeries->last()['label'] ?? '' }}</div>
            </div>
            <div class="admin-chart" aria-label="30-day revenue bar chart">
                @foreach($salesSeries as $point)
                    @php $height = max(3, ((float)$point['revenue'] / $maxRevenue) * 100); @endphp
                    <div class="admin-chart-bar"
                         style="height:{{ $height }}%"
                         title="{{ $point['label'] }} · PKR {{ number_format($point['revenue']) }} · {{ $point['orders'] }} order(s)"></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <div class="admin-eyebrow">Operations</div>
                <div style="margin-top:4px;font-size:14px;font-weight:700">Order status</div>
            </div>
        </div>
        <div style="padding:8px 20px 18px">
            @forelse($statusBreakdown as $status)
                <div class="admin-mini-stat">
                    <span style="font-size:12px;text-transform:capitalize">{{ str_replace('_',' ',$status->status) }}</span>
                    <strong style="font-size:12px">{{ number_format($status->total) }}</strong>
                </div>
            @empty
                <div class="admin-muted" style="padding:30px 0;text-align:center;font-size:12px">No order-status data yet.</div>
            @endforelse
        </div>
    </section>
</div>

<div class="admin-section-grid" style="margin-top:14px">
    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <div class="admin-eyebrow">Commerce</div>
                <div style="margin-top:4px;font-size:14px;font-weight:700">Recent orders</div>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="admin-btn">Open orders</a>
        </div>
        @if($recentOrders->isEmpty())
            <div style="padding:52px 20px;text-align:center">
                <div style="font-size:13px;font-weight:650">No orders yet</div>
                <p class="admin-muted" style="margin:7px 0 0;font-size:12px">New storefront orders will appear here.</p>
            </div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th style="text-align:right">Total</th></tr></thead>
                    <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show',$order) }}" style="font-weight:680">{{ $order->order_number }}</a></td>
                            <td>{{ $order->customer_name ?: 'Guest' }}</td>
                            <td><span class="admin-status">{{ ucfirst($order->status) }}</span></td>
                            <td style="text-align:right;font-weight:650">{{ $order->currency }} {{ number_format((float)$order->grand_total) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <div class="admin-eyebrow">Notification center</div>
                <div style="margin-top:4px;font-size:14px;font-weight:700">Needs attention</div>
            </div>
            <a href="{{ route('admin.notifications.index') }}" class="admin-btn">View all</a>
        </div>
        @forelse($notifications as $notification)
            <div class="admin-notification-item">
                <span class="admin-notification-marker {{ $notification->type }}"></span>
                <div>
                    <div class="admin-notification-title">{{ $notification->title }}</div>
                    @if($notification->message)<div class="admin-notification-message">{{ $notification->message }}</div>@endif
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" style="display:inline-block;margin-top:7px;font-size:10px;font-weight:700">{{ $notification->action_label ?: 'Open' }} →</a>
                    @endif
                </div>
                @if(!$notification->read_at)<span class="admin-status warning">New</span>@endif
            </div>
        @empty
            <div style="padding:42px 20px;text-align:center">
                <div style="font-size:13px;font-weight:650">All clear</div>
                <p class="admin-muted" style="margin:7px 0 0;font-size:11px">No unresolved system alerts right now.</p>
            </div>
        @endforelse
    </section>
</div>

<div class="admin-section-grid" style="margin-top:14px">
    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <div class="admin-eyebrow">Product performance</div>
                <div style="margin-top:4px;font-size:14px;font-weight:700">Top products by revenue</div>
            </div>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Product</th><th style="text-align:right">Units</th><th style="text-align:right">Revenue</th></tr></thead>
                <tbody>
                @forelse($topProducts as $row)
                    <tr>
                        <td style="font-weight:650">{{ $row->product_name }}</td>
                        <td style="text-align:right">{{ number_format($row->qty) }}</td>
                        <td style="text-align:right">PKR {{ number_format((float)$row->revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="admin-muted" style="text-align:center;padding:34px">Sales data will appear after orders are placed.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card" style="padding:20px">
        <div class="admin-eyebrow">Platform health</div>
        <h3 style="margin:7px 0 18px;font-size:17px">Enterprise readiness</h3>
        @foreach([
            ['Database',$databaseOnline],
            ['Separate admin bundle',true],
            ['Global search',true],
            ['Notification center',true],
            ['Customer activation',true],
            ['SMTP transport',$mailConfigured],
        ] as [$name,$ready])
            <div class="admin-mini-stat">
                <span style="font-size:12px">{{ $name }}</span>
                <span class="admin-status {{ $ready ? 'success' : 'warning' }}">{{ $ready ? 'Ready' : 'Configure' }}</span>
            </div>
        @endforeach
        <a href="{{ route('admin.mail-diagnostics.index') }}" class="admin-btn" style="width:100%;box-sizing:border-box;margin-top:17px">Open mail diagnostics</a>
    </section>
</div>
@endsection

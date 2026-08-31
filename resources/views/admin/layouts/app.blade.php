<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Admin') — Scents by Aamir</title>
    @vite(['resources/css/admin.css','resources/js/admin.js'])
</head>
<body>
@php
    $navGroups = [
        'Overview' => [
            ['Dashboard','admin.dashboard','DB'],
            ['Analytics','admin.analytics.index','AN'],
        ],
        'Commerce' => [
            ['Orders','admin.orders.index','OR'],
            ['Customers','admin.customers.index','CU'],
            ['Inventory','admin.inventory.index','IN'],
            ['Promotions','admin.coupons.index','PR'],
        ],
        'Catalog' => [
            ['Products','admin.products.index','PD'],
            ['Categories','admin.categories.index','CT'],
            ['Collections','admin.collections.index','CL'],
        ],
        'Content' => [
            ['Content Overview','admin.content.index','CM'],
            ['Pages','admin.pages.index','PG'],
            ['Journal','admin.journal-posts.index','JR'],
            ['Navigation','admin.navigations.index','NV'],
            ['Media','admin.media.index','MD'],
            ['SEO','admin.seo.index','SE'],
        ],
        'Operations' => [
            ['Support','admin.contact-inquiries.index','SP'],
            ['Newsletter','admin.newsletter.index','NL'],
            ['Shipping','admin.shipping.index','SH'],
            ['Payments','admin.payments.index','PY'],
        ],
        'System' => [
            ['Notifications','admin.notifications.index','NT'],
            ['Mail Diagnostics','admin.mail-diagnostics.index','EM'],
            ['Store Settings','admin.settings.index','ST'],
            ['Admin Users','admin.admin-users.index','US'],
            ['Audit Log','admin.audit.index','AU'],
            ['Woo Import','admin.woocommerce.index','WC'],
        ],
    ];
    $routePermissions = [
        'admin.products.index'=>'catalog',
        'admin.categories.index'=>'catalog',
        'admin.collections.index'=>'catalog',
        'admin.orders.index'=>'orders',
        'admin.customers.index'=>'customers',
        'admin.inventory.index'=>'inventory',
        'admin.coupons.index'=>'promotions',
        'admin.content.index'=>'content',
        'admin.pages.index'=>'content',
        'admin.journal-posts.index'=>'content',
        'admin.navigations.index'=>'content',
        'admin.media.index'=>'media',
        'admin.analytics.index'=>'analytics',
        'admin.contact-inquiries.index'=>'support',
        'admin.newsletter.index'=>'content',
        'admin.settings.index'=>'system',
        'admin.admin-users.index'=>'system',
        'admin.audit.index'=>'system',
        'admin.mail-diagnostics.index'=>'system',
        'admin.woocommerce.index'=>'system',
    ];

    $globalUnreadNotifications = \Illuminate\Support\Facades\Schema::hasTable('admin_notifications')
        ? \App\Models\AdminNotification::query()->visible()->unread()->count()
        : 0;
@endphp

<div class="admin-shell" data-admin-shell>
    <button class="admin-drawer-backdrop" type="button" data-admin-nav-close aria-label="Close navigation"></button>

    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="{{ route('admin.dashboard') }}" aria-label="Scents by Aamir Admin">
                <img src="{{ asset('logo.png') }}" alt="Scents by Aamir">
            </a>
            <div>
                <div style="font-size:12px;font-weight:760;letter-spacing:.01em">Enterprise Admin</div>
                <div style="margin-top:2px;font-size:10px;color:rgba(255,255,255,.38)">Commerce control center</div>
            </div>
        </div>

        <nav class="admin-nav-scroll" aria-label="Admin navigation">
            @foreach($navGroups as $group => $items)
                <section class="admin-nav-group">
                    <p class="admin-nav-label">{{ $group }}</p>
                    @foreach($items as [$label,$route,$icon])
                        @php
                            $permission = $routePermissions[$route] ?? 'dashboard';
                            if (!auth()->user()->canAdmin($permission)) continue;
                            $pattern = str_ends_with($route,'.index')
                                ? str_replace('.index','.*',$route)
                                : $route;
                            $active = request()->routeIs($route) || request()->routeIs($pattern);
                        @endphp
                        <a href="{{ route($route) }}" class="admin-nav-link {{ $active ? 'is-active' : '' }}">
                            <span class="admin-nav-icon">{{ $icon }}</span>
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </section>
            @endforeach
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-user-card">
                <div class="admin-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}</div>
                <div style="min-width:0;flex:1">
                    <div style="font-size:12px;font-weight:680;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->name }}</div>
                    <div style="margin-top:2px;font-size:10px;color:rgba(255,255,255,.38);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin-top:9px">
                @csrf
                <button type="submit" class="admin-nav-link" style="width:100%;border:0;background:transparent;cursor:pointer">
                    <span class="admin-nav-icon">↗</span><span>Sign out</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;min-width:0">
                <button type="button" class="admin-btn admin-mobile-button" data-admin-nav-open aria-label="Open navigation">☰</button>
                <div style="min-width:0">
                    <div class="admin-eyebrow">@yield('eyebrow','Scents by Aamir')</div>
                    <h1 class="admin-heading">@yield('header','Dashboard')</h1>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:9px">
                <button type="button" class="admin-btn admin-search-trigger" data-admin-command-open>
                    <span>Search</span><kbd>Ctrl K</kbd>
                </button>
                <a href="{{ route('admin.notifications.index') }}" class="admin-icon-btn" aria-label="Notifications">
                    <span>NT</span>
                    @if($globalUnreadNotifications > 0)
                        <b class="admin-notification-dot">{{ $globalUnreadNotifications > 99 ? '99+' : $globalUnreadNotifications }}</b>
                    @endif
                </a>
                <a href="{{ route('home') }}" target="_blank" class="admin-btn">View storefront ↗</a>
            </div>
        </header>

        <div class="admin-page">
            @if(session('success'))
                <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="admin-alert admin-alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="admin-alert admin-alert-danger">
                    <ul style="margin:0;padding-left:18px">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

<div class="admin-command" data-admin-command hidden>
    <button type="button" class="admin-command-backdrop" data-admin-command-close aria-label="Close search"></button>
    <section class="admin-command-panel" role="dialog" aria-modal="true" aria-label="Admin search">
        <div class="admin-command-input-wrap">
            <span class="admin-command-search-icon">⌕</span>
            <input type="search" autocomplete="off" placeholder="Search products, orders, customers…" data-admin-command-input>
            <kbd>ESC</kbd>
        </div>
        <div class="admin-command-results" data-admin-command-results>
            <div class="admin-command-empty">
                Type at least 2 characters to search across commerce records.
            </div>
        </div>
        <div class="admin-command-footer">
            <span>Quick search</span>
            <span>Products · Orders · Customers</span>
        </div>
    </section>
</div>

</body>
</html>

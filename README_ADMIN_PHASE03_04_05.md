# Scents by Aamir — Admin Phases 03, 04 & 05

This is a complete Laravel checkpoint built on the accepted storefront + Admin/API/DB Phase 01 + Catalog Phase 02.

## Included

### Phase 03 — Orders & Customers
- Order list/search/filter, order detail, fulfilment/payment/tracking updates
- Customer list/search, create/edit/detail/deactivate
- Customer order history and lifetime order totals
- DB enhancements for billing/shipping/order admin metadata

### Phase 04 — Inventory & Promotions
- Inventory dashboard with base + variant stock
- Manual stock adjustments and immutable adjustment history
- Coupon/promotion CRUD: percentage/fixed, minimum order, max discount, usage limits, scheduling
- Storefront-ready `/api/v1/promotions/validate`
- Compatibility fix for Phase 02 variant `stock` and `sort_order`

### Phase 05 — CMS / Journal / Navigation
- CMS page CRUD with draft/published/archived state and SEO fields
- Journal post CRUD with image path, excerpt, publication state and SEO fields
- Navigation/menu manager
- Public content APIs for pages, journal and navigation

## Existing Phase 02 also wired directly
The complete `routes/web.php` and `routes/api.php` now include catalog admin routes and catalog APIs; separate route-snippet files are no longer required for this checkpoint.

## Install / merge
Replace/merge this checkpoint into your Laravel project root. Do not copy `vendor`, `node_modules` or `.env` from anywhere else.

Run manually:

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
npm run build
php artisan serve
```

Existing Phase 01 seed data does not need to be re-seeded. If you have not seeded Phase 01 yet, run `php artisan db:seed` once.

## Main admin routes
- `/admin/products`
- `/admin/categories`
- `/admin/collections`
- `/admin/orders`
- `/admin/customers`
- `/admin/inventory`
- `/admin/coupons`
- `/admin/pages`
- `/admin/journal-posts`
- `/admin/navigations`

## API routes
- `GET /api/v1/catalog/products`
- `GET /api/v1/catalog/products/{slug}`
- `GET /api/v1/catalog/categories`
- `GET /api/v1/catalog/collections`
- `POST /api/v1/promotions/validate`
- `GET /api/v1/content/pages/{slug}`
- `GET /api/v1/content/journal`
- `GET /api/v1/content/journal/{slug}`
- `GET /api/v1/content/navigation/{key}`

## Roadmap note
A later dedicated WooCommerce Migration Center is reserved for a one-time WordPress/WooCommerce import into Laravel DB. It is intentionally not implemented in Phases 03–05.

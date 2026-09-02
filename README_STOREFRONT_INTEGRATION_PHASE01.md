# Scents by Aamir — Storefront Integration Phase 01

This checkpoint preserves the accepted storefront and Admin Phases 01–09, then starts the live storefront/database integration.

## Phase 01 completed
- Storefront home fragrance selection reads active/featured Laravel catalog products when available.
- `/shop` reads active products, category/collection relationships, variants, stock and product images from MySQL.
- `/product/{slug}` reads the real Laravel product, variants, price, stock, editorial fields and related products.
- `/collections/{slug}` reads admin-managed collections and assigned active products.
- Existing static storefront configuration remains a safe visual fallback when the database has no active products yet. This keeps the accepted design usable while catalog data is being entered/imported.
- Existing `/api/v1/catalog/*` API remains available for API consumers.
- Product image paths support absolute URLs, root-relative URLs and Laravel `storage/` media paths.

## Admin fixes included
- Analytics crash fixed: `order_items.line_total` is now used instead of the non-existent `order_items.total` column.
- Analytics, Audit Log and WooCommerce Migration Center now degrade safely with a setup notice if their required migration table has not yet been installed rather than throwing a 500 page.
- Admin Users also has a safe table readiness check.
- Admin layout now renders success/error flash messages consistently.
- Product variant schema compatibility fix adds the `stock` and `sort_order` columns used by the Phase 02 catalog code and copies existing `stock_quantity` values into `stock` where appropriate.
- Product-card component now supports normalized database product data and fixes the related-products rendering issue on the product page.

## Run after merging
From `E:\ScentsByAamirLaravel\frontend`:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan storage:link
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Do not run `migrate:fresh` on an existing development database.

## Admin access
- Login: `http://127.0.0.1:8000/admin/login`
- Dashboard: `http://127.0.0.1:8000/admin/dashboard`

## Activating a DB product on the storefront
In Admin > Products, create/edit a product and set **Status = Active**. Add a price, stock/variants and at least one image URL/path. Once at least one active DB product exists, the storefront catalog begins using the real database catalog instead of the static fallback.

## Next phase
Storefront Integration Phase 02: real cart + wishlist variant IDs/SKUs, stock validation and persistent checkout-ready cart structure.

# Scents by Aamir — Admin Phases 06–09

This checkpoint continues the same Laravel application and preserves storefront + Phases 01–05.

## Phase 06 — Media + SEO
- Media Library uploads to Laravel public storage
- Alt text and file metadata
- SEO redirects (301/302)

## Phase 07 — Store Configuration
- General store settings
- Shipping zones/rates/free-shipping thresholds
- Payment-method activation and test-mode controls
- Public `GET /api/v1/store/config`

## Phase 08 — Operations
- Commerce analytics summary
- Top-selling product report
- Admin users / basic roles
- Audit-log viewer foundation

## Phase 09 — WooCommerce Migration Center
- Dedicated one-time migration UI
- WooCommerce REST API connection test
- Select products/categories/customers/orders/media
- Durable import-run and source-to-local mapping tables
- Current implementation intentionally creates safe migration runs; high-volume production import processing should execute through queued jobs so HTTP requests do not time out.

## Run manually
```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Do not replace `.env`. `vendor` and `node_modules` are intentionally excluded from the checkpoint.

After these admin phases, the next major work is storefront database/API integration, checkout/account integration, payment-provider credentials, production WooCommerce importer jobs, QA, and deployment.

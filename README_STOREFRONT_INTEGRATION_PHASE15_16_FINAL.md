# Scents by Aamir — Storefront Integration Phase 15 + 16 FINAL

## Phase 15 — Production hardening
- Added global security response headers.
- Added `scents:doctor` production-readiness diagnostic.
- WooCommerce importer model table-name bug fixed (`WooCommerceImportRun`/`Map` now explicitly use `woocommerce_*` tables).
- WooCommerce CLI command now rejects literal `RUN_ID`, checks migrations, and gives actionable errors.
- Existing checkout stock validation, transaction-safe order placement, coupon controls, inventory restore, auth middleware, CSRF and throttling remain preserved.

## Phase 16 — Final QA / deployment readiness
- Vite bundle warning addressed with vendor chunk splitting and lazy Three.js loading. Three.js is only downloaded when `[data-three-atmosphere]` exists.
- Final deployment/QA command checklist included below.

## WooCommerce command — important
First run migrations:

```powershell
php artisan migrate
```

Then create a migration run in **Admin > WooCommerce Migration Center**. Use the numeric run ID shown there. Example if the run ID is 1:

```powershell
php artisan woocommerce:import 1 --key=ck_REAL_KEY --secret=cs_REAL_SECRET
```

Do **not** type the placeholder `RUN_ID` literally.

The previous SQL error referring to `woo_commerce_import_runs` was caused by Laravel's automatic Eloquent table naming for `WooCommerceImportRun`. The actual migration table is `woocommerce_import_runs`; this final phase explicitly pins the model to the correct table.

## Final local commands

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan scents:doctor
php artisan serve --host=127.0.0.1 --port=8000
```

Never run `migrate:fresh` against the real store database.

## Production environment checklist
Use production values, not these as secrets/examples:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

Configure real MySQL, SMTP, WooCommerce credentials only in `.env` on the server. `.env` is intentionally excluded from this ZIP.

## Final QA flow
1. Home/shop/product/collection/search/finder pages.
2. Customer registration/login/profile/address.
3. Product variant stock -> cart -> coupon -> gifting -> checkout.
4. COD order creation and inventory deduction.
5. Bank transfer reference/receipt -> admin approve/reject.
6. Admin order status updates -> customer notifications/emails.
7. Cancellation restores inventory exactly once.
8. Contact/newsletter/track-order.
9. CMS/journal/navigation/SEO redirects.
10. WooCommerce import test using a real numeric migration run ID.

This checkpoint is the cumulative final integration package through Phase 16.

## One-command PowerShell WooCommerce migration
This final checkpoint also includes `scripts/woocommerce-import.ps1`.

Set these values in `.env`:

```env
WOOCOMMERCE_URL=https://your-wordpress-site.com
WOOCOMMERCE_CONSUMER_KEY=ck_your_real_read_key
WOOCOMMERCE_CONSUMER_SECRET=cs_your_real_read_secret
```

Then from the Laravel project root run:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\woocommerce-import.ps1
```

Or keep credentials out of `.env` and pass them for that run:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\woocommerce-import.ps1 `
  -Url "https://your-wordpress-site.com" `
  -Key "ck_REAL_KEY" `
  -Secret "cs_REAL_SECRET"
```

The script runs the safe pending migrations (including repair of missing `woocommerce_import_runs` / `woocommerce_import_maps`), creates a new import run automatically, and imports categories, products, variants, customers, orders and product media. Import operations use update-or-create/mapping logic so missing rows are inserted and existing matching rows are updated rather than truncating the Laravel database.

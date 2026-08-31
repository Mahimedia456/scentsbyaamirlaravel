# Scents by Aamir — Storefront Integration Phase 07 + 08

## Phase 07 — CMS, Journal, Navigation & SEO
- Published Admin CMS pages now override matching static information pages while config remains a safe fallback.
- Published Journal posts are rendered from the database; existing config journal remains fallback until DB content is published.
- `main_header` navigation created in Admin can replace the primary mega-menu links without redesigning the menu.
- SEO meta title/description from CMS/Journal are rendered in storefront pages.
- Admin SEO redirects now execute on storefront GET requests (admin/api excluded).

## Phase 08 — Customer/Order polish
- Customer order history is paginated.
- Added protected individual order detail page with ownership check.
- Improved status/payment/line-item visibility while preserving the accepted account visual system.
- Existing Phase 05/06 checkout, COD, bank transfer, receipt verification, inventory deduction and admin synchronization are preserved.

## Run manually
```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

## CMS activation
Admin → Pages: publish a page using a matching slug such as `shipping`, `returns`, `privacy`, `terms`, `cookies`, `accessibility`, `gift-wrapping`, or `personalized-message`.

Admin → Navigations: create/activate key `main_header` to drive the large primary menu links.

Admin → Journal: publish posts to replace the config fallback journal.

# Phase 02 — Collections + Loader Logo

## Scope completed

- Restored the real `logo-02.png` inside the global page loader.
- Removed the temporary SBA text mark from the loader.
- Explicit loader logo dimensions prevent the source logo from expanding unexpectedly.
- Loader is available on desktop and mobile.
- Rebuilt `/collections` as a premium collection landing page.
- Added Men / Women / Unisex direct catalogue entry points.
- Added fragrance-family discovery links.
- Existing database collections remain the source of truth.
- Unknown/future database collections are rendered safely instead of being hidden.
- Rebuilt individual `/collections/{slug}` pages.
- Collection detail hero typography is controlled and no longer oversized.
- Existing product cards and collection-product assignments are preserved.
- Existing header and footer are untouched.
- No database migration is required for this phase.

## Phase 02 image folder

`public/images/collections/`

Expected files:

- `collections-hero.webp`
- `signature-worlds.webp`
- `nocturnal.webp`
- `light-studies.webp`

See `PHASE02_COLLECTIONS_IMAGE_PROMPTS.md`.

## Local verification

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
npm run build
php artisan serve
```

Test:

- `/collections`
- `/collections/signature-worlds`
- `/collections/nocturnal`
- `/collections/light-studies`
- Men link → `/shop?audience=men`
- Women link → `/shop?audience=women`
- Unisex link → `/shop?audience=unisex`
- Oud / Floral / Fresh / Woody / Amber family links
- Loader should show the real Scents by Aamir logo, not `SBA`.

Do not push production until the local pages and mobile layout are approved.

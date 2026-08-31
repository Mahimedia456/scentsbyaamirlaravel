# Scents by Aamir — Frontend Phases 06–08

Complete checkpoint preserving Phases 01–05.

## Phase 06 — Distinct Fragrance Worlds
- each product now carries its own theme data
- background / surface / ink / accent values
- world image
- world statement
- mood
- per-product layout mode: offset / cinema / light
- reusable `product-world.blade.php`
- backend-ready data structure for future product theme controls

## Phase 07 — Search + Fragrance Finder
- `/search`
- instant Alpine product search
- name + family matching
- responsive search results
- `/fragrance-finder`
- 3-step guided flow
- mood
- intensity
- occasion
- recommendation edit UI

## Phase 08 — Ingredients
- `/ingredients`
- premium material library
- stock image-led ingredient cards
- `/ingredients/{slug}`
- ingredient editorial detail pages
- related fragrance grids
- Oud, Amber, Rose, Musk, Cedar, Neroli seeded in frontend config

## Important
Stock image URLs and theme values remain in:
`config/storefront.php`

Later backend integration can replace this config data without changing the visual component architecture.

## Manual run

```powershell
cd E:\ScentsByAamirLaravel\frontend
composer install --prefer-dist
npm install
npm run dev
```

Second terminal:

```powershell
php artisan serve
```

Useful URLs:
- `/product/memory-01`
- `/product/velvet-oud`
- `/search`
- `/fragrance-finder`
- `/ingredients`
- `/ingredients/oud`

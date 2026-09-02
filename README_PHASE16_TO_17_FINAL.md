# Scents by Aamir — FINAL Laravel Frontend Checkpoint

This ZIP preserves Phases 01–15 and completes Phases 16–17.

## Phase 16 — Accessibility + Performance + Responsive QA
- semantic `main` content target
- keyboard skip-to-content control
- stronger `:focus-visible` states
- reduced-motion safeguards
- touch/coarse-pointer safeguards
- mobile spacing/button sizing polish
- responsive product-stage aspect ratio
- offline status notice
- lazy-loading/async decoding applied to secondary imagery
- Three.js hidden for reduced-motion users
- WebGL render work reduced while document is hidden
- dynamic product / ingredient / journal routes now return proper 404s
- custom luxury 404 page
- theme-color metadata

## Phase 17 — Final Frontend Polish
- final house CTA/edit on homepage
- global premium trust/service strip
- account link surfaced in header
- final typography and interaction refinements
- preserved stock-media architecture
- preserved backend-ready per-product theme data
- complete frontend route structure
- frontend roadmap marked complete

## Important architecture note
Product images, world images and temporary theme colors still live in:
`config/storefront.php`

During backend integration these fields can be supplied from database/API records without redesigning the frontend component architecture.

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

## Core QA URLs
- `/`
- `/shop`
- `/collections`
- `/product/memory-01`
- `/product/velvet-oud`
- `/search`
- `/fragrance-finder`
- `/ingredients`
- `/ingredients/oud`
- `/journal`
- `/wishlist`
- `/checkout`
- `/account`
- `/account/orders`
- `/gifting`
- `/services`

Frontend roadmap: 17 / 17 complete.

# Phase 08 + 09 — Gifting / Tester Boxes + Live Search

## Phase 08 — Gifting
- `/gifting` is now controller-driven instead of a static view.
- Tester/discovery products are automatically detected from the live catalogue using tester/sample/discovery wording.
- Existing tester-box products therefore appear automatically without hardcoding product IDs.
- Full-size gift edit uses live featured catalogue products.
- Gifting page redesigned with:
  - premium hero
  - discovery/tester set grid
  - how-to-discover editorial
  - full-size gift edit
  - gift presentation section
  - gift wrapping + personalized message links
  - fragrance finder CTA
- No product-specific manual mapping was added.

## Phase 09 — Search refinement
- Existing `/search` now supports AJAX live search.
- 320ms debounce prevents a request on every keystroke.
- Results update without full page reload.
- URL updates as search text changes.
- Graceful full-page fallback if AJAX fails.
- Search works across:
  - display name
  - imported full product name
  - family
  - audience
  - description
  - story
  - notes
  - category
- Search suggestions include real Scents by Aamir product names plus discovery terms.
- Empty-search discovery shortcuts added.

## Images
See:
`PHASE08_09_GIFTING_SEARCH_IMAGE_PROMPTS.md`

Folder:
`public/images/gifting/`

Files:
- `gifting-hero.webp`
- `tester-box-editorial.webp`
- `gift-wrap.webp`

Search intentionally requires no custom image.

## Product finalization
The full 26-product sitemap-based image/background/note final setup remains deferred until the end of storefront phases.

## No migration required

## Local verification

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
npm run build
php artisan serve
```

Test:
- `/gifting`
- confirm tester boxes are picked from actual catalog
- click every tester/full-size product
- `/search`
- type slowly and confirm results change without full reload
- search `Dark Seduction`
- search `Smoky Chic`
- search `oud`
- search `women`
- search `unisex`
- clear search
- mobile search
- gift wrapping and personal-message links

Do not push until locally approved.

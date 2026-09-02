# Scents by Aamir — Storefront Polish Phase 01

## Scope

Phase 01 finalizes the Fragrances / Catalogue page while preserving the existing Scents by Aamir header, footer, Laravel architecture and product database integration.

## Completed

- Premium catalogue hero with safe custom artwork fallback.
- Controlled luxury typography; no oversized imported SEO product titles in cards.
- All / Men / Women / Unisex top navigation.
- New and House Favourites edits.
- Functional GET-based catalogue search.
- Functional fragrance-family filtering.
- Functional collection filtering.
- Functional in-stock filter.
- Functional minimum/maximum price filters.
- Functional sort: Featured, Newest, A–Z, price ascending, price descending.
- Desktop filter panel.
- Mobile filter drawer.
- Result count and empty-state UX.
- Product card display names clean imported `- Inspired by ...` SEO suffixes while product URLs/data remain unchanged.
- Existing real Laravel product images remain the source for catalogue cards.
- Home final typography version included in this checkpoint.

## Custom Image

Generate one image only for this phase:

`public/images/catalog/fragrances-hero.webp`

See `PHASE01_CATALOG_IMAGE_PROMPTS.md` for the exact prompt and placement rules.

## Local verification

From the Laravel project root:

```powershell
php artisan optimize:clear
npm run build
php artisan serve
```

Open:

`http://127.0.0.1:8000/shop`

Test:

- All Fragrances
- Men
- Women
- Unisex
- New
- House Favourites
- Search
- Family filter
- Collection filter
- In stock
- Min/max price
- Every sort option
- Mobile filter drawer
- Product card links

Do not push until the local catalogue is visually approved.

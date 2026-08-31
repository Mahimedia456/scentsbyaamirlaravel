# Phase 06 + 07 — About / Our House + Journal

## Phase 06
- Added named `/about` route.
- Added dedicated premium About / Our House page.
- Added DB/config fallback through `StorefrontContentService`.
- Existing `pages` table can later override the About fallback content if a published `about` page is created in admin.
- Material, house philosophy, wearability, values and ritual sections added.
- Header/footer unchanged.

## Phase 07
- Journal landing page redesigned.
- Published DB journal posts remain the source of truth.
- Existing admin-created journal content continues to work.
- Featured story + editorial grid.
- Journal article detail typography normalized so titles are not oversized.
- Journal article fallback artwork support.
- Cross-links to About, Ingredients and Finder.
- No migration required.

## Images
See:
`PHASE06_07_ABOUT_JOURNAL_IMAGE_PROMPTS.md`

Folders:
- `public/images/about/`
- `public/images/journal/`

All Phase 06/07 custom artwork is bottle-free.

## Product imagery
The 26-product final image/world setup is intentionally deferred until the end of the storefront phases.

## Local verification

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
npm run build
php artisan serve
```

Check:
- `/about`
- `/journal`
- at least one `/journal/{slug}`
- About → Ingredients
- About → Finder
- About → Journal
- Journal → About / Ingredients / Finder
- mobile layouts
- header/footer remain unchanged
- real logo loader remains intact

Do not push production until local approval.

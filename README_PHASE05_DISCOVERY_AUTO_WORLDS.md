# Phase 05 — Discovery / Fragrance Finder + Automatic Product Worlds

## Major Phase 04 correction

Per-product custom art is no longer required.

The PDP now automatically:
- reads real product name/family/story/notes;
- detects an appropriate visual world;
- loads a reusable world background from `public/images/product-worlds/`;
- overlays the real product image above that background;
- uses text/database notes rather than requiring a notes image;
- uses one reusable `ritual.webp` for the ritual section.

This solves the scalability issue for 20+ products and future catalogue additions.

## Automatic worlds

- `signature.webp`
- `dark.webp`
- `oud.webp`
- `floral.webp`
- `fresh.webp`
- `gourmand.webp`
- `amber.webp`
- `spicy.webp`
- `woody.webp`
- `ritual.webp`

## Phase 05 Finder

- 4-step guided finder:
  1. Mood
  2. Intensity
  3. Occasion
  4. Men / Women / Unisex / Any
- AJAX recommendations without full page reload.
- Browser URL updates with the selected finder answers.
- Recommendations are scored from the live database catalogue.
- Recommendation cards explain matching terms.
- Search page polished and now searches audience too.
- Cross-links to Ingredients, Families and full catalogue.

## Finder image

- `public/images/discovery/finder-hero.webp`

See:
`PHASE05_DISCOVERY_AUTO_PRODUCT_WORLDS_PROMPTS.md`

## No migration required

Phase 05 does not require a new database migration.

## Local verification

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
npm run build
php artisan serve
```

Test:
- `/fragrance-finder`
- all four finder steps
- AJAX result update
- restart finder
- recommendation product links
- `/search?q=oud`
- `/search?q=women`
- multiple product detail pages
- confirm different products pick different automatic world backgrounds
- confirm actual product image appears above the background
- confirm Notes are text/data based and no per-product notes image is required
- confirm ritual uses reusable background
- mobile PDP and finder

Do not push until locally approved.

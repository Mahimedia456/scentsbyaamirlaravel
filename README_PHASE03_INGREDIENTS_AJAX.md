# Phase 03 — Ingredients + Families + AJAX Catalogue Tabs

## Included

### Shop correction requested after Phase 01
- All Fragrances / Men / Women / Unisex / New / House Favourites are now centered on desktop.
- Tabs use AJAX and update the fragrance results without a full page reload.
- Browser URL is updated with `history.pushState`.
- Browser Back/Forward reloads the corresponding catalogue edit through AJAX.
- If AJAX fails, navigation falls back to a normal full-page request.
- Loading state added while a new edit is fetched.
- Existing filters and sorting remain available.
- Mobile keeps horizontal tab scrolling.

### Phase 03
- `/ingredients`
- `/ingredients/{slug}`
- `/families`
- `/families/{slug}`
- Ingredient library expanded to:
  - Oud
  - Rose
  - Amber
  - Citrus
  - Sandalwood
  - Jasmine
  - Vanilla
  - Spices
- Families:
  - Fresh
  - Floral
  - Woody
  - Oud
  - Amber
  - Spicy
- Ingredient/family product grids use the existing live catalogue service.
- Existing database product data remains the source of truth.
- No database migration is required in this phase.

### Loader
- Phase 02 real `logo-02.png` loader remains included.
- SBA text loader is not restored.

## Image location

`public/images/ingredients/`

See:

`PHASE03_INGREDIENTS_FAMILIES_IMAGE_PROMPTS.md`

All Phase 03 imagery is intentionally bottle-free.

## Local testing

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
npm run build
php artisan serve
```

Check:
- `/shop`
- click All / Men / Women / Unisex / New / House Favourites and verify NO full-page reload
- browser Back / Forward
- `/ingredients`
- all 8 ingredient detail pages
- `/families`
- all family detail pages
- mobile tab horizontal scroll
- filters and sort still work
- loader uses real logo

Do not push until local approval.

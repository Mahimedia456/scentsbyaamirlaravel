# Scents by Aamir — Phase 16 + 17 FINAL

## Phase 16 — Global QA / SEO / Performance foundation
- Added central SEO defaults.
- Layout now has fallback title, description, robots, canonical, OpenGraph and Twitter card metadata.
- Added `/sitemap.xml`.
- Preserved existing header/footer identity.
- Existing mobile/desktop storefront remains intact.

## Phase 17 — Final header mega menu + product finalization framework
- Mega menu rebuilt to auto-fit within viewport height.
- No vertical scrolling inside desktop mega menu.
- No content cut-off at bottom.
- Large navigation and Mood / Material / Services are visible in the same viewport.
- Responsive compression uses `min(730px, calc(100dvh - header))`.
- Hover/focus changes editorial preview image only; it does not scroll the menu.
- Reuses existing custom Scents by Aamir images:
  - catalog/fragrances-hero.webp
  - collections/signature-worlds.webp
  - discovery/finder-hero.webp
  - ingredients/ingredients-hero.webp
  - ingredients/oud-ingredient.webp
  - journal/journal-hero.webp
- No new mega-menu image generation required.
- Added `config/product-finalization.php` as the final 26-product override map.
- Confirmed known product slugs received safe initial world mappings.
- Live DB product data still remains the source of truth for names, prices, stock, images, descriptions and notes.

## Important for the 26 products
The user-provided product sitemap contains 26 exact products. Final product-by-product imagery/content should be verified against the live imported catalogue before adding any unconfirmed slug-specific overrides.

## Local verification
```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
npm run build
php artisan serve
```

Check:
- desktop mega menu on 1366x768
- desktop mega menu on 1920x1080
- laptop short viewport
- no inner menu scrollbar
- Mood / Material / Services remain visible
- hover Fragrances / Collections / Finder / Ingredients / Oud / Journal preview
- mobile header/menu
- `/sitemap.xml`
- home/shop/PDP/cart/checkout/account/orders/wishlist/support

# Scents by Aamir — Header + Mobile + Product + Cart/Stock Final Fix

## A. Desktop header / mega menu
The old header had TWO menu systems:
1. the original large local overlay
2. the newer Phase 17 mega-menu component

The Menu button was still opening the old overlay, which is why Mood / Material / Services were cut off.

This package removes that conflict:
- desktop Menu now opens the real viewport-fit mega menu
- the menu uses the full available height below the announcement/header
- no inner desktop scrolling
- short-laptop viewport CSS compresses type/gaps automatically
- Mood, Material and Services remain visible
- hover changes only the existing SBA editorial preview image
- only previously-created SBA images are reused

## B. Mobile header
Mobile now has its own visible header + drawer:
- hamburger
- centered SBA logo
- Cart
- Fragrances / Collections / Finder / Ingredients / Journal / Gifting
- Materials
- Search / Account / Wishlist / Support
- Finder image tile
Mobile drawer may vertically scroll on very short phones; the desktop mega menu does not.

## C. Product page
The PDP no longer injects generic `world_image` / reusable product-world artwork into the top product gallery.

Top gallery priority:
1. exact `public/images/products/{slug}/hero.webp` if you create it
2. actual imported Woo/Laravel product images
3. no fake/dummy gallery image

Desktop gallery is now:
- one dominant hero image
- up to two real supporting images
instead of four giant equal panels.

Lower Notes / World / Story imagery is also exact-only:
- `notes.webp`
- `world.webp`
- `story.webp`
If these do not exist, the page does not show a random reusable dummy visual.

## D. How many images per perfume?
For a normal fragrance create **4 final images**:

1. `hero.webp` — exact SBA bottle reference YES
2. `notes.webp` — bottle NO; exact note ingredients only
3. `world.webp` — bottle NO; scent atmosphere
4. `story.webp` — bottle NO; editorial mood/story

Save under:
`public/images/products/{exact-slug}/`

Run:
```powershell
php artisan storefront:prepare-product-artwork
```

Each real Laravel product gets:
- `PROMPTS.md`
- `manifest.json`

The prompt file is generated from that product's actual imported description/story/notes/variants.

Tester/discovery boxes use the exact box/sample reference instead of a full perfume bottle.

## E. 50 ML / Out of Stock / Add to Cart
The storefront now reads the effective stock from BOTH:
- modern `stock`
- legacy `stock_quantity`

This matters because the early schema/import history contained both columns.

The catalog and cart validation:
- prefer an in-stock real variant
- do not default to a zero-stock 100 ML variant
- use real selected variant id / SKU
- validate current DB stock before cart totals

### Audit stock
```powershell
php artisan storefront:audit-stock
```

### Repair legacy stock mismatch
Only run after checking the audit:
```powershell
php artisan storefront:audit-stock --fix-legacy
```

This copies positive `stock_quantity` into `stock` only where `stock` is currently zero.

## F. Local test
```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan storefront:audit-stock
npm run build
php artisan serve
```

Check:
- desktop header at 1366x768
- desktop header at 1920x1080
- Mood / Material / Services visible without menu scroll
- mobile header at 390px and 430px width
- mobile drawer
- product page uses real imported images only
- 50 ML in-stock variant is selected
- Add to bag opens cart and retains the product
- quantity cannot exceed stock

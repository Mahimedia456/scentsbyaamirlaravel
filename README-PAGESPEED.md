# Scents by Aamir — Mobile PageSpeed Final Fix

Base reviewed: user's current `frontend(9).zip`.

## What this patch fixes

- First homepage hero is server-visible immediately instead of waiting for Alpine `x-cloak`.
- Adds high-priority preload for the first LCP hero.
- Adds 768px/1200px responsive WebP homepage images.
- Adds 480px/768px responsive product-card hero images for all current product hero folders.
- Product cards now use `srcset` + `sizes` so mobile does not download 1800px hero artwork.
- Optimizes `logo-02.png` and `logo.png` from 4198px-wide originals to 1200px-wide optimized PNG while preserving filenames.
- Removes Google Fonts `@import` from critical CSS.
- Cormorant Garamond is desktop-only; mobile uses the existing premium serif fallback without the Google Fonts render-blocking request.
- GSAP + ScrollTrigger + Lenis are removed from the mobile initial JS path and loaded dynamically only on desktop after critical rendering.
- Header scroll updates are requestAnimationFrame-throttled.
- Adds explicit image dimensions for footer/logo/hero/product-card images.
- Keeps desktop luxury motion but prevents mobile from paying its initial parse/execution cost.
- Adds archive/database patterns to `.gitignore` to prevent another large ZIP Git push regression.

## Install

Extract this ZIP directly over:

`E:\ScentsByAamirLaravel\frontend`

Allow overwrite/merge.

Then run:

```powershell
cd E:\ScentsByAamirLaravel\frontend

php artisan optimize:clear

npm ci
npm run build

php artisan view:cache
php artisan view:clear
php artisan optimize:clear
```

Then deploy with your normal production deployment flow.

## Production after deploy

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan view:cache
```

## Important

This patch targets the exact Lighthouse items supplied in the mobile PageSpeed report:
image delivery, LCP discovery/render delay, render-blocking Google Fonts, unused motion JavaScript, image dimensions and small forced-reflow pressure.

No database schema/content changes are included in this PageSpeed patch.

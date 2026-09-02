# Scents by Aamir — Mobile PageSpeed FINAL V2

This is a self-contained replacement patch based on the user's current frontend.

## Pass 1 improvements already included
- responsive product and homepage WebP variants
- optimized logo files
- mobile-friendly `srcset` / `sizes`
- desktop-only deferred GSAP / ScrollTrigger / Lenis
- Google Font removed from mobile critical path
- explicit image dimensions
- LCP hero preload

## V2 fixes
The first PageSpeed pass reduced image-delivery estimated savings from about 920 KiB to about 357 KiB, confirming the responsive-image patch was active.

The remaining mobile score was still held back by two implementation details:

1. A later CSS rule re-enabled `.house-page-loader` on small screens, overriding an earlier mobile `display:none`.
2. The first mobile hero still lived inside the Alpine carousel (`x-show` + timer), keeping JavaScript in the critical LCP path.

V2:
- forces the page loader completely off on mobile
- removes loader visibility/animation from mobile LCP
- renders the first mobile hero as plain server HTML with no Alpine visibility dependency
- keeps the luxury auto-rotating carousel only on tablet/desktop
- disables hero image transition transforms on mobile
- preserves all desktop visual behavior

## Install

Extract/merge into:

`E:\ScentsByAamirLaravel\frontend`

Then:

```powershell
cd E:\ScentsByAamirLaravel\frontend

php artisan optimize:clear

npm ci
npm run build

php artisan view:cache
php artisan view:clear
php artisan optimize:clear

.\deploy-production.ps1
```

If `npm ci` is interrupted with SIGINT, run it again and let it finish; SIGINT is an interrupted process, not a Laravel compilation error.

## After deployment
Run PageSpeed again in a fresh test. Lighthouse scores vary between runs, so compare LCP/FCP/TBT and warnings, not only the headline number.

No database changes are included.

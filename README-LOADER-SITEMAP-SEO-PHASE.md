# Scents by Aamir — Loader + Dynamic Sitemap SEO Phase

This patch intentionally does NOT replace the current `routes/web.php` or
`resources/js/app.js` from the ZIP. Instead, the installer command patches the
current files in place so later Journal/account/checkout work is preserved.

## 1. Loader fix

Root cause of the visible delay:
the existing JavaScript showed the loader on navigation but also scheduled a
2.6-second `reveal()` fail-safe. If the next Laravel response/navigation took
longer than that, the loader disappeared while the old page was still visible.

The v2 loader removes that normal navigation hide timer. After a real same-site
link/form navigation begins, the loader remains visible until the next document
reaches `load` or `pageshow`.

The Blade loader has only a 12-second emergency safety valve for abnormal browser
or third-party resource failures.

## 2. Dynamic XML sitemaps

Routes installed:

- `/sitemap.xml` — sitemap index
- `/sitemap-pages.xml`
- `/sitemap-products.xml`
- `/sitemap-collections.xml`
- `/sitemap-journal.xml`

Products:
- active storefront products only
- DB `updated_at` becomes `<lastmod>`

Collections:
- active collections with active products
- DB `updated_at` becomes `<lastmod>`

Journal:
- `published` only
- future scheduled posts excluded
- DB `updated_at`/publish date becomes `<lastmod>`

Pages:
- public editorial/store routes only
- ingredient and fragrance-family detail pages included
- admin/account/cart/checkout/wishlist/search/API/internal URLs excluded

A category sitemap is intentionally not generated because the current storefront
does not have canonical `/category/{slug}` landing routes. Indexing filtered
`/shop?category=...` URLs would create weak/duplicate canonical targets.

## 3. robots.txt

Adds `public/robots.txt` with:
- public storefront allowed
- admin/account/checkout/cart/wishlist/search/API excluded
- `Sitemap: https://scentsbyaamir.com/sitemap.xml`

## Local installation

Merge the ZIP into:

`E:\ScentsByAamirLaravel\frontend`

Then run:

```powershell
cd E:\ScentsByAamirLaravel\frontend

composer dump-autoload
php artisan optimize:clear
php artisan site:install-seo-navigation
php artisan optimize:clear
npm run build
php artisan seo:sitemap-status
```

Add to `.env`:

```env
SITEMAP_BASE_URL=https://scentsbyaamir.com
```

Then validate in the browser:

- http://127.0.0.1:8000/sitemap.xml
- http://127.0.0.1:8000/sitemap-pages.xml
- http://127.0.0.1:8000/sitemap-products.xml
- http://127.0.0.1:8000/sitemap-collections.xml
- http://127.0.0.1:8000/sitemap-journal.xml
- http://127.0.0.1:8000/robots.txt

The XML `<loc>` values will intentionally use the production canonical domain
`https://scentsbyaamir.com`, even while checking locally.

## Loader validation

After `npm run build`:
1. open any storefront page;
2. click Journal/product/collection links;
3. loader should appear;
4. it must remain visible until the destination page is actually ready;
5. Back/Forward browser navigation must reveal the restored page normally.

## Git

Review first:

```powershell
git status
git diff -- routes/web.php resources/js/app.js
```

Then:

```powershell
git add app/Http/Controllers/Storefront/SitemapController.php
git add app/Console/Commands/InstallSeoNavigationPhase.php
git add app/Console/Commands/SitemapStatus.php
git add config/sitemap.php
git add routes/seo.php
git add routes/web.php
git add resources/js/app.js
git add resources/views/components/house/page-loader.blade.php
git add resources/views/store/sitemap-index.blade.php
git add resources/views/store/sitemap-urlset.blade.php
git add public/robots.txt

git commit -m "Add dynamic sitemaps and fix navigation loader timing"
git push origin main
```

## Production after Git deploy/pull

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan seo:sitemap-status
/usr/php84/usr/bin/php artisan view:cache
```

The Vite `public/build` assets must be deployed by the normal production build
pipeline because the loader fix changes `resources/js/app.js`.

Verify:

- https://scentsbyaamir.com/sitemap.xml
- https://scentsbyaamir.com/sitemap-products.xml
- https://scentsbyaamir.com/sitemap-journal.xml
- https://scentsbyaamir.com/robots.txt

After those return HTTP 200 with correct XML, add `scentsbyaamir.com` as a Google
Search Console Domain property, verify its DNS TXT record, then submit:

`https://scentsbyaamir.com/sitemap.xml`

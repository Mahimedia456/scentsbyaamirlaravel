# Scents by Aamir — Final Polished QA Checkpoint

This checkpoint consolidates the latest storefront/admin fixes into one Laravel project snapshot.

## High-priority fixes

- Rebuilt `resources/views/components/house/cart-drawer.blade.php` with safe Blade/Alpine syntax.
- Replaced Blade-conflicting Alpine `@error` syntax with `x-on:error`.
- Replaced the desktop mega menu with a deterministic, low-overhead Blade/Alpine implementation.
- Rebuilt `resources/views/admin/inventory/index.blade.php` in multiline Blade syntax.
- Product detail page now uses one generated `hero.webp` at the purchase/gallery top instead of duplicating imported Woo images.
- Removed the imported bottle overlay from the generated `world.webp` composition.
- Cart validation prefers `public/images/products/{slug}/hero.webp` when it exists.
- Cart state preserves its existing image if validation returns no image.
- Production deploy script builds Vite locally and uses one SSH session; StackCP does not need npm.
- Deployment now runs `php artisan view:cache` before Git push as a Blade compilation gate.

## Static QA performed before packaging

- PHP lint passed for application, bootstrap, config, database and route PHP files.
- Blade structural scan passed across 100 Blade templates for `@if`, `@foreach`, `@forelse`, `@auth`, `@guest`, `@isset`, etc.
- No Blade-conflicting `@error=` Alpine attributes remain.
- No escaped markup artifacts such as `\<div>` or escaped PHP identifiers remain in Blade templates.
- `node --check` passed for `resources/js/app.js` and `resources/js/admin.js`.
- Required route names used by the rebuilt mega menu/cart were checked in `routes/web.php`.

## After extraction

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan view:clear
php artisan view:cache
npm ci
npm run build
php artisan route:clear
php artisan route:cache
```

Then restart the local server:

```powershell
php artisan serve
```

Test at minimum:

- `/`
- `/shop`
- one product detail page
- header desktop mega menu
- mobile menu
- cart drawer
- `/cart`
- `/checkout` while logged in as customer
- `/admin`
- `/admin/inventory`
- `/admin/orders`
- `/admin/orders/create`

## Production deployment

Run the included root script:

```powershell
powershell -ExecutionPolicy Bypass -File .\deploy-production.ps1 -CommitMessage "Final polished QA release"
```

Do not run `config:cache` for this project at this checkpoint.

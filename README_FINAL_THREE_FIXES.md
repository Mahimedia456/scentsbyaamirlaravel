# Scents by Aamir — Final 3 Fixes

This checkpoint fixes the three regressions reported on 2026-09-01:

1. **Product detail gallery**
   - Restores all four generated product artworks.
   - Desktop uses one large interactive main image + four aligned thumbnails.
   - Gallery order: `hero.webp`, `notes.webp`, `world.webp`, `story.webp`.
   - Imported Woo images are fallback only.
   - Purchase panel is narrower and more compact so the imagery remains dominant without creating empty dead space.

2. **Header desktop menu**
   - Mega-menu state is now local to the header Alpine component (`megaOpen`) instead of depending on `$store.site`.
   - This removes the state/init regression that caused the Menu button to do nothing.
   - Escape, backdrop click, links and close button all close the same state.

3. **50 ML / stock**
   - Normal active simple perfumes remain 50 ML.
   - Tester products remain 5 ML.
   - Adds `php artisan storefront:repair-simple-products`.
   - For active untracked normal fragrances, legacy false availability is repaired to in-stock.
   - No variants are created.

## Local after extraction

```powershell
cd E:\ScentsByAamirLaravel\frontend

php artisan optimize:clear
php artisan storefront:repair-simple-products --dry-run
php artisan storefront:repair-simple-products
php artisan view:cache

npm ci
npm run build

php artisan serve
```

Test desktop:
- `/`
- Menu
- `/shop`
- Wild Intense product
- Night Rider product
- Cart

Do not run the old one-off PDP/header/cart patch scripts again.


## Production deployment

For the first deployment of this checkpoint, run the one-time product repair:

```powershell
powershell -ExecutionPolicy Bypass -File .\deploy-production.ps1 `
  -CommitMessage "Fix PDP gallery header menu and 50ML stock" `
  -RepairProducts
```

Later normal deployments do **not** need `-RepairProducts`:

```powershell
powershell -ExecutionPolicy Bypass -File .\deploy-production.ps1 `
  -CommitMessage "Update storefront"
```

`-RepairProducts` is intentionally optional so a future manually-out-of-stock product is not reactivated on every deployment.

# Scents by Aamir — Home, Loader & Product Upload Polish

This checkpoint builds on the final Laravel storefront integration + imported media hotfix.

## Included

- Home page now uses the real active Laravel/WooCommerce-imported catalog instead of silently falling back to demo products when no item is marked Featured.
- Home displays up to 8 real products.
- Premium editorial hero rebuilt so it looks intentional even before custom campaign banners are supplied. It uses the first available catalog product image as the visual focus and has a CSS-only luxury fallback.
- Global Scents by Aamir loader added across storefront, admin and admin login.
- Loader appears on initial load, same-origin page navigation and valid form submissions, with a fail-safe so a stalled asset cannot block the UI.
- Admin Product Create/Edit now supports direct multi-image upload.
- Supported uploads: JPG, JPEG, PNG, WebP; max 12 files, 8 MB each.
- Upload preview before save.
- Existing product image previews, primary-image selection, removal by clearing the path, and advanced external URL/path option.
- Uploaded files are stored on Laravel's `public` disk under `products/{product_id}` and are served through the existing `/media/...` route, so local Windows does not depend on the `public/storage` symlink.

## Run after replacing/merging

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

No new database migration is required for this polish checkpoint.

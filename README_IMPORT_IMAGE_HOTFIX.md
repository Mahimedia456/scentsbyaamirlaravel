# Imported Product Image + Content Hotfix

This checkpoint fixes WooCommerce media paths that previously became `storage/storage/...`, removes WooCommerce HTML tags from product content, and makes imported images work even when `public/storage` is not available as a symlink.

## After replacing the project files

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
php artisan storefront:repair-imported-catalog
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

You do not need to re-import WooCommerce data just to repair already imported images/content.

Future `php artisan woocommerce:sync` runs automatically store normalized media paths and execute the repair command after import.

## What changed

- Imported `product_images.path` is normalized to `products/imported/...`.
- Old paths beginning with `storage/` are repaired in-place.
- `/media/{path}` securely serves files from Laravel's public storage disk, so local Windows testing does not depend on `php artisan storage:link`.
- Product descriptions/story/notes/wear are rendered as plain text and old HTML tags are removed from imported DB content.
- Home campaign sections use imported catalog imagery where available, with original campaign images as fallback.
- Admin Media thumbnails also use the media route instead of the storage symlink.

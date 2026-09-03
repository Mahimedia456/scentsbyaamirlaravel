# Scents by Aamir — Journal Images Final

Journal-only update. No core storefront layout/routes/CSS/JS changes.

## Included
- Admin Journal create: featured image upload.
- Admin Journal edit: current image preview, replace upload, remove checkbox.
- Admin Journal list: featured image thumbnail column.
- Uploaded images stored on Laravel `public` disk under `journal/{slug}/`.
- WordPress imported featured images are downloaded locally when possible.
- If a WordPress featured image cannot be downloaded, its remote URL is preserved as a storefront/admin fallback instead of losing the image.
- Journal storefront listing and detail resolve local storage and remote image URLs.
- Existing categories and tags remain imported/editable.
- Comments remain excluded.
- Corrective taxonomy DB migration is included.

## Local

```powershell
cd E:\ScentsByAamirLaravel\frontend
composer dump-autoload
php artisan optimize:clear
php artisan migrate
php artisan storage:link
php artisan wordpress:import-journal --update-existing
php artisan journal:status
php artisan optimize:clear
php artisan view:clear
```

Test Admin -> Journal create/edit image upload/remove and `/journal` + article details.

## Production

Deploy/pull the code first, then:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force
/usr/php84/usr/bin/php artisan storage:link || true
/usr/php84/usr/bin/php artisan wordpress:import-journal --update-existing
/usr/php84/usr/bin/php artisan journal:status
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan view:cache
```

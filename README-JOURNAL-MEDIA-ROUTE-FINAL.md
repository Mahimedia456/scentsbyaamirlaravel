# Journal Media Route Final Fix

Root cause:
Journal images exist in `storage/app/public/journal/...`, but views were generating
`/storage/...` URLs. Local Windows `public/storage` is empty/unavailable, and remote
WordPress hotlink URLs can return Forbidden.

This patch uses the project's already-existing public Laravel media endpoint:

`/media/{path}` -> `StorefrontMediaController` -> `Storage::disk('public')`

Therefore the browser can read files directly from `storage/app/public/journal/...`
without relying on the `public/storage` symlink.

Also:
- Admin Journal thumbnails use `/media/...`
- Create/Edit featured image previews use `/media/...`
- Storefront Journal cards/details use `/media/...`
- Imported inline article images are saved with `/media/...` URLs
- Older article HTML containing `/storage/journal/...` is rewritten at render time
- No routes, core layout, CSS, JS, homepage, cart or checkout files are changed
- Comments remain excluded; categories/tags remain unchanged

Local:
php artisan optimize:clear
php artisan journal:media-check
php artisan wordpress:import-journal --update-existing
php artisan journal:media-check
php artisan view:clear

You can directly open a Browser URL printed by `journal:media-check`.

Production:
php artisan optimize:clear
php artisan migrate --force
php artisan wordpress:import-journal --update-existing
php artisan journal:media-check
php artisan view:cache

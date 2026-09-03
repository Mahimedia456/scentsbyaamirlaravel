# Scents by Aamir — Journal Storefront Images + Headings Final

This is a very small Journal storefront-only patch.

Changed only:
- resources/views/store/journal.blade.php
- resources/views/store/journal-detail.blade.php

Fixes:
1. `/journal` featured/card images now use the existing `/media/{path}` endpoint for
   locally stored Journal images instead of generating `/storage/...` URLs.
2. Legacy `storage/...` and `/storage/...` database values are normalized to `/media/...`.
3. Imported WordPress article `<h5>` and `<h6>` section headings now render as proper
   editorial headings instead of normal-looking body text.
4. Existing h2/h3/h4 styling remains intact.

No admin, importer, DB migration, routes, layout, CSS/JS, products, checkout or other modules are changed.

Local:
php artisan optimize:clear
php artisan view:clear

Then check:
http://127.0.0.1:8000/journal

A local DB image path such as:
journal/floral-charm-a-signature-scent-inspired-by-the-beauty-of-gucci-flora/featured-0f52cb23578e.webp

should render in the browser as:
http://127.0.0.1:8000/media/journal/floral-charm-a-signature-scent-inspired-by-the-beauty-of-gucci-flora/featured-0f52cb23578e.webp

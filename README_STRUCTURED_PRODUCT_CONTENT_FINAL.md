# Scents by Aamir — Structured Product Content Final

This fixes the Base Notes problem at the data-model level instead of trying to
keep parsing the entire product description on every page load.

## Product database

New `products` columns:

- `description` — Product Description only
- `story` — Story / short description
- `top_notes` — Top Notes only
- `heart_notes` — Heart Notes only
- `base_notes` — Base Notes only
- `wear` — Wear / usage guidance
- `notes` — legacy combined compatibility summary

## Woo import

When Woo copy contains:

```text
Top Notes: Bergamot, Ambroxan
Heart Notes: Woody Notes, Floral Accords
Base Notes: Musk, Patchouli, Ambergris
Product Description Bold Heat combines elegance and power...
```

Laravel stores:

```text
top_notes   = Bergamot, Ambroxan
heart_notes = Woody Notes, Floral Accords
base_notes  = Musk, Patchouli, Ambergris
description = Bold Heat combines elegance and power...
```

Product Description can no longer leak into Base Notes.

## Admin Create/Edit Product

Admin now has dedicated fields:

- Product description
- Story / short description
- Top notes
- Heart notes
- Base notes
- Wear / usage guidance

So all future products can be entered cleanly from the admin panel without Woo.

## Product Detail Page

The three note cards read only the structured note columns.

A new **Product Description** section appears after the fragrance Story + Notes
section and before the Scent World banner.

## Existing products

The migration automatically backfills existing Woo-imported records.

Run locally:

```powershell
cd E:\ScentsByAamirLaravel\frontend

php artisan optimize:clear
php artisan migrate

php artisan storefront:normalize-product-content --dry-run

php artisan view:clear
php artisan view:cache

npm ci
npm run build

php artisan serve
```

The migration already performs the backfill. The dry-run command is provided so
you can verify if any rows still need normalization.

Do not use `--force` unless you intentionally want to re-parse content that was
manually edited in Admin.

## Production

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force
/usr/php84/usr/bin/php artisan storefront:normalize-product-content --dry-run
/usr/php84/usr/bin/php artisan view:cache
```

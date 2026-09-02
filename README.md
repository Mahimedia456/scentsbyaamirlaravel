# Scents by Aamir — Fragrance Notes Plain-Text DB Fix

Built against the uploaded current `frontend(9).zip`.

## Root cause fixed

`SeedMasterProductContent.php` was converting Top / Heart / Base arrays with `json_encode()` before writing them into TEXT columns.

Old DB value:

`["Bergamot","Black Pepper"]`

New DB value:

`Bergamot, Black Pepper`

## What this patch changes

1. `SeedMasterProductContent.php`
   - Future 26-product master-content updates save fragrance notes as comma-separated plain text.
   - No more JSON arrays in `top_notes`, `heart_notes`, or `base_notes`.

2. `NormalizeFragranceNoteStorage.php`
   - New safe Artisan command that converts existing JSON-looking note values in the database to plain text.
   - Has `--dry-run` and `--backup` modes.

3. `ProductController.php`
   - Admin saves notes in plain-text format even if JSON is pasted into the textarea.
   - Legacy combined `notes` field is rebuilt from normalized values.

4. `StorefrontCatalogService.php`
   - Defensive frontend normalization. Even before the DB command is run, a JSON array value is displayed as comma-separated text.

5. `resources/views/admin/products/form.blade.php`
   - Defensive admin display. Existing JSON values are displayed as normal comma-separated notes.

## Merge location

Extract this ZIP into:

`E:\ScentsByAamirLaravel\frontend`

and allow overwrite/merge.

## LOCAL DATABASE — run in this order

```powershell
cd E:\ScentsByAamirLaravel\frontend
composer dump-autoload
php artisan optimize:clear

# Check what will change; DB remains untouched
php artisan storefront:normalize-fragrance-note-storage --dry-run

# Apply conversion and save pre-change JSON backup
php artisan storefront:normalize-fragrance-note-storage --backup

php artisan optimize:clear
```

After this, DB examples become:

- Top: `Bergamot, Black Pepper`
- Heart: `Patchouli, Spices, Floral Accords`
- Base: `Ambergris, Vanilla, Woody Notes`

## IF YOU ALSO WANT TO REAPPLY THE 26-PRODUCT MASTER CONTENT LOCALLY

The patched master command now saves notes correctly as plain text:

```powershell
php artisan storefront:seed-master-product-content --dry-run --strict
php artisan storefront:seed-master-product-content --strict --backup
php artisan optimize:clear
```

## PRODUCTION / SERVER DATABASE

After deploying these patched files to production:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12
/usr/php84/usr/bin/php artisan optimize:clear

/usr/php84/usr/bin/php artisan storefront:normalize-fragrance-note-storage --dry-run
/usr/php84/usr/bin/php artisan storefront:normalize-fragrance-note-storage --backup

/usr/php84/usr/bin/php artisan optimize:clear
```

If you also want to reapply the curated 26-product master content on production:

```bash
/usr/php84/usr/bin/php artisan storefront:seed-master-product-content --dry-run --strict
/usr/php84/usr/bin/php artisan storefront:seed-master-product-content --strict --backup
/usr/php84/usr/bin/php artisan optimize:clear
```

## Backup location

Before write, `--backup` stores the old DB values at:

`storage/app/master-content-backups/fragrance-notes-before-normalize-YYYYMMDD-HHMMSS.json`

The master-content command also creates its own pre-overwrite backup.

## No commerce data touched

The normalization command updates only:

- `top_notes`
- `heart_notes`
- `base_notes`

It does not change price, stock, SKU, slug, product name, product images, category, inventory, variants, status or other commerce data.

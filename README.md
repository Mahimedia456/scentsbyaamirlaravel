# ScentsByAamir Curated SEO Master Content — 26 Products

This package contains curated master storefront copy for 26 Scents by Aamir products:
- 23 normal fragrances
- 3 tester/discovery boxes

The Artisan command updates **content/SEO columns only**. It does not update price, sale price, stock, SKU, slug, product name, images, category, variants, inventory or other commerce data.

## Copy / merge into Laravel

Extract this ZIP into:

`E:\ScentsByAamirLaravel\frontend`

Allow the two package files to merge into:
- `app/Console/Commands/SeedMasterProductContent.php`
- `config/master_product_content.php`

Laravel 12 auto-discovers commands in `app/Console/Commands`.

## Local database — recommended safe sequence

From PowerShell:

```powershell
cd E:\ScentsByAamirLaravel\frontend

composer dump-autoload
php artisan optimize:clear

php artisan list | Select-String "seed-master-product-content"

# 1) Verify matching only — NO database change
php artisan storefront:seed-master-product-content --dry-run --strict

# 2) Apply locally and first create a JSON backup
php artisan storefront:seed-master-product-content --strict --backup

# 3) Clear runtime caches
php artisan optimize:clear
```

Expected result:
`Master content applied to 26 product(s).`

If strict dry-run reports a missing product, do not run the write command until that mismatch is checked.

## Production/server database

After these files are committed/pushed and your production source is updated:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

/usr/php84/usr/bin/php artisan optimize:clear

# Verify production matching first
/usr/php84/usr/bin/php artisan storefront:seed-master-product-content --dry-run --strict

# Apply to production DB with a JSON backup
/usr/php84/usr/bin/php artisan storefront:seed-master-product-content --strict --backup

/usr/php84/usr/bin/php artisan optimize:clear
```

The backup is written under:
`storage/app/master-content-backups/`

## One-line SSH production command from Windows

```powershell
ssh -o IdentitiesOnly=yes -i "C:\Users\hp\.ssh\scentsbyaamir_github_actions_nopass" scentsbyaamir.com@ssh.gb.stackcp.com "cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12 && /usr/php84/usr/bin/php artisan optimize:clear && /usr/php84/usr/bin/php artisan storefront:seed-master-product-content --dry-run --strict && /usr/php84/usr/bin/php artisan storefront:seed-master-product-content --strict --backup && /usr/php84/usr/bin/php artisan optimize:clear"
```

## Important behavior

The command dynamically checks which of these columns exist:
`subtitle`, `short_description`, `description`, `story`, `top_notes`, `heart_notes`, `base_notes`, `wear`, `notes`, `meta_title`, `meta_description`.

It only writes columns that actually exist on your `products` table.

Tester boxes intentionally receive no fabricated fragrance-note pyramid. Their top/heart/base arrays are empty because a tester box contains multiple scents rather than one composition.

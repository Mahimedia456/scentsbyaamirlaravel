# Tester-only size normalization hotfix

Scents by Aamir size rule:
- Tester products only: `5 ML`
- All other fragrance products: `50 ML`
- Discovery products are NOT treated as tester products.

This hotfix:
1. Changes Woo import size normalization so stale Woo `5ml` attributes cannot force a normal fragrance to display 5 ML.
2. Adds `storefront:normalize-product-sizes` to repair already-imported Laravel product rows.
3. Keeps simple-product inventory/cart behavior from the previous final phase.

## Production commands

After pushing the ZIP contents to `main` and deploying:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

/usr/php84/usr/bin/php artisan optimize:clear

/usr/php84/usr/bin/php artisan storefront:normalize-product-sizes --dry-run
/usr/php84/usr/bin/php artisan storefront:normalize-product-sizes

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan route:cache
/usr/php84/usr/bin/php artisan view:cache
```

Verify a normal fragrance such as Wild Intense:

```bash
/usr/php84/usr/bin/php artisan tinker --execute="\$p=App\Models\Product::where('slug','wild-intense')->first(); dump(\$p?->size_label);"
```

Expected:

```text
"50 ML"
```

Tester products should remain `5 ML`.

# Scents by Aamir — Simple Product Inventory + Cart Final

## Confirmed source structure
WooCommerce export confirms the catalog products are `simple`, not variable products.

The export also confirms:
- Woo numeric `Stock` is blank for the current simple products.
- `In stock?` is the real availability source.
- The original products that expose a Size attribute use `50ml`.
- Tester boxes are product bundles (`5 × 5 ML`), not 50 ML perfume bottles.

Therefore `product_variants = 0` is valid and should NOT be repaired by inventing variants.

## Inventory model
Laravel now supports both inventory modes:

### Simple availability mode
For Woo products with `manage_stock = false`:
- `track_inventory = false`
- `is_in_stock = true/false`
- numeric stock may remain `0`
- Add to Bag works while `is_in_stock = true`
- checkout does not decrement a fake numeric quantity

### Tracked quantity mode
For products where a real quantity is maintained:
- `track_inventory = true`
- `stock` / `stock_quantity` are validated
- order placement decrements stock
- cancelled orders restore stock
- `is_in_stock` follows numeric quantity

## Product size
`products.size_label` is now the source for simple products.

Woo attributes are imported when present.
Project standard fragrance fallback is `50 ML`.
Tester boxes are normalized to `5 × 5 ML`.

## Woo import
The importer now maps:
- `manage_stock` -> `track_inventory`
- `stock_quantity` -> `stock` + `stock_quantity`
- `stock_status` -> `is_in_stock`
- Woo Size/Volume/Capacity attribute -> `size_label`

## Admin
Product editor now treats a product as simple by default.

Base commerce includes:
- price
- compare-at price
- display size
- Track numeric quantity
- stock quantity
- Available / In stock

Variants are in an Advanced section and are only used when a product really has multiple variants.

## Production sequence
After pushing this phase to `main`:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

git fetch origin
git checkout main
git reset --hard origin/main

/usr/php84/usr/bin/php /usr/local/bin/composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force
```

Then re-run the Woo import once so existing products receive the real stock status and size:

```bash
/usr/php84/usr/bin/php artisan woocommerce:sync
```

Then audit:

```bash
/usr/php84/usr/bin/php artisan storefront:audit-stock
```

Expected for a normal in-stock simple fragrance:

```text
Wild Intense
  size=50 ML
  mode=simple availability
  numeric_stock=0
  is_in_stock=YES
  variants=none (correct for Woo simple product)
```

Tester boxes that are out of stock in Woo should show:

```text
mode=simple availability
is_in_stock=NO
```

Finally:

```bash
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan route:cache
/usr/php84/usr/bin/php artisan view:cache
```

Do not run `config:cache` yet because this project previously had direct env/config-cache compatibility issues.

## Storefront result
Normal simple fragrance:
- 50 ML (or exact imported `size_label`)
- product price
- in-stock status from the product itself
- `variant_id = null`
- Add to Bag works
- cart validates product availability
- checkout accepts the simple product

No fake 50/75/100 ML variants are created.

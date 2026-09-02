# Scents by Aamir — Storefront Integration Phase 02

## Scope
Phase 02 connects the existing cart and wishlist experience to the real Laravel catalog while preserving the accepted storefront and Admin Phases 01–09.

### Cart integration
- Cart lines now persist `product_id`, `variant_id`, SKU, selected size, current numeric price, stock and quantity.
- Same product/variant merges into one stable `line_key` instead of relying only on slug + size.
- Quantity controls respect live variant stock.
- Cart persists in localStorage and upgrades older stored cart data automatically.
- `POST /api/v1/store/cart/validate` revalidates active product, active variant, current price, SKU and available stock.
- Removed/deactivated/out-of-stock items are retained visibly but excluded from checkout totals.
- If requested quantity is greater than stock, the bag quantity is reduced to current stock and the customer is notified.
- Static catalog fallback remains operational until real active products exist in the database.

### Wishlist integration
- Wishlist now stores real `product_id` where available.
- `POST /api/v1/store/wishlist/resolve` refreshes current product name, price, image and stock availability.
- Removed/unavailable DB products are marked unavailable instead of silently producing stale commerce data.
- Static fallback wishlist items continue to work before catalog migration is complete.

### Product page
- Size buttons are backed by real variants.
- Variant price, SKU, stock and `variant_id` are passed to the cart.
- Out-of-stock variants cannot be selected.
- Quantity cannot exceed selected variant stock.
- Wishlist uses the real product ID when present.

### Checkout preparation
- Checkout revalidates the cart when opened.
- Summary uses validated numeric prices and only available lines.
- Checkout payload is already available in Alpine as `$store.commerce.checkoutItems` with product IDs, variant IDs, SKU and quantity.
- Payment shell is aligned to the project requirement: Cash on Delivery and Bank Transfer only. Actual order creation/payment verification comes in later checkout phases.

## New API endpoints
- `POST /api/v1/store/cart/validate`
- `POST /api/v1/store/wishlist/resolve`

## Apply
Merge this checkpoint into the Laravel project root, then run manually:

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

No new database migration is required specifically for Phase 02.

## Test checklist
1. Open an active database product.
2. Select an in-stock size and add it to the bag.
3. Confirm bag quantity +/- cannot exceed current stock.
4. Change variant price/stock in Admin, refresh/open bag and confirm current data is applied.
5. Add/remove wishlist items and verify they survive refresh.
6. Open `/checkout`; cart should be revalidated before showing totals.

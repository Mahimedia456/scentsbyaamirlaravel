# Scents by Aamir — Targeted Mobile / Checkout / Orders Bugfix

Base: the user's uploaded current `frontend(9).zip`.

This patch intentionally changes only the requested areas. It does NOT include the aggressive PageSpeed V2 changes and does not redesign unrelated storefront pages.

## Included fixes

1. Mobile header:
   - Account icon
   - Wishlist icon + count
   - Bag icon + count
   - Existing desktop header labels remain.

2. App-style mobile bottom navigation:
   - Home
   - Shop
   - Saved
   - Account
   - Bag
   - Wishlist and bag counts included.

3. Add to Bag feedback:
   - Desktop + mobile popup/toast
   - Bag/check icon on mobile
   - Full cart drawer is no longer forced open after every add.

4. Guest checkout:
   - Guests can continue directly from Shopping Bag to Checkout.
   - Guest contact + shipping details are collected securely.
   - Guest order success is protected by the active session.
   - Guest success page links to Track Order instead of authenticated My Orders.

5. Login/Register:
   - Show/Hide password controls.
   - Mobile shows the actual account form first.
   - 01 Faster checkout / 02 Order history / 03 Saved details benefits are hidden on mobile.
   - Desktop presentation remains.

6. Loader behavior:
   - Existing single global house page loader remains.
   - Checkout/cart button labels no longer turn into extra visual loaders such as “Placing order…” / “Checking…”.

7. Account / orders:
   - Recent order total uses `grand_total`.
   - Delivered orders are no longer incorrectly counted as “In progress”.
   - Orders page rebuilt to avoid mobile horizontal overflow.
   - Order detail uses the real final status `delivered` instead of nonexistent `completed`.
   - Mobile order journey no longer collapses into five unreadable columns.

8. Checkout mobile scrolling:
   - Delivery and Payment step buttons scroll back to the checkout panel below the fixed header.
   - Shipping/payment layouts are mobile-safe and no longer force horizontal overflow.

9. Notifications:
   - Header and “Back to account” stack safely on mobile.
   - Notification text and pagination are protected against horizontal overflow.

10. Tracking:
   - Customer can enter either Order Number OR courier Tracking ID.
   - Email or phone is still required for identity verification.
   - Shipping partner and Tracking ID appear in tracking result.
   - Tracking ID and shipping partner appear in customer Order Details.

11. Admin fulfilment:
   - New Shipping Partner field.
   - Choices: TCS, PostEx, Leopards, M&P, Other.
   - Tracking number remains editable.
   - Courier + tracking summary is visible in the fulfilment panel.
   - Adds one production-safe migration: `orders.shipping_partner`.

## Merge path

Extract/merge this ZIP into:

`E:\ScentsByAamirLaravel\frontend`

Allow overwrite of matching files.

## Local commands

```powershell
cd E:\ScentsByAamirLaravel\frontend

composer dump-autoload
php artisan optimize:clear
php artisan migrate

npm run build

php artisan view:cache
php artisan view:clear
php artisan optimize:clear
```

Then test at minimum:

- mobile header
- bottom navigation
- add to bag popup
- guest COD checkout
- logged-in checkout
- login/register password visibility
- My Account → Orders → Order Details
- Notifications
- Track Order with order number + email
- Track Order with tracking ID + email
- Admin → Orders → Shipping Partner + Tracking ID

## Production

Your existing deploy script already runs:

`php artisan migrate --force`

so the `shipping_partner` migration will be applied during normal deployment.

If running manually:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force
/usr/php84/usr/bin/php artisan view:cache
```

## Scope guard

No product content, SEO content, prices, stock, product images, category data, homepage design, desktop luxury styling, or unrelated admin modules are changed by this patch.

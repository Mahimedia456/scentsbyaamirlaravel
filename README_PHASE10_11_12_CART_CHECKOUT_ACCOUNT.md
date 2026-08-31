# Scents by Aamir — Storefront Phase 10 + 11 + 12

## Phase 10 — Cart
- Added `/cart` full shopping bag page.
- Existing localStorage/API validation architecture preserved.
- Quantity update, stock limit, remove, refresh and subtotal.
- Empty bag state.
- Cart drawer redesigned.
- Drawer now links to full bag + checkout.
- Guest cart is preserved while user signs in.

## Phase 11 — Checkout
- Existing server-side OrderPlacementService remains the source of truth.
- 3-step checkout polished:
  1. Contact & delivery address
  2. Shipping
  3. Payment / promo / gifting / order note
- Existing COD and Bank Transfer support preserved.
- Existing private payment receipt upload preserved.
- Existing promotion API preserved.
- Existing gift wrap/message support preserved.
- Rich order summary now shows actual cart lines.
- Checkout clearly redirects customer to account when delivery address is missing.
- No payment logic moved to the browser; final totals and stock remain server validated.

## Phase 12 — Customer Account
- Account overview redesigned.
- Order count, in-progress orders and unread notification summary.
- Recent orders.
- Profile editing.
- Default delivery address.
- Marketing preference preserved.
- Login/register redesigned.
- Intended redirect support added so a customer coming from Cart returns to Checkout after sign-in.

## Database
No migration required.

## Product finalization
The 26-product sitemap/image/note/background final mapping is still deferred to Phase 17.

## Local test

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
npm run build
php artisan serve
```

Test:
- `/cart`
- add 2 different products
- quantity + / -
- remove item
- refresh bag
- open/close cart drawer
- checkout as logged-out customer
- sign in and confirm intended checkout redirect
- `/checkout`
- address missing flow
- delivery selection
- COD
- Bank Transfer
- promo code
- gift wrap/message
- `/account`
- update profile
- update delivery address
- recent orders
- logout/login/register

Do not push production until local approval.

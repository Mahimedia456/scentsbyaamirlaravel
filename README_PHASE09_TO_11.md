# Scents by Aamir — Frontend Phases 09–11

Complete checkpoint preserving Phases 01–08.

## Phase 09 — Journal
- `/journal`
- premium editorial landing
- featured article
- editorial card grid
- dynamic `/journal/{slug}`
- long-form article layout
- central article data in `config/storefront.php`

## Phase 10 — Wishlist + Cart Drawer
- persistent Alpine commerce store
- localStorage cart
- localStorage wishlist
- product-card wishlist heart
- PDP wishlist action
- PDP Add to Bag
- live cart count in header
- right-side cart drawer
- subtotal calculation
- `/wishlist`

## Phase 11 — Checkout UI
- `/checkout`
- 3-step checkout flow
- contact/address
- delivery method
- payment method
- live order summary
- cart subtotal
- express delivery adjustment
- frontend-only payment/order shell, ready for backend integration later

## Manual run

```powershell
cd E:\ScentsByAamirLaravel\frontend
composer install --prefer-dist
npm install
npm run dev
```

Second terminal:

```powershell
php artisan serve
```

Useful URLs:
- `/journal`
- `/journal/memory-and-scent`
- `/wishlist`
- `/checkout`

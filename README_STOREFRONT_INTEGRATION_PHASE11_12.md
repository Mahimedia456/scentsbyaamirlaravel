# Scents by Aamir — Storefront Integration Phase 11 + 12

## Phase 11 — Promotions + Gifting
- Checkout promo-code UI with live preview.
- Final coupon validation is performed server-side against the live subtotal.
- Enforces active dates, global usage limits, per-customer usage limits, minimum order and maximum discount.
- Successful orders create `coupon_usages` and increment coupon usage atomically.
- Signature gift presentation toggle at checkout.
- Optional sender name and recipient gift message.
- Configurable `gift_wrap_fee` in Admin → Store Settings.
- Coupon, discount and gifting details are persisted on the order and visible to customer/admin.

## Phase 12 — Transactional Notifications + Commerce Hardening
- New customer notification center at `/account/notifications`.
- Notifications are created for order placed, order status changes, bank-payment approval and rejection.
- Notifications are ownership-protected and can be marked read.
- Checkout token/idempotency protection reduces accidental duplicate order creation.
- Cancelling an order from Admin restores product/variant inventory exactly once and records inventory adjustments.
- Existing stock locking, price recalculation, payment verification and transaction handling remain intact.

## Database changes
Migration: `2026_08_31_160000_storefront_integration_phase11_12_promotions_gifting_notifications.php`

Adds order fields for coupon/gifting/idempotency/restock tracking and creates `customer_notifications`.
Rollback intentionally keeps new order-history columns to avoid destructive commerce-history loss.

## Run after merge
```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Do not use `migrate:fresh` on an existing store database.

## Admin setup
- Admin → Promotions: create/enable coupon codes.
- Admin → Store Settings: set `gift_wrap_fee` (use `0` for complimentary gift wrapping).
- Admin → Orders: status changes now produce customer notifications; cancellation restores stock once.

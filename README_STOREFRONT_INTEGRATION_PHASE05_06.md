# Scents by Aamir — Storefront Integration Phase 05 + 06

## Phase 05 — Real Order Placement
- Checkout POST now creates real Laravel orders and order items.
- Server recalculates live catalog prices, shipping and inventory under a DB transaction.
- Product/variant stock is locked and decremented only when order creation succeeds.
- Inventory adjustment history records each storefront sale.
- Customer order history updates immediately; cart clears on success.
- Shipping free-threshold is rechecked on the server.

## Phase 06 — COD + Bank Transfer + Admin Payment Verification
- Only Cash on Delivery and Bank Transfer are exposed at checkout.
- Bank details are editable in Admin → Payments.
- Bank transfer requires a transaction/reference number; receipt upload is optional.
- Receipts are stored on Laravel's private local disk (not public storage).
- Admin order view can download the receipt and Approve/Reject bank payment.
- Approval marks payment Paid and confirms a pending order.
- Rejection stores the reason and marks payment Failed.
- Payment verification events are written to Audit Log when available.

## Run after merge
```powershell
php artisan optimize:clear
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Before testing Bank Transfer, configure Admin → Payments → Bank Transfer with bank name/account details and enable it.

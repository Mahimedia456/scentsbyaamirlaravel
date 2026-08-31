# Scents by Aamir — Order + Invoice + Cart/Checkout Hotfix

## Fixed: Admin Order Detail ParseError
`resources/views/admin/orders/show.blade.php` was completely rebuilt with balanced Blade directives.
The previous nested inline `@if ... @endif` structure that caused:

`syntax error, unexpected token "endif"`

has been removed.

The view also understands both:
- Laravel address keys (`address_line_1`, `region`, `postal_code`)
- legacy Woo address keys (`address_1`, `state`, `postcode`)

## Order lifecycle remains functional
Admin order detail supports:
- pending
- confirmed
- processing
- shipped
- delivered
- cancelled
- refunded

Payment:
- pending
- paid
- failed
- refunded

Status changes continue to:
- update the order
- restock tracked inventory on first cancellation
- send matching transactional customer email
- record audit events

## Invoice generation
New route:

`/admin/orders/{order}/invoice`

The order screen now has **Invoice / Print PDF**.

Invoice contains:
- invoice/order number
- issue date
- billing/customer details
- shipping address
- products
- SKU
- quantity
- unit price
- line totals
- discount
- shipping
- grand total
- payment status
- fulfilment status
- tracking

The invoice is A4 print-optimized. Use **Print / Save PDF** to generate the PDF from the browser without adding a fragile PDF package to Laravel.

## Admin-created orders
New:
- `/admin/orders/create`
- `POST /admin/orders`

Admin can create an order for an existing customer using active products.

Admin-created orders:
- create a normal real `orders` record
- create `order_items`
- use product live price
- copy customer's saved/default address
- support COD / bank transfer / manual payment
- support shipping fee + manual discount
- deduct tracked inventory
- log inventory movement
- trigger order placed notification/email
- generate invoice immediately

No fake product variants are introduced.

## Storefront cart/header fix
Header Cart is now a real `/cart` link with Alpine drawer enhancement:
- with JS: cart drawer opens
- if Alpine/JS fails: browser still navigates to `/cart`
- drawer gets explicit high z-index
- cart remains persisted in localStorage
- checkout cart is cleared after a successful order

Checkout remains:
1. customer sign-in/account
2. address
3. delivery
4. payment
5. order placement
6. success page

## Local commands after replacing files
```powershell
cd E:\ScentsByAamirLaravel\frontend

php artisan optimize:clear
php artisan view:clear
php artisan route:clear

npm install
npm run build

php artisan route:cache
php artisan view:cache
php artisan serve
```

## Test checklist
1. `/admin/orders/1` opens without Blade ParseError.
2. Change status and save.
3. Open Invoice / Print PDF.
4. `/admin/orders/create` creates a test order.
5. Confirm tracked inventory is deducted once.
6. Header Cart opens drawer.
7. Disable JS temporarily: Cart link still opens `/cart`.
8. Add real product to cart.
9. Cart → sign in → checkout.
10. Save address if needed.
11. Select shipping and payment.
12. Place order.
13. Success page clears local cart.
14. New order appears in admin and invoice works.

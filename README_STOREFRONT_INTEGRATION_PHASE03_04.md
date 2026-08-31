# Storefront Integration Phase 03 + 04

## Phase 03 — Customer Account & Authentication
- Dedicated `customer` Laravel session guard, separate from admin users.
- Customer register/login/logout.
- Real customer profile persistence.
- Default delivery address persistence via `customer_addresses`.
- Account orders page reads real orders linked to the logged-in customer.
- Protected account/checkout routes.

## Phase 04 — Checkout Address & Shipping
- Checkout pre-fills authenticated customer and default address.
- Active shipping methods come from Admin → Shipping / `shipping_zones`.
- Enabled payment method choices come from Admin → Payments.
- Cart remains live-price/live-stock validated from Phase 02.
- Order placement is intentionally reserved for Phase 05 (COD + bank transfer + transactional order/inventory write).

## Run
`php artisan optimize:clear`
`php artisan migrate`
`npm run build`
`php artisan serve --host=127.0.0.1 --port=8000`

Customer URLs:
- `/account/register`
- `/account/login`
- `/account`
- `/account/orders`
- `/checkout`

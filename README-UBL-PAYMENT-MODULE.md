# Scents by Aamir — UBL Hosted Card Payment Module

## What this module adds

- UBL hosted/redirection card checkout (`ubl_card`)
- Visa/Mastercard card details remain on the gateway page
- Registration -> hosted payment page -> 3D Secure -> ReturnPath -> Finalization flow
- server-side Finalization before an order is marked paid
- callback TransactionID matching
- OrderID and amount verification when UBL returns them
- idempotent payment attempts and retry flow
- guest checkout and customer checkout support
- COD and Bank Transfer remain unchanged
- `payment_transactions` audit table
- Admin -> Payments UBL enable/disable + recent transaction visibility
- `.env`-only gateway credentials
- `ubl:diagnose` command with optional outbound port-2443 connectivity test

## Official guide flow implemented

1. Registration JSON is posted to the EPG server.
2. Successful response must have `ResponseCode=0`, `TransactionID` and `PaymentPortal`.
3. Browser POSTs `TransactionID` to the returned PaymentPortal URL.
4. EPG returns the customer to the configured ReturnPath and posts TransactionID.
5. Laravel verifies that TransactionID matches the stored attempt.
6. Laravel sends a Finalization request.
7. Only Finalization `ResponseCode=0` can mark the order paid.

## Installation

Merge this ZIP at the Laravel project root. It is based on the locked targeted-bugfix checkout so guest checkout remains preserved.

Run locally:

```powershell
cd E:\ScentsByAamirLaravel\frontend
composer dump-autoload
php artisan optimize:clear
php artisan ubl:install
php artisan migrate
php artisan optimize:clear
php artisan route:list | findstr ubl
php artisan ubl:diagnose
```

Add the values from `UBL-SANDBOX-ENV.txt` to `.env`, then:

```powershell
php artisan optimize:clear
php artisan ubl:diagnose --connect
```

Then Admin -> Payments -> enable `Debit / Credit Card (UBL)`.

## Server commands

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12
composer dump-autoload
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan ubl:install
/usr/php84/usr/bin/php artisan migrate --force
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan route:list | grep ubl
/usr/php84/usr/bin/php artisan ubl:diagnose --connect
```

### If `ubl:diagnose --connect` gives cURL error 7 or timeout

The gateway uses outbound TCP port **2443**. Ask the hosting provider to allow outbound TCP 2443 from the PHP/server node to the UBL/EPG host. Do not disable SSL verification to work around a firewall problem.

## Sandbox values

The official UBL-linked integration PDF publishes these staging/demo settings:

- endpoint: `https://demo-ipg.ctdev.comtrust.ae:2443`
- Customer: `Demo Merchant`
- Store/Terminal: `0000` (or not required)
- Username: `Demo_fY9c`
- Password: public demo password in `UBL-SANDBOX-ENV.txt`
- success Visa: `4111111111111111`
- success Mastercard: `5555555555554444`
- insufficient funds Visa: `4012888888881881`
- do-not-honor Mastercard: `5105105105105100`
- sandbox expiry: any future month/year
- sandbox CVV: `123`

These values are TEST ONLY. The generic EPG demo merchant may not have every currency/payment capability enabled. If `PKR` is rejected, do not change the store order currency just to force a successful test; request UBL's merchant-specific Pakistan sandbox profile/credentials.

## Production cutover

When UBL issues live merchant credentials, replace only `.env` values (plus any UBL-provided merchant-specific Store/Terminal requirements):

```env
UBL_MODE=production
UBL_BASE_URL=https://ipg.comtrust.ae:2443
UBL_PUBLIC_URL=https://shop.scentsbyaamir.com
UBL_CUSTOMER=YOUR_UBL_CUSTOMER_ID
UBL_STORE=YOUR_UBL_STORE_IF_PROVIDED
UBL_TERMINAL=YOUR_UBL_TERMINAL_IF_PROVIDED
UBL_USERNAME=YOUR_UBL_USERNAME
UBL_PASSWORD=YOUR_UBL_PASSWORD
UBL_CURRENCY=PKR
UBL_VERIFY_SSL=true
```

Then:

```bash
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan ubl:diagnose --connect
```

Do not place production credentials in Git or Admin database.

## Testing checkout

1. Enable UBL in Admin -> Payments.
2. Add a product to cart.
3. Checkout as guest or signed-in customer.
4. Select `Debit / Credit Card (UBL)`.
5. Click `Place order & pay securely`.
6. Laravel creates a pending order and UBL transaction attempt.
7. You should reach the hosted gateway payment page.
8. Use the sandbox test card only in sandbox.
9. On return, Laravel calls Finalization.
10. The order becomes `paid + confirmed` only when the Finalization response succeeds and validation passes.

## Files intentionally not changed

- core storefront layout
- Journal importer/content
- sitemap module
- cart JS
- product catalog
- account/auth routes

The only checkout replacements are the locked-baseline CheckoutController, OrderPlacementService and checkout Blade with UBL additions.

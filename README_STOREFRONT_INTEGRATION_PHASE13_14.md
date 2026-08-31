# Scents by Aamir — Storefront Integration Phase 13 + 14

## Phase 13 — Transactional Email
- Order received email after a successfully committed order.
- Order confirmed / processing / shipped / delivered / cancelled / refunded emails.
- Bank transfer approved/rejected emails; rejection includes the admin reason.
- Contact-form acknowledgement email.
- Email delivery failures are reported to Laravel logs and never roll back a valid order/payment action.
- Local default is `MAIL_MAILER=log`; configure SMTP in production.

## Phase 14 — Production WooCommerce one-time importer
Admin: `/admin/woocommerce-import`

Imports selected data through WooCommerce REST API:
- categories
- simple + variable products
- variants/SKUs/prices/stock
- customers
- historical orders + line items
- product media copied into Laravel `storage/app/public/products/imported/...`
- per-run source→Laravel mapping and import statistics/errors

The importer uses update-safe matching (product/category slugs, customer email, `WC-{order number}`) and source mappings, so retrying a failed migration does not intentionally duplicate those entities.

### Browser import
Use **Run import now** for a small/test store.

### Production/large store
Create a CLI run in Admin, note the run ID, then:

```powershell
php artisan woocommerce:import RUN_ID --key=ck_xxx --secret=cs_xxx
```

Or temporarily set `WOOCOMMERCE_CONSUMER_KEY` and `WOOCOMMERCE_CONSUMER_SECRET` in `.env` and omit the options. Remove the keys after the one-time migration.

Imported WooCommerce customer passwords cannot be recovered from WordPress hashes for Laravel authentication. Imported customers receive an unusable random Laravel password; a password-reset/activation flow should be used before asking historical customers to sign in.

## Apply
```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
php artisan storage:link
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

No new database migration is required in Phase 13/14; existing WooCommerce import tables are reused.

## Before retiring WordPress
1. Back up WooCommerce + Laravel DB/files.
2. Test REST connection.
3. Run a test migration and inspect products/customers/orders/media.
4. Run the final migration during a write-free maintenance window.
5. Verify counts/totals and media locally.
6. Keep the old store read-only until final Phase 15/16 QA is signed off.

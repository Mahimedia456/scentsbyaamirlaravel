# Scents by Aamir — Enterprise Admin Phase 04

## Delivered

### Orders Operations Workspace
- enterprise KPI strip
- search by order, customer, email or tracking
- status, payment status, payment method and date filters
- payment + fulfilment visibility
- bulk order workflow:
  - confirm
  - processing
  - shipped
  - delivered
  - cancelled
- bulk status operations process customer transactional emails
- cancelled orders use existing inventory restock protection

### Enterprise Customer CRM
- KPI strip: all, active, inactive, unverified, archived
- search name/email/phone
- account-state, verification and marketing filters
- lifetime order value and order count
- customer profile with addresses
- order timeline
- internal notes
- activation / deactivation
- archive + restore
- historical orders preserved on archive
- marketing opt-in controls
- bulk lifecycle actions

### Customer account emails
Admin can resend a signed 48-hour activation link to an unverified customer.
Activation/deactivation status email is also supported.
All admin-triggered customer mail is logged into the admin notification center on success/failure.

### Database
Migration:
`2026_09_01_000100_admin_enterprise_phase04_customer_operations.php`

Adds:
- `customers.admin_archived_at`
- `customers.admin_archived_by`

No historical orders are deleted.

## Deployment
```powershell
cd E:\ScentsByAamirLaravel\frontend
git add .
git commit -m "Add enterprise admin phase 04 orders and customer CRM"
git push origin main
```

Server:
```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12
git fetch origin
git checkout main
git reset --hard origin/main

/usr/php84/usr/bin/php /usr/local/bin/composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force

npm ci
npm run build

/usr/php84/usr/bin/php artisan route:cache
/usr/php84/usr/bin/php artisan view:cache
```

Do not run `config:cache` yet.

## QA
1. `/admin/orders` filters and KPI cards.
2. Bulk-confirm a safe test order; verify customer email.
3. Bulk-cancel a safe test order; confirm stock is not double-restocked.
4. `/admin/customers` filters and KPI cards.
5. Open a customer and verify addresses/order timeline.
6. Deactivate then reactivate a test customer.
7. Resend activation to an unverified email.
8. Archive then restore a customer; historical orders must remain.
9. Check `/admin/notifications` for customer email success/failure.

## Next — Enterprise Admin Phase 05
- Inventory command center
- stock movement ledger
- low/out-of-stock operational views
- manual adjustment workflow
- coupons/promotions enterprise redesign
- collection/category merchandising polish

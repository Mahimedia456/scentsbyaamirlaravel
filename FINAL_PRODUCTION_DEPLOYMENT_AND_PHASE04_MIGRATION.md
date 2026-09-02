# Final Production Deployment + Phase 04 Migration

## Phase 04 migration on the StackCP server

Phase 04 introduced:

```text
database/migrations/2026_09_01_000100_admin_enterprise_phase04_customer_operations.php
```

It adds:
- `customers.admin_archived_at`
- `customers.admin_archived_by`

### Recommended command
Laravel tracks migrations in the `migrations` table, so the normal production command safely runs only migrations that have not already run:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force
```

### To run/check only the Phase 04 file

```bash
/usr/php84/usr/bin/php artisan migrate \
  --path=database/migrations/2026_09_01_000100_admin_enterprise_phase04_customer_operations.php \
  --force
```

### Verify migration status

```bash
/usr/php84/usr/bin/php artisan migrate:status
```

Look for:

```text
2026_09_01_000100_admin_enterprise_phase04_customer_operations
```

with status `Ran`.

## Final Phase 07–08 deployment

Local:

```powershell
cd E:\ScentsByAamirLaravel\frontend

git add .
git commit -m "Complete enterprise admin phases 07 and 08"
git push origin main
```

Server:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

git fetch origin
git checkout main
git reset --hard origin/main

/usr/php84/usr/bin/php /usr/local/bin/composer install \
  --no-dev \
  --no-interaction \
  --prefer-dist \
  --optimize-autoloader

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force

npm ci
npm run build

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan route:cache
/usr/php84/usr/bin/php artisan view:cache
```

Do not run `config:cache`.

## Final production QA

1. Admin login.
2. Super Admin can access Admin Users and Settings.
3. Create a Catalog Manager test user and confirm they cannot access system settings.
4. Reset the test admin password by email.
5. Product CRUD.
6. Order status email.
7. Customer activation email.
8. Inventory movement.
9. Coupon CRUD.
10. CMS + Media.
11. Analytics date filters and CSV exports.
12. Audit log records POST/PUT/PATCH/DELETE actions.
13. Mail Diagnostics sends successfully.
14. Customer registration → email activation → login.
15. Place a test order and verify customer + admin emails.

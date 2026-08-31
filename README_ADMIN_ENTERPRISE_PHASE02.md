# Scents by Aamir — Enterprise Admin Phase 02

## Delivered
Phase 02 upgrades the admin from a redesigned shell into an operational commerce console.

### Executive dashboard
- 30-day revenue
- 30-day orders
- average order value
- new customers
- pending/processing order count
- 30-day revenue chart without a third-party chart dependency
- order-status breakdown
- recent orders
- top products by revenue
- platform-health panel

### Global command search
Use `Ctrl+K` / `Cmd+K` from anywhere in admin.
Searches:
- products by name / SKU / slug
- orders by number / customer
- customers by email / name / phone

The search endpoint stays behind admin middleware.

### Persistent notification center
New `admin_notifications` table and system-alert service.
Automatic alerts currently include:
- SMTP not configured
- pending/processing orders
- unverified customer accounts
- low tracked inventory
- open support inquiries

Features:
- unread badge in topbar
- notification center page
- mark read
- mark all read
- dismiss
- resolve system alerts automatically when the condition clears

### Mail diagnostics
New System → Mail Diagnostics page:
- shows non-secret mail configuration
- never renders SMTP password
- can send a real branded test email
- success/failure also surfaces in the admin notification center
- Phase 03 order-email roadmap shown in admin

## Deployment
After replacing the Phase 01 project with this Phase 02 checkpoint:

```powershell
cd E:\ScentsByAamirLaravel\frontend

git add .
git commit -m "Add enterprise admin phase 02 dashboard notifications and mail diagnostics"
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
```

Build:

```bash
npm ci
npm run build
```

Final:

```bash
/usr/php84/usr/bin/php artisan route:cache
/usr/php84/usr/bin/php artisan view:cache
```

Do not use `config:cache` yet.

## Production checks
1. `/admin/dashboard`
2. press `Ctrl+K`
3. search a product name
4. open `/admin/notifications`
5. open `/admin/system/mail`
6. if real SMTP is configured, send a test message to an address you can inspect
7. confirm notification badge/read/dismiss flows

## Next
Enterprise Admin Phase 03:
- complete product/catalog CRUD redesign
- bulk product actions
- status/visibility workflow
- image/gallery management polish
- SEO/catalog panels
- order-created customer email
- admin new-order email
- payment and order-status transactional email engine

# Scents by Aamir — Enterprise Admin Phase 03

## Delivered

### Enterprise Product Catalog
Product management is now redesigned as a professional catalog workspace.

Index:
- KPI counts: all, active, draft, archived, featured
- search by name / SKU / slug
- status, category, availability and featured filters
- product image thumbnail
- price, size, stock state and media count
- bulk actions:
  - activate
  - draft
  - archive
  - feature / unfeature
  - mark in stock / out of stock
  - permanent delete
- duplicate product into a safe draft copy

Editor:
- core identity
- description
- fragrance story / notes / wear
- category
- collections
- simple product commerce controls
- standard size control
- optional numeric inventory
- SEO fields
- advanced variants kept optional
- media upload and gallery management
- explicit primary image selection
- explicit image removal
- danger-zone delete

Critical gallery fix:
Existing product images are no longer deleted/re-created on every save.
They remain untouched unless the administrator explicitly selects them for removal.

### Order Transactional Email Engine
`TransactionalMailService` is now included and wired into storefront checkout and admin order actions.

Customer emails:
- order placed
- confirmed
- processing
- shipped
- delivered
- cancelled
- refunded
- payment approved
- payment rejected
- payment failed

Admin email:
- new order alert

Order status changes from the admin order page trigger the appropriate customer email.
Tracking reference is included in the shipped email when present.
Bank payment approval/rejection continues to trigger customer notifications plus branded transactional mail.

Mail exceptions are caught and reported. An email failure does NOT corrupt or roll back a successfully placed order.
Success/failure is also surfaced in the admin notification center.

### Admin order page
- enterprise order summary
- customer/shipping details
- item table
- payment verification
- receipt download
- fulfilment workflow
- tracking field
- internal notes
- status-driven notification behavior

## Production email recipient
Set this in production `.env` if new-order alerts should go somewhere other than the sender address:

```env
ORDER_NOTIFICATION_EMAIL=contact@scentsbyaamir.com
```

The real SMTP values from Phase 01/02 are still required.

## Deployment

Local:

```powershell
cd E:\ScentsByAamirLaravel\frontend

git add .
git commit -m "Add enterprise admin phase 03 catalog CRUD and order email engine"
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

## Phase 03 QA
1. Open `/admin/products`.
2. Filter products.
3. Edit a product and save without changing its gallery; existing images must remain.
4. Upload a new image; old images must remain.
5. Explicitly remove one image and save.
6. Duplicate a product; duplicate must be draft.
7. Test bulk Draft / Active on safe products.
8. Open an order.
9. Change status to processing; customer receives processing email when SMTP works.
10. Add tracking and change to shipped; shipped email includes tracking.
11. Test bank-payment approval/rejection.
12. Place a storefront test order; customer receives order-received email and admin recipient receives new-order alert.
13. Review `/admin/notifications` for mail success/failure records.

## Next: Enterprise Admin Phase 04
- complete Orders workspace redesign
- order list bulk workflow / saved operational filters
- Customer enterprise CRUD
- customer activation/admin lifecycle controls
- resend activation / account status actions
- customer order timeline
- safe customer archive/delete strategy
- customer communication actions

# Scents by Aamir — Enterprise Admin Phase 05 + Phase 06

This checkpoint contains both requested phases and preserves Phase 01–04 work.

## Phase 05 — Inventory, Promotions & Merchandising

### Inventory Command Center
- tracked vs simple-availability inventory modes
- low stock / out of stock / in stock operational filters
- KPI strip
- manual movement workflow
- movement reasons and internal references
- movement ledger
- CSV export
- simple-product availability toggle
- tracked stock adjustment keeps `stock`, `stock_quantity`, `track_inventory`, and `is_in_stock` aligned
- existing order inventory/restock workflow remains intact

### Promotions / Coupons
- enterprise promotions list
- campaign search
- enabled / disabled / scheduled / expired filtering
- KPI cards
- percentage/fixed discounts
- minimum order / maximum discount
- total and per-customer usage limits
- start/end scheduling
- enable/disable action
- duplicate as disabled campaign
- edit and delete

### Categories + Collections
- professional CRUD redesign
- visibility and sort order
- direct product assignment
- category product movement safely updates `category_id`
- collection product assignment syncs pivot relation
- delete protections remain appropriate

## Phase 06 — CMS + Media

### Content Operations
New `/admin/content` publishing dashboard:
- page counts
- journal counts
- published content status
- media count
- navigation count
- recent edited pages/posts
- recent media

### Pages
- enterprise page list/editor
- draft / published / archived
- template selection
- scheduled publication timestamp
- SEO fields
- duplicate page as draft
- create / edit / delete remain available

### Journal
- enterprise editorial list/editor
- title, slug, eyebrow, excerpt, content
- featured media path
- publishing state/time
- SEO
- duplicate article as draft
- create / edit / delete

### Navigation
- professional menu workspace
- active/inactive menus
- 12-item editor
- label, URL, target
- create / edit / delete preserved

### Media Library
- multiple-file upload
- search by filename / alt / caption
- image/other filter
- media count and storage summary
- metadata editing
- alt text + captions
- file replacement
- permanent delete
- Laravel-managed disk cleanup on replacement/deletion

## Deployment

Local:
```powershell
cd E:\ScentsByAamirLaravel\frontend

git add .
git commit -m "Add enterprise admin phases 05 and 06 inventory merchandising CMS media"
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

Do not use `config:cache` yet.

## QA
1. `/admin/inventory`
2. filter tracked/simple/low/out
3. adjust a safe test product +1, then -1
4. export movement CSV
5. toggle simple availability on a test simple product
6. `/admin/coupons` create/edit/disable/duplicate/delete test campaign
7. category product assignment
8. collection product assignment
9. `/admin/content`
10. create/edit/duplicate page
11. create/edit/duplicate journal post
12. create/edit navigation
13. `/admin/media` upload multiple images
14. edit alt text/caption
15. replace a safe test image
16. delete a safe test media asset

## Remaining enterprise phases
- Phase 07: Admin users, roles, permissions, store/email/security settings, password reset/security workflows
- Phase 08: final enterprise polish, reporting/export, audit hardening, responsive QA, permission QA, transactional email QA

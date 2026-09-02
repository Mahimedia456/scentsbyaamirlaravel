# Scents by Aamir — Storefront Integration Phase 09 + 10

## Phase 09 — Live Search + Fragrance Finder
- `/search` now searches the real active catalog (with existing static fallback during migration).
- Search matches product name, family, category, description, story and notes.
- `/fragrance-finder` keeps the premium 3-step UI but recommendations are now selected from the live catalog using mood/intensity/occasion scent-term scoring.
- Finder results link to real product slugs and show live price/image/availability source.

## Phase 10 — Storefront Operations
- Contact form now creates real support inquiries in the database.
- Admin > Support can filter, read, status and annotate inquiries.
- Footer newsletter form now persists unique subscribers.
- Admin > Newsletter can review and subscribe/unsubscribe records.
- Track Order now securely resolves an order only when order number AND checkout email/phone match.
- Tracking view exposes order/payment/shipping/tracking status without exposing unrelated customer data.
- Contact page reads support email/phone from Store Settings.
- Store Settings expanded with store tagline and social URL fields.
- Public forms include validation, CSRF and rate limiting.

## Database
New tables:
- `contact_inquiries`
- `newsletter_subscribers`

Run only normal migrations; do not use `migrate:fresh`.

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

## URLs
Storefront:
- `/search?q=oud`
- `/fragrance-finder`
- `/contact`
- `/track-order`

Admin:
- `/admin/contact-inquiries`
- `/admin/newsletter`
- `/admin/settings`

## Next integration batch
Phase 11 + 12 should cover promotions/coupon checkout integration, gifting options, transactional customer notifications, and final commerce QA/hardening.

# Scents by Aamir — Enterprise Admin Phase 01

## Phase 01 delivered
- Admin is still inside the SAME Laravel application.
- Admin now has an isolated frontend bundle:
  - `resources/css/admin.css`
  - `resources/js/admin.js`
- Storefront continues using `app.css` / `app.js`.
- Enterprise responsive admin shell:
  - grouped sidebar
  - mobile drawer
  - sticky topbar
  - reusable cards/buttons/status/table styles
  - separate admin login experience
  - system readiness on dashboard
- Existing admin routes/modules are preserved.
- Customer email activation is active:
  - registration creates an inactive customer
  - branded activation email is sent
  - signed activation link expires after 24 hours
  - activation sets `email_verified_at` and `is_active`
  - customer can only sign in after activation
  - resend activation endpoint is rate-limited
- Sender default changed to `contact@scentsbyaamir.com`.

## SMTP requirement
Real SMTP credentials are NOT invented or hardcoded.

Production `.env` must contain the actual mailbox/provider details:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=<REAL_SMTP_HOST>
MAIL_PORT=<REAL_SMTP_PORT>
MAIL_USERNAME=<REAL_SMTP_USERNAME>
MAIL_PASSWORD=<REAL_SMTP_PASSWORD>
MAIL_FROM_ADDRESS=contact@scentsbyaamir.com
MAIL_FROM_NAME="Scents by Aamir"
```

After editing production `.env`:

```bash
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan config:clear
```

Do not use `config:cache` yet for this project.

## Deployment
```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12
git fetch origin
git checkout main
git reset --hard origin/main

/usr/php84/usr/bin/php /usr/local/bin/composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force
```

Build admin/storefront assets:

```bash
npm ci
npm run build
```

Final:

```bash
/usr/php84/usr/bin/php artisan route:cache
/usr/php84/usr/bin/php artisan view:cache
```

## Email rollout across the 8 enterprise phases
Email is NOT postponed until the admin is finished.

Phase 01:
- SMTP/mail architecture
- branded activation template
- registration activation flow
- resend flow

Phase 02:
- notification center UI
- email delivery visibility / diagnostics
- admin quick actions

Phase 03:
- order placed confirmation
- admin new-order notification
- payment received / failed notifications
- order status notifications: confirmed, processing, shipped, delivered, cancelled, refunded

Phase 04:
- customer lifecycle notifications
- customer account/admin-assisted messages

Phase 05:
- inventory/operations alerts where useful

Phase 06:
- CMS-managed transactional email branding/content controls where safe

Phase 07:
- email/store settings
- admin users/roles/security notifications
- password reset flows

Phase 08:
- full email QA, deliverability checks, responsive templates, retry/failure audit

## Remaining enterprise admin phases
1. Foundation + account activation (this ZIP)
2. Dashboard + global UX / reusable enterprise components
3. Product catalog CRUD + order notification integration
4. Orders + customers enterprise CRUD/workflows
5. Inventory + collections + taxonomy
6. CMS + media manager
7. Admin users + roles + settings + security
8. Final polish, reports, bulk actions, exports, responsive QA and email QA

By Phase 08 the goal is full professional CRUD wherever hard-delete is appropriate.
Orders/payment/audit records use safe lifecycle actions rather than destructive hard delete.

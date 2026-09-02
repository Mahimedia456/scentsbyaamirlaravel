# Scents by Aamir — Fully Final Furnished Checkpoint

This checkpoint consolidates the latest storefront/admin fixes.

## Product artwork
The PDP resolves product artwork before use and tolerates older folder-name differences.
Dedicated note images:
- top-notes.webp
- heart-notes.webp
- base-notes.webp

Shopping gallery:
- hero.webp first
- official/imported Laravel/Woo product gallery after it
- note/world/story artwork is not mixed into the shopping gallery

## Cart
Fixed:
- Add to Bag for normal simple/untracked perfume products
- cart immediately persists locally
- cart drawer opens without triggering the global navigation loader
- page-loader ignores Alpine-prevented links
- every navigation loader display now has a failsafe
- commerce store initializes immediately
- localStorage failures cannot crash cart
- normal simple products use a safe UI quantity ceiling and server validation

## Email flows
Wired:
- registration activation email
- resend activation
- customer forgot password
- customer password reset
- admin password reset/setup
- order placed customer email
- new order admin email
- confirmed
- processing
- shipped
- delivered
- cancelled
- refunded
- payment approved
- payment rejected
- payment failed
- admin mail diagnostic/test

Important: real inbox delivery requires SMTP credentials. If production still has
MAIL_MAILER=log, Laravel will only write email content to logs.

Use `PRODUCTION_MAIL_ENV_ADD_ONLY.txt`.

## Server screenshot / artisan path
The StackCP SSH login starts in the account home directory, not the Laravel directory.
Therefore `php artisan ...` from `~` returns "Could not open input file: artisan".

Correct server directory:

`/home/sites/41b/8/81d92349b7/public_html/shop/laravel12`

Use `server-production-check.ps1` locally if you want this path handled automatically.

## Local QA commands

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan view:clear
php artisan view:cache
php artisan route:list --path=account
php artisan storefront:mail-check
npm ci
npm run build
php artisan serve
```

## Live SMTP test after production env is configured

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12
/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan config:clear
/usr/php84/usr/bin/php artisan storefront:mail-check --to=orders@scentsbyaamir.com
```

Do not use `config:cache`.

MAIL TLS FIX

Your current env can keep:

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.stackmail.com
MAIL_PORT=587

This build normalizes MAIL_SCHEME=tls to Symfony's supported `smtp` scheme,
which uses STARTTLS automatically on port 587.

After extracting:

php artisan optimize:clear
php artisan config:clear
php artisan storefront:mail-check --to=orders@scentsbyaamir.com

Do not run config:cache.

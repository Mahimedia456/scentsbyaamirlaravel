# Scents by Aamir — Final Production `.env`

This is the final production environment structure after Enterprise Admin Phase 08.

## Values confirmed for this project

```env
APP_NAME="Scents by Aamir"
APP_ENV=production
APP_KEY=<KEEP_THE_EXISTING_PRODUCTION_APP_KEY>
APP_DEBUG=false
APP_URL=https://shop.scentsbyaamir.com
APP_TIMEZONE=Asia/Karachi

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=sdb-53.hosting.stackcp.net
DB_PORT=3306
DB_DATABASE=laravel12-35303035980e
DB_USERNAME=scentsbyaamir
DB_PASSWORD=<KEEP_THE_EXISTING_PRODUCTION_DATABASE_PASSWORD>

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.scentsbyaamir.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.stackmail.com
MAIL_PORT=587
MAIL_USERNAME=contact@scentsbyaamir.com
MAIL_PASSWORD=<PASSWORD_OF_contact@scentsbyaamir.com_MAILBOX>
MAIL_FROM_ADDRESS=contact@scentsbyaamir.com
MAIL_FROM_NAME="Scents by Aamir"

ORDER_NOTIFICATION_EMAIL=contact@scentsbyaamir.com
```

### About the two secret placeholders
Do **not** replace the existing production `APP_KEY` or database password with guessed values.
The SMTP mailbox password is also not stored in the repository and cannot be derived from the email address. Use the actual password for the `contact@scentsbyaamir.com` mailbox from StackCP.

The Stackmail SMTP host is `smtp.stackmail.com`; standard authenticated SMTP supports port 587 with TLS/STARTTLS. Username is the full mailbox address.

## After changing `.env`

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan config:clear
/usr/php84/usr/bin/php artisan route:cache
/usr/php84/usr/bin/php artisan view:cache
```

Do **not** run `config:cache` for this project yet.

Then open:

```text
https://shop.scentsbyaamir.com/admin/system/mail
```

Send a test email and confirm it arrives.

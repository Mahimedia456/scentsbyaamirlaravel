# Scents by Aamir — Admin/API/Database Phase 01

This checkpoint preserves the accepted storefront and adds the first production backend/admin foundation.

## Included
- Laravel 12 MySQL database configuration for the supplied StackCP host/database/user.
- Database migrations for admin users, categories, collections, products, variants, product images, customers, orders and order items.
- Environment-driven super-admin seeder; no real password is committed to this ZIP.
- Protected `/admin` area with session authentication, role/active checks, login/logout and premium dashboard UI.
- Dashboard database status and starter metrics.
- `GET /api/v1/health` endpoint that verifies the API and live database connection.
- Small demo catalog seeder for database validation.

## Local placement
Extract the contents of this ZIP directly into the Laravel project root — the directory that contains `artisan`, `composer.json`, `routes`, `resources`, `app` and `public`.

Recommended Windows location if starting clean:
`E:\ScentsByAamir\`

After extraction the file should be:
`E:\ScentsByAamir\artisan`
NOT:
`E:\ScentsByAamir\ScentsByAamir_LARAVEL_ADMIN_API_DB_PHASE01\artisan`

## Environment
Copy `.env.example` to `.env`. Set `DB_PASSWORD` to the database password supplied separately by the project owner. Replace `ADMIN_EMAIL` and `ADMIN_PASSWORD` before seeding.

## Commands to run manually
The project owner runs commands manually by project convention:

```bash
composer install
npm install
php artisan key:generate
php artisan config:clear
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

For production deployment use `APP_ENV=production`, `APP_DEBUG=false`, the production `APP_URL`, and HTTPS.

## Verify
- Storefront: `/`
- Admin login: `/admin/login`
- Dashboard after login: `/admin/dashboard`
- API/database health: `/api/v1/health`

## Next phase
Admin Catalog Management: product/category/collection CRUD, variants, stock, media upload, fragrance metadata and API endpoints.

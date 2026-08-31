# Scents by Aamir — Fresh Laravel Frontend Restart Phase 01

This is a NEW clean storefront checkpoint. Do not merge it with the rejected old Phase 01–04 design.

## Included
- Laravel 12 / PHP 8.3
- Tailwind 3.4
- Alpine.js
- Lenis
- GSAP / Three.js dependencies prepared
- bootstrap/cache directory fix
- campaign-style announcement bar
- transparent-over-hero utility header
- slide-out mega navigation
- search strip
- modern home page
- full catalogue page `/shop`
- filter drawer
- sorting
- product grid
- large service footer
- mobile responsive structure

## Design
The project uses luxury fragrance/fashion ecommerce references for layout principles only. Branding, copy, components and product worlds are original to Scents by Aamir.

## Manual install

```powershell
cd E:\ScentsByAamirLaravel\frontend
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
```

Terminal 1:
```powershell
npm run dev
```

Terminal 2:
```powershell
php artisan serve
```

Open:
http://127.0.0.1:8000

If `.env` already exists, do not overwrite it.

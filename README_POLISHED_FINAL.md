# Scents by Aamir — Polished FINAL Laravel Frontend

This checkpoint includes the full frontend roadmap (01–17) plus the final header/footer correction pass.

## Final correction pass

### Header
- header now remains readable on both white and dark pages
- warm-white translucent fixed header
- black typography/icons on every page
- subtle scroll shadow
- improved mobile spacing
- less cramped desktop controls
- centered wordmark protected from side navigation crowding

### Menu
- redesigned slide-out menu
- reduced visual congestion
- better hierarchy and spacing
- smaller, more controlled editorial navigation typography
- dedicated House Edit visual panel
- separate Finder panel
- Account / Gifting / Services utility row
- mobile-friendly full-width drawer behavior

### Footer
- fully redesigned black luxury footer
- large house wordmark
- newsletter separated from navigation
- proper 2-column / 4-column responsive information architecture
- links now stack vertically instead of appearing as one crowded line
- clearer customer care / shop / house services / legal structure
- cleaner copyright/localization row

### Additional correction
- duplicate nested `<main>` element from previous checkpoint fixed
- existing product/theme/image architecture preserved
- all frontend phases remain included

## Manual run — recommended Windows sequence

Open Terminal 1:

```powershell
cd E:\ScentsByAamirLaravel\frontend

composer install --prefer-dist
npm install
npm run dev
```

Keep Terminal 1 open.

Open Terminal 2:

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan key:generate
php artisan optimize:clear
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000
```

### If `.env` does not exist

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### If Vite says dependencies are already installed
That is fine. Keep `npm run dev` running and use the PHP URL above.

### Production frontend build later

```powershell
npm run build
```

For local development, use `npm run dev`.

## Core QA pages

```text
/
 /shop
 /collections
 /product/memory-01
 /product/velvet-oud
 /search
 /fragrance-finder
 /ingredients
 /journal
 /wishlist
 /checkout
 /account
 /account/orders
 /gifting
 /services
```

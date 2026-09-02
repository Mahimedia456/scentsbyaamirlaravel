# Scents by Aamir — Frontend Restart Phase 04

Complete checkpoint preserving Restart Phases 01–03.

## Phase 04 — Collections
- full `/collections` landing page
- signature hero collection
- two secondary collection campaign cards
- material editions grid
- fragrance finder CTA
- dynamic `/collections/{slug}` detail route
- Signature Worlds detail
- Nocturnal detail
- Light Studies detail
- collection-specific hero atmospheres
- collection-specific product grids
- quick view preserved on collection detail
- previous homepage, mega menu, catalogue and Composer Windows fix preserved

## Manual run

```powershell
cd E:\ScentsByAamirLaravel\frontend
composer install --prefer-dist
npm install
npm run dev
```

Second terminal:

```powershell
php artisan serve
```

Open:

```text
http://127.0.0.1:8000/collections
```

Examples:

```text
http://127.0.0.1:8000/collections/signature-worlds
http://127.0.0.1:8000/collections/nocturnal
http://127.0.0.1:8000/collections/light-studies
```

# Scents by Aamir — Enhanced Frontend Checkpoint (Phases 01–05)

This checkpoint does NOT start Phase 06 yet.

## Main enhancement
Phases 01–05 now use stock-image URLs stored in central Laravel config data.

File:
`config/storefront.php`

It contains:
- campaign image URLs
- product card image URLs
- product world image URLs
- per-product theme colors
- product names / family / price / badges

## Important architecture
The `theme` array inside each product is temporary mock data.

Later backend/API/database product records can provide:
- background color
- surface color
- ink/text color
- accent color
- card image
- hero/world image
- other product-world media

The Blade product page already consumes those values through CSS custom properties, making future backend replacement straightforward.

## Enhanced areas
- homepage hero uses stock campaign image
- collection blocks use stock images
- fragrance finder module uses stock image
- material/journal visual uses stock image
- catalogue cards use data-driven stock images
- PDP hero/world uses data-driven stock image
- PDP secondary media uses stock image
- image links live in one data location instead of scattered Blade files
- existing Composer Windows fix preserved

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

Phase 06 is intentionally still pending.

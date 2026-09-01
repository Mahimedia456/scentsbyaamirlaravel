# Scents by Aamir — Creative PDP + Header Mega Menu + 50 ML Stock Final

This checkpoint fixes the three reported regressions.

## 1. Header mega menu
Root cause was Alpine scope: `<x-house.mega-menu />` was rendered *after* the
header's root `x-data` element had already closed. The menu used `megaOpen`,
but that variable did not exist in the component's scope.

The component now renders inside the same header `x-data`, so:
- Menu button opens it.
- Close button closes it.
- backdrop closes it.
- Escape closes it.
- navigation links close it.

## 2. Product detail
The top PDP was redesigned using the uploaded references as layout inspiration
without copying their branding.

It now uses:
- large premium rounded gallery stage;
- the exact 4 generated product artworks;
- Signature / Notes / Scent world / Ritual thumbnails;
- click-to-switch main gallery;
- compact premium purchase card;
- clearer price / 50 ML / stock / quantity / cart hierarchy;
- fragrance profile cards;
- editorial scent introduction;
- three note cards;
- product-specific scent-world artwork.

Existing lower Notes / Story / Wear, materials, ritual and related products
remain in place.

## 3. 50 ML + simple-product stock
This catalogue is Woo simple products:
- normal active fragrances: 50 ML;
- tester products: 5 ML;
- no fake variants.

For untracked active normal fragrances the storefront treats availability as
in stock, matching the imported catalogue behavior. Tester products retain
their explicit availability state. Tracked products still use numeric stock.

## Local

```powershell
cd E:\ScentsByAamirLaravel\frontend

php artisan optimize:clear
php artisan view:clear
php artisan view:cache

npm ci
npm run build

php artisan serve
```

Test:
- `/`
- desktop `Menu`
- `/shop`
- Wild Intense PDP
- Night Rider PDP
- cart

Do not run older one-off header/PDP patch scripts after installing this build.

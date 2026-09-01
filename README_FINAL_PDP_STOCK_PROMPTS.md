# Scents by Aamir — Final Product Page + Stock + Prompts

## Product page
- Final cream / black luxury PDP.
- Exact product gallery order:
  1. `hero.webp`
  2. `notes.webp`
  3. `world.webp`
  4. `story.webp`
- Main image uses a fixed premium aspect ratio with `object-cover`.
- Four aligned thumbnails switch the main gallery.
- Product card includes dummy/static rating, review count and sold count.
- Related products also show stable dummy rating + sold values.
- Normal perfumes display 50 ML.
- Notes section uses `notes.webp`.
- Scent-world banner uses `world.webp`.
- Product-information strip is inserted between the lower editorial banners.
- House ritual/story banner uses `story.webp`.
- Banner heights were reduced and normalized so artwork fits cleanly.

## Stock fix
Normal active Woo simple fragrances are treated as available when numeric inventory
tracking is disabled. The same rule now exists in:
- PDP/catalog rendering
- cart validation
- checkout/order placement

Tester products keep their explicit availability.
Tracked products still use numeric stock.

## Generate all product prompts from exact Laravel data

```powershell
php artisan storefront:export-product-image-prompts
```

Output:

```text
PRODUCT_IMAGE_PROMPTS_ALL_26.md
```

That file contains exact:
- product name
- slug
- category
- SKU
- imported description
- story
- notes
- variants
- save paths
- `notes.webp` prompt
- `world.webp` prompt
- `story.webp` prompt

Missing imported data is stated as missing instead of being invented.

## Local

```powershell
cd E:\ScentsByAamirLaravel\frontend

php artisan optimize:clear
php artisan view:clear
php artisan view:cache

php artisan storefront:export-product-image-prompts

npm ci
npm run build

php artisan serve
```

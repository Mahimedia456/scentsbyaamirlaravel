# Scents by Aamir — Official Gallery + 3 Note Images

## Correct final image responsibilities

### Shopping gallery
The top product gallery now uses only:

1. `hero.webp`
2. official/imported Woo/Laravel product gallery images

It no longer uses:
- `notes.webp`
- `world.webp`
- `story.webp`

inside the shopping gallery.

### Product editorial images
Existing:
- `world.webp` = Scent World banner
- `story.webp` = House Ritual / Story banner

### New note-card images
Generate exactly three images per product:

- `top-notes.webp`
- `heart-notes.webp`
- `base-notes.webp`

Each note card now uses its own image at natural `object-cover` sizing.
The old single `notes.webp` remains only as a temporary fallback until all three
new note images are uploaded.

## Prompt file

Run:

```powershell
php artisan storefront:export-product-note-prompts
```

This creates one complete file:

```text
PRODUCT_NOTE_IMAGES_PROMPTS_ALL_26.md
```

using exact current Laravel database product data.

## After uploading the note images

Expected folder:

```text
public/images/products/{slug}/
├── hero.webp
├── top-notes.webp
├── heart-notes.webp
├── base-notes.webp
├── world.webp
└── story.webp
```

Imported/official product gallery media stays in the Laravel product image data
and is automatically appended after hero.webp in the shopping gallery.

## Local

```powershell
cd E:\ScentsByAamirLaravel\frontend

php artisan optimize:clear
php artisan view:clear
php artisan view:cache

php artisan storefront:export-product-note-prompts

npm ci
npm run build

php artisan serve
```

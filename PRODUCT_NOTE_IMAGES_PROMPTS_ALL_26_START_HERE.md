# 26 Separate Product Prompt Files

Run:

```powershell
php artisan storefront:export-product-note-prompts
```

It creates a folder:

```text
PRODUCT_NOTE_PROMPTS_26/
```

Inside it there will be **one separate Markdown file for every active product**
(26 files when the active catalogue contains the expected 26 products).

Example:

```text
01-ocean-spirit-inspired-by-acqua-di-gio-NOTE-IMAGES-PROMPT.md
02-wild-intense-NOTE-IMAGES-PROMPT.md
...
```

Each individual product file contains exactly its own three image prompts:

- `top-notes.webp`
- `heart-notes.webp`
- `base-notes.webp`

Existing hero/world/story images are not regenerated.

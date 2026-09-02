# Prompt Export Correction

The exporter no longer combines all products into one Markdown document.

`php artisan storefront:export-product-note-prompts`

now writes one `.md` prompt document per active product into
`PRODUCT_NOTE_PROMPTS_26/`.

Each product document contains its three dedicated Top / Heart / Base note-image prompts.

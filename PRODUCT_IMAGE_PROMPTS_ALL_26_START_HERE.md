# Scents by Aamir — All 26 Product Image Prompt Workflow

The final PDP uses the existing `hero.webp` plus **three new supporting images per product**:

- `notes.webp`
- `world.webp`
- `story.webp`

Because exact slugs/SKU/category/descriptions/notes live in your Laravel database, generate the final detailed prompt file directly from the DB:

```powershell
php artisan storefront:export-product-image-prompts
```

This creates:

`PRODUCT_IMAGE_PROMPTS_ALL_26.md`

The generated file follows your requested format and includes exact save paths for every current active product.

## Current 26-product batch

1. Delure — inspired by Good Girl
2. Vauren — inspired by YSL Tuxedo
3. Dark Aure — inspired by Nuit de Feu
4. Elyndor — inspired by Roja Elysium
5. Kavian — inspired by Al Haitham
6. Cherelle — inspired by Dior Oud Ispahan
7. Hivalta — inspired by Nishane Hacivat
8. Royal Noxis — inspired by Clive Christian 1872
9. Custom Perfume Tester Box
10. Floral Charm
11. Mens Perfume Tester Box
12. Female Perfume Tester Box
13. Silver Breeze — inspired by Creed Silver Mountain Water
14. Blossom Shine — inspired by Victoria's Secret Bombshell
15. Smoky Chic
16. Opulent Rouge
17. Bold Heat — inspired by Office for Men
18. Aventus Spirit — inspired by Creed Aventus
19. Night Rider — inspired by Bleu de Chanel
20. Ocean Spirit — inspired by Acqua di Gio
21. Le Reve Dore — inspired by La Vie Est Belle
22. Amerel — inspired by Dior J’adore
23. Infinite Spark
24. Wild Intense
25. Desert Soul — inspired by Ombre Nomade
26. Dark Seduction

## Final folder contract

```text
public/images/products/{exact-laravel-slug}/
├── hero.webp   # existing product hero
├── notes.webp  # generate
├── world.webp  # generate
└── story.webp  # generate
```
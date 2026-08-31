# Scents by Aamir — Phase 05 Discovery + Automatic Product Worlds

## Important change from Phase 04

You do **NOT** need to generate `campaign-world.webp`, `notes.webp`, and `ritual.webp` for every product anymore.

The product page now automatically:

1. reads the real product name, family, story and notes;
2. assigns the product to a visual world;
3. loads one reusable world background;
4. places the actual product image over that background.

So you only create the reusable background library once.

Save all files here:

`public/images/product-worlds/`

No bottle is required for any background below.

---

## 1. `signature.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create a minimal luxury fragrance-house background with matte black mineral plinths, subtle champagne reflections, one pale flower and one small amber resin fragment.
No bottle, no packaging, no people, no text, no logo.
The center and right-center should remain visually open enough for a perfume bottle to be layered on top by the website.
Deep black, architectural, timeless, premium.
2000x1600.

---

## 2. `dark.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create a dark nocturnal fragrance environment with charred wood, restrained smoke, black mineral stone and one subtle bronze highlight.
No bottle.
Keep the central area relatively open because the website will overlay the real product bottle.
No flames, no text, no people, no packaging.
2000x1600.

---

## 3. `oud.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create a sophisticated oud fragrance environment: sculptural dark oud wood, resin-darkened edges, black stone, one translucent amber resin piece, fine incense haze.
No bottle.
Keep center-right clear for website product overlay.
Deep charcoal and bronze gallery lighting.
No text, people, packaging or watermark.
2000x1600.

---

## 4. `floral.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create a high-fashion floral fragrance environment using deep burgundy rose petals, one pale white flower, restrained dark green stems and black mineral stone.
Elegant, not bridal, not bright pink.
No bottle.
Leave center-right clear for the website to overlay the real product.
No text, people, packaging or watermark.
2000x1600.

---

## 5. `fresh.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create a refined fresh fragrance environment using wet obsidian, restrained bergamot peel, one white blossom, small green leaves, clean water droplets and mineral reflections.
No beach, no tropical ocean.
No bottle.
Keep center-right visually clear for product overlay.
Black, graphite, silver and restrained citrus tones.
2000x1600.

---

## 6. `gourmand.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create a dark luxury gourmand fragrance environment using a few roasted coffee beans, one vanilla pod, subtle amber resin glow, dark stone and a tiny white floral accent.
No bottle.
Not food photography.
Keep center-right open for the actual bottle overlay.
Deep black, warm amber, polished and seductive.
2000x1600.

---

## 7. `amber.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create a warm amber fragrance environment with translucent amber resin, soft resinous glow, dark mineral stone and restrained smoky depth.
No bottle.
Keep central/right-central space visually open.
Deep black and glowing warm gold, premium and minimal.
2000x1600.

---

## 8. `spicy.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create a luxury spicy fragrance environment using very restrained saffron threads, cardamom, black pepper and one dark cinnamon fragment arranged like museum objects on black stone.
No kitchen flat-lay.
No bottle.
Keep center-right clear for product overlay.
Warm bronze highlights and deep charcoal shadows.
2000x1600.

---

## 9. `woody.webp`
**Bottle:** NO  
**Size:** 2000x1600

Create an architectural woody fragrance environment using pale sandalwood, darker dry wood grain and graphite mineral stone.
No bottle.
Keep the center-right area open for the website product overlay.
Controlled warm light, deep shadow, premium tactile detail.
2000x1600.

---

## 10. `ritual.webp`
**Bottle:** NO  
**Size:** 1800x1600

Create a quiet luxury fragrance ritual image with folded premium black and cream fabric, one blotter strip, a small clear glass dropper and soft directional window light.
No perfume bottle.
No readable text.
No people.
No packaging.
Timeless and intimate.
1800x1600.

---

# Finder hero

Save:

`public/images/discovery/finder-hero.webp`

**Bottle:** NO  
**Size:** 2400x1200

Create a cinematic luxury fragrance discovery hero.

NO perfume bottle.
NO packaging.
NO people.
NO text.
NO logo.

On the RIGHT half, create a restrained sensory spectrum using:
bergamot peel,
dark oud wood,
rose petals,
translucent amber resin,
pale sandalwood,
one white jasmine bloom,
and a very fine smoke ribbon.

The materials should flow from fresh and luminous to dark and resinous, representing different fragrance personalities.

Keep LEFT 42% deep black and uncluttered for website copy.

Directional gallery lighting.
Quiet luxury.
Contemporary niche perfumery.
2400x1200.

---

## How automatic assignment works

The Blade currently chooses:

- oud terms → `oud.webp`
- dark / smoke / leather → `dark.webp`
- rose / floral / jasmine → `floral.webp`
- citrus / bergamot / fresh / ocean → `fresh.webp`
- vanilla / gourmand / sweet / coffee → `gourmand.webp`
- amber / resin → `amber.webp`
- spice / saffron / pepper / cardamom → `spicy.webp`
- wood / sandal / cedar → `woody.webp`
- anything else → `signature.webp`

The real product image is then layered above that world automatically.

This means one background set can support the entire current and future catalogue.

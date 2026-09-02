# Scents by Aamir — WordPress → Laravel Journal Import Module

Baseline: **ScentsByAamir_TARGETED_BUGFIX_FINAL.zip** (locked checkpoint).

This ZIP is a targeted module patch. Extract/merge it over the locked Laravel frontend; it does not contain the 400+ MB project archive or unrelated product assets.

## What the module does

- Uses the existing Laravel `JournalPost` model, `/journal`, `/journal/{slug}` and Admin Journal screens.
- Imports **published WordPress posts** from the WordPress REST API.
- Imports title, slug, excerpt, full article HTML, publish/modified dates and original URL.
- Imports embedded WordPress author, categories and tags.
- Imports Yoast SEO fields when WordPress exposes `yoast_head_json`:
  - meta title
  - meta description
  - Open Graph title/description/image
- Uses the **new Laravel Journal URL as canonical**; the old WordPress URL remains stored as source provenance.
- Downloads featured images to Laravel public storage.
- Finds `<img>` elements inside WordPress article HTML, downloads those images locally and rewrites article HTML to `/storage/journal/...` URLs.
- Sanitizes imported HTML by removing scripts, styles, iframes, forms, event-handler attributes and unsafe links.
- Keeps headings, paragraphs, lists, blockquotes, figures, captions, links, tables and images.
- Uses `wordpress_id` as a unique import key so the same post is not duplicated.
- Supports update mode for posts already imported from WordPress.
- Adds an Admin **Import WordPress** screen as well as an Artisan command.
- Existing manually-created Journal articles continue to work.

## Files added / extended

- `app/Services/WordPressJournalImporter.php`
- `app/Console/Commands/ImportWordPressJournal.php`
- `app/Http/Controllers/Admin/JournalImportController.php`
- `database/migrations/2026_09_02_203000_extend_journal_posts_for_wordpress_import.php`
- `config/wordpress.php`
- `resources/views/admin/journal-import/index.blade.php`
- existing Journal model/controller/admin views
- existing Journal Detail view
- store layout SEO section support
- admin Journal routes

## 1. Merge ZIP locally

Extract over:

`E:\ScentsByAamirLaravel\frontend`

Allow matching files to overwrite.

## 2. Local setup

```powershell
cd E:\ScentsByAamirLaravel\frontend

composer dump-autoload
php artisan optimize:clear
php artisan migrate
php artisan storage:link
```

`storage:link` may say the link already exists; that is fine.

The default WordPress source is already:

```env
WORDPRESS_URL=https://scentsbyaamir.com
WORDPRESS_TIMEOUT=25
WORDPRESS_PER_PAGE=50
```

Only add/change these in `.env` if needed.

## 3. First test — no writes

```powershell
php artisan wordpress:import-journal --dry-run
```

This reads the live WordPress posts but makes **no DB or image changes**.

For a very small test:

```powershell
php artisan wordpress:import-journal --dry-run --limit=3
```

## 4. Actual import

```powershell
php artisan wordpress:import-journal
```

New WordPress posts are created in Laravel. Existing imported WordPress posts are skipped.

## 5. Update imported WordPress posts later

```powershell
php artisan wordpress:import-journal --update-existing
```

This refreshes imported content/metadata/images while preserving the Laravel Journal architecture.

## 6. Import content without downloading images (testing only)

```powershell
php artisan wordpress:import-journal --no-images
```

For the final migration, normal import **with images** is recommended.

## Admin import

After deployment:

**Admin → Journal → Import WordPress**

The admin screen provides:

- Dry run
- Import new posts
- Update imported posts
- optional post limit
- local image download toggle
- import totals + warnings

For the full image-heavy migration, Artisan/SSH is preferred because browser requests on shared hosting can have shorter execution limits.

## Production/server commands

Your normal deploy script runs migrations. After deployment, SSH to the server and run:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force
/usr/php84/usr/bin/php artisan storage:link || true

/usr/php84/usr/bin/php artisan wordpress:import-journal --dry-run
```

If the result is correct:

```bash
/usr/php84/usr/bin/php artisan wordpress:import-journal
/usr/php84/usr/bin/php artisan optimize:clear
```

Later, if WordPress posts were edited and you deliberately want to refresh Laravel copies:

```bash
/usr/php84/usr/bin/php artisan wordpress:import-journal --update-existing
```

## Verify after import

Check:

1. `/journal`
2. several `/journal/{slug}` pages
3. featured images
4. inline article images
5. headings/lists/quotes inside article content
6. Admin → Journal
7. Admin → Journal → imported article SEO/source/taxonomy
8. page source for canonical, Open Graph and meta description

## Storage structure

Imported images are stored under Laravel's public disk, for example:

```text
storage/app/public/journal/how-to-choose-perfume/featured-a12b34c56d.webp
storage/app/public/journal/how-to-choose-perfume/inline-f47ac10b58-93c84f2a1e.jpg
```

The browser receives `/storage/journal/...` URLs through Laravel's normal public storage symlink.

## WordPress independence

After the import, normal Journal browsing reads **Laravel MySQL + Laravel storage**. WordPress is not queried when customers open Journal pages.

WordPress is contacted only when you deliberately execute an import/update command or use the Admin import screen.

## Important scope

This module does not change products, inventory, checkout, homepage design, perfume content or the locked mobile/order fixes.

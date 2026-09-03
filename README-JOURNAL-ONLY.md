# Scents by Aamir — WordPress Journal Import (Journal-only patch)

This corrected package is intentionally isolated to the existing Laravel Journal module.

## Scope lock

It does **not** replace or modify:

- `routes/web.php`
- storefront layout
- header / footer
- `resources/css/app.css`
- `resources/js/app.js`
- homepage
- products
- cart / checkout
- account / orders
- notifications
- any unrelated admin module

The existing `/journal`, `/journal/{slug}` and Admin → Journal routes remain exactly as they already are.

## Imported from WordPress

For published WordPress posts the importer brings across:

- title
- slug
- excerpt
- full article body
- featured image
- inline article images
- publication date
- author name
- all WordPress post categories (ID, name, slug, source link when available)
- primary WordPress category as the existing Journal `eyebrow`
- all WordPress post tags (ID, name, slug, source link when available)
- original WordPress article URL
- Yoast SEO title / description when the WordPress API exposes them

### Deliberately NOT imported

- comments
- comment counts
- comment author/user data
- comment feeds / comment metadata
- unrelated WordPress metadata
- unrelated WordPress pages/products

There is no Laravel comments feature added by this package.

## Image behavior

Featured and inline WordPress article images are downloaded once into Laravel's `public` storage disk under:

`storage/app/public/journal/{article-slug}/`

Inline image URLs inside the article HTML are rewritten to Laravel `/storage/...` URLs. The Journal then works without calling WordPress on each customer page view.

## Files changed by this patch

Only Journal-specific files are changed/added:

- `app/Console/Commands/ImportWordPressJournal.php`
- `app/Services/Journal/WordPressJournalImporter.php`
- `app/Services/Journal/JournalHtmlSanitizer.php`
- `app/Models/JournalPost.php`
- `app/Http/Controllers/Admin/JournalPostController.php`
- `resources/views/store/journal-detail.blade.php`
- `resources/views/admin/journal-posts/form.blade.php`
- `resources/views/admin/journal-posts/index.blade.php`
- `database/migrations/2026_09_02_210000_add_wordpress_source_fields_to_journal_posts.php`
- `config/journal_import.php`

## Install locally

Extract/merge into:

`E:\ScentsByAamirLaravel\frontend`

Then:

```powershell
cd E:\ScentsByAamirLaravel\frontend

composer dump-autoload
php artisan optimize:clear
php artisan migrate
php artisan storage:link
```

No `npm` rebuild is required by this Journal-only package because it does not alter CSS or JavaScript.

## WordPress source

Default source:

`https://scentsbyaamir.com`

You can override it in `.env`:

```env
WORDPRESS_JOURNAL_URL=https://your-wordpress-domain.com
```

or directly per command with `--url=`.

## Safe dry run first

```powershell
php artisan wordpress:import-journal --dry-run
```

Test only 3 posts:

```powershell
php artisan wordpress:import-journal --dry-run --limit=3
```

If the output is correct:

```powershell
php artisan wordpress:import-journal
```

Later, refresh posts already imported from WordPress:

```powershell
php artisan wordpress:import-journal --update-existing
```

The duplicate key is `wordpress_id`, so normal repeated imports do not create duplicate Journal posts.

## Server / production

After deploying this patch:

```bash
cd /home/sites/41b/8/81d92349b7/public_html/shop/laravel12

/usr/php84/usr/bin/php artisan optimize:clear
/usr/php84/usr/bin/php artisan migrate --force
/usr/php84/usr/bin/php artisan storage:link || true
/usr/php84/usr/bin/php artisan wordpress:import-journal --dry-run
```

If dry-run looks right:

```bash
/usr/php84/usr/bin/php artisan wordpress:import-journal
/usr/php84/usr/bin/php artisan optimize:clear
```

## Important

Do not re-apply the previous Journal import ZIP that modified core storefront files. This package replaces that importer approach and is the Journal-only version.


## Important dry-run behavior
`--dry-run` NEVER writes posts to MySQL and NEVER makes them appear in Admin. After a successful dry-run, run `php artisan wordpress:import-journal` without `--dry-run`. Use `php artisan journal:status` to confirm the exact Laravel database and row counts.

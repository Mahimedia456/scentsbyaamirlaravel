# Scents by Aamir — Frontend Restart Phase 03

Complete checkpoint preserving Restart Phases 01–02.

## Composer / Windows installation fix

The previous `composer install` reached 96–100% but Windows locked a temporary `phar-io/manifest` directory. That dependency came from PHPUnit in the dev dependency stack.

For the frontend stage PHPUnit / Collision / Pint are not required, so this checkpoint removes those unnecessary Composer dev packages. The normal install is now much smaller and avoids the failing phar-io package path.

Because the previous install stopped before `vendor/autoload.php` was finalized, clean the incomplete vendor folder once before reinstalling.

Run manually in PowerShell:

```powershell
cd E:\ScentsByAamirLaravel\frontend

Get-Process php,composer -ErrorAction SilentlyContinue | Stop-Process -Force

Remove-Item .\vendor -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item .\composer.lock -Force -ErrorAction SilentlyContinue

composer clear-cache
composer install --prefer-dist

php artisan key:generate
```

If Windows still says a file is locked:
1. Close VS Code terminals that are using PHP/Composer.
2. Temporarily pause Windows Search indexing for this project folder or add the project folder to antivirus exclusions.
3. Run the cleanup/install commands again.

Do not run `php artisan ...` until `composer install` finishes successfully and `vendor/autoload.php` exists.

## npm warning

The `esbuild ... npm approve-scripts` line is a warning, not the Composer failure. If Vite itself works, no action is required for that warning.

## Phase 03 catalogue upgrades

- desktop filter panel
- mobile slide-in filter drawer
- active category chips
- category tabs
- sort dropdown
- 2/4-column view controls
- product quick-view drawer
- size selector
- Add to Bag shell
- View Full Product World action
- responsive catalogue polish
- Phase 01–02 header, mega menu, homepage and footer preserved

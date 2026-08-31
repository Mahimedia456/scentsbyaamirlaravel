param(
    [string]$Url = "",
    [string]$Key = "",
    [string]$Secret = "",
    [switch]$SkipMedia
)

$ErrorActionPreference = "Stop"
Set-Location (Split-Path -Parent $PSScriptRoot)

Write-Host "Scents by Aamir - WooCommerce -> Laravel full import" -ForegroundColor Cyan
Write-Host "This script safely INSERTS missing records and UPDATES matching records." -ForegroundColor DarkGray
Write-Host "It does not run migrate:fresh and does not truncate your Laravel store." -ForegroundColor DarkGray

Write-Host "`n[1/4] Clearing cached Laravel configuration..." -ForegroundColor Yellow
php artisan optimize:clear
if ($LASTEXITCODE -ne 0) { throw "optimize:clear failed" }

Write-Host "`n[2/4] Applying/repairing database migrations..." -ForegroundColor Yellow
php artisan migrate --force
if ($LASTEXITCODE -ne 0) { throw "php artisan migrate failed" }

Write-Host "`n[3/4] Ensuring public storage link..." -ForegroundColor Yellow
php artisan storage:link 2>$null

$argsList = @("artisan", "woocommerce:sync")
if ($Url)    { $argsList += "--url=$Url" }
if ($Key)    { $argsList += "--key=$Key" }
if ($Secret) { $argsList += "--secret=$Secret" }
if ($SkipMedia) { $argsList += "--no-media" }

Write-Host "`n[4/4] Importing categories, products, variants, customers, orders and media..." -ForegroundColor Yellow
& php @argsList
if ($LASTEXITCODE -ne 0) { throw "WooCommerce import failed. Check the error above and storage/logs/laravel.log." }

Write-Host "`nWooCommerce import completed successfully." -ForegroundColor Green
Write-Host "You can safely run this script again. Existing mapped/natural-key records are updated and missing records are inserted." -ForegroundColor Green

param(
    [switch]$Force
)

$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$argsList = @("artisan", "storefront:prepare-product-artwork")
if ($Force) {
    $argsList += "--force"
}

php @argsList

Write-Host ""
Write-Host "Done. Product folders are in:" -ForegroundColor Green
Write-Host "public\images\products\" -ForegroundColor Cyan

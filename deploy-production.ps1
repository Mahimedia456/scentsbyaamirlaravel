param(
    [string]$CommitMessage = "Deploy Scents by Aamir production",
    [switch]$RepairProducts
)

$ErrorActionPreference = "Stop"

$ProjectRoot = $PSScriptRoot
$GitBranch = "main"
$GitRemote = "origin"
$SshHost = "ssh.gb.stackcp.com"
$SshUser = "scentsbyaamir.com"
$SshKey = "C:\Users\hp\.ssh\scentsbyaamir_github_actions_nopass"
$ServerPath = "/home/sites/41b/8/81d92349b7/public_html/shop/laravel12"
$Php = "/usr/php84/usr/bin/php"
$Composer = "/usr/local/bin/composer"
$ProductionUrl = "https://shop.scentsbyaamir.com"

function Write-Step([string]$Message) {
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor DarkGray
    Write-Host $Message -ForegroundColor Cyan
    Write-Host "============================================================" -ForegroundColor DarkGray
}

function Assert-Success([string]$Step) {
    if ($LASTEXITCODE -ne 0) {
        throw "$Step failed with exit code $LASTEXITCODE."
    }
}

Set-Location $ProjectRoot

Write-Step "1/7 - Checking local Laravel project"

if (-not (Test-Path ".\artisan")) { throw "artisan not found in $ProjectRoot" }
if (-not (Test-Path ".\package.json")) { throw "package.json not found." }
if (-not (Test-Path $SshKey)) { throw "SSH private key not found: $SshKey" }

git rev-parse --is-inside-work-tree | Out-Null
Assert-Success "Git repository check"

$currentBranch = (git branch --show-current).Trim()
if ($currentBranch -ne $GitBranch) {
    git checkout $GitBranch
    Assert-Success "git checkout $GitBranch"
}

Write-Step "2/7 - Validating Blade and building Vite locally"

php artisan optimize:clear
Assert-Success "php artisan optimize:clear"

# This is the deployment gate: all Blade templates must compile before push.
php artisan view:cache
Assert-Success "php artisan view:cache"
php artisan view:clear
Assert-Success "php artisan view:clear"

if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
    throw "npm is not available on this Windows machine."
}

if (Test-Path ".\package-lock.json") {
    npm ci
} else {
    npm install
}
Assert-Success "npm dependency install"

npm run build
Assert-Success "npm run build"

if (-not (Test-Path ".\public\build\manifest.json")) {
    throw "Vite manifest was not generated: public\build\manifest.json"
}

Write-Step "3/7 - Committing application and production build"

git add -A
Assert-Success "git add"

$changes = git status --porcelain
if ($changes) {
    git commit -m $CommitMessage
    Assert-Success "git commit"
} else {
    Write-Host "No new local changes to commit." -ForegroundColor Yellow
}

$LocalCommit = (git rev-parse --short HEAD).Trim()

Write-Step "4/7 - Pushing main to GitHub"

git push $GitRemote $GitBranch
Assert-Success "git push"

Write-Step "5/7 - Preparing one-session StackCP deployment"

$RemoteProductRepair = ""
if ($RepairProducts) {
    $RemoteProductRepair = @"
echo '--- One-time simple product size / availability repair ---'
'$Php' artisan storefront:repair-simple-products
"@
}

$RemoteCommand = @"
set -e
cd '$ServerPath'

echo '--- Connected to StackCP ---'
pwd

echo '--- Updating origin/main ---'
git fetch '$GitRemote'
git checkout '$GitBranch'
git reset --hard '$GitRemote/$GitBranch'

echo '--- Production commit ---'
git rev-parse --short HEAD

echo '--- Composer production dependencies ---'
'$Php' '$Composer' install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo '--- Clear stale Laravel caches ---'
'$Php' artisan optimize:clear

echo '--- Run pending migrations ---'
'$Php' artisan migrate --force

$RemoteProductRepair

echo '--- Storage link ---'
'$Php' artisan storage:link || true

echo '--- Compile Blade views as production validation ---'
'$Php' artisan view:cache

echo '--- Cache routes ---'
'$Php' artisan route:cache

echo '--- Verify Vite manifest ---'
test -f public/build/manifest.json

echo '--- Environment ---'
'$Php' artisan about --only=environment

echo '--- Migration status ---'
'$Php' artisan migrate:status

echo '--- Mail configuration ---'
'$Php' artisan storefront:mail-check

echo '--- Customer password routes ---'
'$Php' artisan route:list --path=account/forgot-password
'$Php' artisan route:list --path=account/reset-password

echo '--- Final commit ---'
git rev-parse --short HEAD
"@

$RemoteBase64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($RemoteCommand))
$ServerExecutor = "echo '$RemoteBase64' | base64 -d | bash"

Write-Step "6/7 - Deploying through one SSH session"

ssh `
    -T `
    -o BatchMode=yes `
    -o IdentitiesOnly=yes `
    -o PreferredAuthentications=publickey `
    -o PasswordAuthentication=no `
    -o KbdInteractiveAuthentication=no `
    -o StrictHostKeyChecking=accept-new `
    -o ServerAliveInterval=30 `
    -o ServerAliveCountMax=6 `
    -i "$SshKey" `
    "$SshUser@$SshHost" `
    $ServerExecutor

Assert-Success "Production SSH deployment"

Write-Step "7/7 - Deployment complete"
Write-Host "DEPLOYMENT SUCCESSFUL" -ForegroundColor Green
Write-Host "Commit : $LocalCommit"
Write-Host "URL    : $ProductionUrl" -ForegroundColor Green
Write-Host "Server npm is not required. Vite is built locally." -ForegroundColor Yellow
Write-Host "config:cache is intentionally not used." -ForegroundColor Yellow
if ($RepairProducts) {
    Write-Host "One-time simple product repair was requested and executed." -ForegroundColor Green
}

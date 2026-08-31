param(
    [string]$CommitMessage = "Deploy Scents by Aamir production"
)

$ErrorActionPreference = "Stop"

# ============================================================
# SCENTS BY AAMIR - PRODUCTION DEPLOYMENT
# Location:
# E:\ScentsByAamirLaravel\frontend\deploy-production.ps1
#
# Run:
# powershell -ExecutionPolicy Bypass -File .\deploy-production.ps1
#
# Custom commit:
# powershell -ExecutionPolicy Bypass -File .\deploy-production.ps1 `
#   -CommitMessage "Fix order invoice and checkout"
# ============================================================

# ------------------------------------------------------------
# LOCAL
# ------------------------------------------------------------

$ProjectRoot = $PSScriptRoot

$GitBranch = "main"
$GitRemote = "origin"

# ------------------------------------------------------------
# STACKCP SSH
# ------------------------------------------------------------

$SshHost = "ssh.gb.stackcp.com"
$SshUser = "scentsbyaamir.com"

$SshKey = "C:\Users\hp\.ssh\scentsbyaamir_github_actions_nopass"

# ------------------------------------------------------------
# PRODUCTION
# ------------------------------------------------------------

$ServerPath = "/home/sites/41b/8/81d92349b7/public_html/shop/laravel12"

$Php = "/usr/php84/usr/bin/php"
$Composer = "/usr/local/bin/composer"

$ProductionUrl = "https://shop.scentsbyaamir.com"

# ============================================================
# HELPERS
# ============================================================

function Write-Step {
    param(
        [string]$Message
    )

    Write-Host ""
    Write-Host "============================================================" `
        -ForegroundColor DarkGray

    Write-Host $Message `
        -ForegroundColor Cyan

    Write-Host "============================================================" `
        -ForegroundColor DarkGray
}

function Assert-LastExitCode {
    param(
        [string]$Step
    )

    if ($LASTEXITCODE -ne 0) {
        throw "$Step failed with exit code $LASTEXITCODE."
    }
}

# ============================================================
# START
# ============================================================

Set-Location $ProjectRoot

Write-Step "1/9 - Checking local Laravel project"

if (-not (Test-Path ".\artisan")) {
    throw @"
Laravel artisan file not found.

This script must be located inside:

E:\ScentsByAamirLaravel\frontend
"@
}

if (-not (Test-Path ".\package.json")) {
    throw "package.json not found in project root."
}

if (-not (Test-Path $SshKey)) {
    throw "SSH private key not found: $SshKey"
}

git rev-parse --is-inside-work-tree | Out-Null

Assert-LastExitCode "Git repository check"

$currentBranch = (git branch --show-current).Trim()

Assert-LastExitCode "Git branch check"

if ($currentBranch -ne $GitBranch) {

    Write-Host ""
    Write-Host "Switching Git branch:" `
        -ForegroundColor Yellow

    Write-Host "$currentBranch -> $GitBranch" `
        -ForegroundColor Yellow

    git checkout $GitBranch

    Assert-LastExitCode "git checkout $GitBranch"
}

# ============================================================
# LOCAL NPM
# ============================================================

Write-Step "2/9 - Installing and building frontend locally"

$npmCommand = Get-Command npm -ErrorAction SilentlyContinue

if (-not $npmCommand) {

    throw @"
npm was not found on this Windows machine.

Install Node.js/npm locally first.

The production StackCP server does NOT need npm because this
deployment script builds Vite assets locally.
"@
}

Write-Host "npm detected:" `
    -ForegroundColor Green

npm --version

Assert-LastExitCode "npm version check"

# ------------------------------------------------------------
# Use npm ci when lock exists
# ------------------------------------------------------------

if (Test-Path ".\package-lock.json") {

    Write-Host ""
    Write-Host "Running npm ci..." `
        -ForegroundColor Green

    npm ci

    Assert-LastExitCode "npm ci"
}
else {

    Write-Host ""
    Write-Host "package-lock.json not found. Running npm install..." `
        -ForegroundColor Yellow

    npm install

    Assert-LastExitCode "npm install"
}

Write-Host ""
Write-Host "Building Vite production assets..." `
    -ForegroundColor Green

npm run build

Assert-LastExitCode "npm run build"

if (-not (Test-Path ".\public\build\manifest.json")) {

    throw @"
Vite build completed but:

public\build\manifest.json

was not found.

Deployment stopped to prevent broken production assets.
"@
}

Write-Host ""
Write-Host "Local Vite production build successful." `
    -ForegroundColor Green

# ============================================================
# GIT
# ============================================================

Write-Step "3/9 - Staging local application and build"

git add -A

Assert-LastExitCode "git add"

$pendingChanges = git status --porcelain

if ($pendingChanges) {

    Write-Host ""
    Write-Host "Changes detected. Creating commit..." `
        -ForegroundColor Green

    git commit -m $CommitMessage

    Assert-LastExitCode "git commit"
}
else {

    Write-Host ""
    Write-Host "No local changes to commit." `
        -ForegroundColor Yellow

    Write-Host "Current HEAD will be deployed." `
        -ForegroundColor Yellow
}

# ============================================================
# PUSH
# ============================================================

Write-Step "4/9 - Pushing main branch to GitHub"

git push $GitRemote $GitBranch

Assert-LastExitCode "git push"

$LocalCommit = (git rev-parse --short HEAD).Trim()

Write-Host ""
Write-Host "GitHub push successful." `
    -ForegroundColor Green

Write-Host "Commit: $LocalCommit"

# ============================================================
# SSH TEST
# ============================================================

Write-Step "5/9 - Testing StackCP SSH connection"

ssh `
    -o BatchMode=yes `
    -o IdentitiesOnly=yes `
    -o StrictHostKeyChecking=accept-new `
    -i $SshKey `
    "$SshUser@$SshHost" `
    "echo 'SSH connected successfully'; pwd"

Assert-LastExitCode "SSH connection"

# ============================================================
# SERVER DEPLOYMENT
# ============================================================

Write-Step "6/9 - Deploying Laravel application on StackCP"

$RemoteScript = @"
set -e

echo ""
echo "=============================================="
echo "SCENTS BY AAMIR PRODUCTION DEPLOYMENT"
echo "=============================================="

cd '$ServerPath'

echo ""
echo "--- Production path ---"
pwd

echo ""
echo "--- Fetching GitHub main ---"

git fetch '$GitRemote'

git checkout '$GitBranch'

git reset --hard '$GitRemote/$GitBranch'

echo ""
echo "--- Production Git commit ---"

git rev-parse --short HEAD

echo ""
echo "--- Installing Composer production dependencies ---"

'$Php' '$Composer' install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

echo ""
echo "--- Clearing Laravel caches BEFORE migration ---"

'$Php' artisan optimize:clear

echo ""
echo "--- Running database migrations ---"

'$Php' artisan migrate --force

echo ""
echo "--- Creating public storage link if required ---"

'$Php' artisan storage:link || true

echo ""
echo "--- Clearing Laravel caches AFTER migration ---"

'$Php' artisan optimize:clear

echo ""
echo "--- Caching routes ---"

'$Php' artisan route:cache

echo ""
echo "--- Caching Blade views ---"

'$Php' artisan view:cache

echo ""
echo "--- Migration status ---"

'$Php' artisan migrate:status

echo ""
echo "--- Laravel environment ---"

'$Php' artisan about --only=environment

echo ""
echo "--- Checking public build ---"

if [ -f "public/build/manifest.json" ]; then

    echo "Vite manifest exists."

else

    echo "ERROR: public/build/manifest.json missing."
    exit 1

fi

echo ""
echo "--- Final production commit ---"

git rev-parse --short HEAD

echo ""
echo "=============================================="
echo "SERVER DEPLOYMENT COMPLETE"
echo "=============================================="
"@

$RemoteScript | ssh `
    -o BatchMode=yes `
    -o IdentitiesOnly=yes `
    -o StrictHostKeyChecking=accept-new `
    -i $SshKey `
    "$SshUser@$SshHost" `
    "bash -s"

Assert-LastExitCode "Remote production deployment"

# ============================================================
# VERIFY COMMIT
# ============================================================

Write-Step "7/9 - Verifying deployed Git commit"

$RemoteCommit = ssh `
    -o BatchMode=yes `
    -o IdentitiesOnly=yes `
    -i $SshKey `
    "$SshUser@$SshHost" `
    "cd '$ServerPath' && git rev-parse --short HEAD"

Assert-LastExitCode "Production commit check"

$RemoteCommit = $RemoteCommit.Trim()

Write-Host ""
Write-Host "Local commit  : $LocalCommit"
Write-Host "Server commit : $RemoteCommit"

if ($LocalCommit -ne $RemoteCommit) {

    throw @"
Production commit does not match local Git commit.

Local:
$LocalCommit

Production:
$RemoteCommit
"@
}

Write-Host ""
Write-Host "Git commit verification successful." `
    -ForegroundColor Green

# ============================================================
# FINAL SERVER CHECK
# ============================================================

Write-Step "8/9 - Final Laravel production checks"

$FinalCheck = @"
set -e

cd '$ServerPath'

echo "--- Environment ---"
'$Php' artisan about --only=environment

echo ""
echo "--- Pending migrations ---"
'$Php' artisan migrate:status

echo ""
echo "--- Route check ---"
'$Php' artisan route:list --path=admin/orders

echo ""
echo "--- Storage ---"

if [ -L "public/storage" ] || [ -d "public/storage" ]; then
    echo "public/storage available"
else
    echo "WARNING: public/storage not found"
fi

echo ""
echo "--- Vite build ---"

if [ -f "public/build/manifest.json" ]; then
    echo "public/build/manifest.json OK"
else
    echo "public/build/manifest.json MISSING"
    exit 1
fi
"@

$FinalCheck | ssh `
    -o BatchMode=yes `
    -o IdentitiesOnly=yes `
    -i $SshKey `
    "$SshUser@$SshHost" `
    "bash -s"

Assert-LastExitCode "Final production check"

# ============================================================
# COMPLETE
# ============================================================

Write-Step "9/9 - Deployment finished"

Write-Host ""
Write-Host "DEPLOYMENT SUCCESSFUL" `
    -ForegroundColor Green

Write-Host ""
Write-Host "Project : Scents by Aamir"
Write-Host "Branch  : $GitBranch"
Write-Host "Commit  : $LocalCommit"
Write-Host "Server  : $SshHost"
Write-Host "Path    : $ServerPath"

Write-Host ""
Write-Host "Production:" `
    -ForegroundColor Green

Write-Host $ProductionUrl `
    -ForegroundColor Green

Write-Host ""
Write-Host "IMPORTANT:" `
    -ForegroundColor Yellow

Write-Host "npm/Vite runs locally on Windows."
Write-Host "StackCP does NOT need npm."
Write-Host "All pending Laravel migrations run automatically."
Write-Host "config:cache is intentionally NOT used."
Write-Host ""

Write-Host "Done." `
    -ForegroundColor Green
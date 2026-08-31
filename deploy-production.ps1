param(
    [string]$CommitMessage = "Deploy Scents by Aamir production"
)

$ErrorActionPreference = "Stop"

# ============================================================
# SCENTS BY AAMIR - ONE CLICK PRODUCTION DEPLOY
# ============================================================

$ProjectRoot = $PSScriptRoot

$GitBranch = "main"
$GitRemote = "origin"

$SshHost = "ssh.gb.stackcp.com"
$SshUser = "scentsbyaamir.com"
$SshKey  = "C:\Users\hp\.ssh\scentsbyaamir_github_actions_nopass"

$ServerPath = "/home/sites/41b/8/81d92349b7/public_html/shop/laravel12"

$Php = "/usr/php84/usr/bin/php"
$Composer = "/usr/local/bin/composer"

$ProductionUrl = "https://shop.scentsbyaamir.com"

# ============================================================
# HELPERS
# ============================================================

function Write-Step {
    param([string]$Message)

    Write-Host ""
    Write-Host "============================================================" `
        -ForegroundColor DarkGray
    Write-Host $Message -ForegroundColor Cyan
    Write-Host "============================================================" `
        -ForegroundColor DarkGray
}

function Assert-Success {
    param([string]$Step)

    if ($LASTEXITCODE -ne 0) {
        throw "$Step failed with exit code $LASTEXITCODE."
    }
}

# ============================================================
# PROJECT CHECK
# ============================================================

Set-Location $ProjectRoot

Write-Step "1/7 - Checking local Laravel project"

if (-not (Test-Path ".\artisan")) {
    throw "artisan not found. Script must be inside Laravel frontend root."
}

if (-not (Test-Path ".\package.json")) {
    throw "package.json not found."
}

if (-not (Test-Path $SshKey)) {
    throw "SSH private key not found: $SshKey"
}

git rev-parse --is-inside-work-tree | Out-Null
Assert-Success "Git repository check"

$currentBranch = (git branch --show-current).Trim()
Assert-Success "Git branch check"

if ($currentBranch -ne $GitBranch) {

    Write-Host "Switching to main branch..." -ForegroundColor Yellow

    git checkout $GitBranch

    Assert-Success "Git checkout"
}

# ============================================================
# LOCAL VITE BUILD
# ============================================================

Write-Step "2/7 - Building frontend locally"

$npm = Get-Command npm -ErrorAction SilentlyContinue

if (-not $npm) {
    throw "npm is not installed/found on this Windows machine."
}

Write-Host "npm version:" -ForegroundColor Green

npm --version

Assert-Success "npm version"

if (Test-Path ".\package-lock.json") {

    Write-Host ""
    Write-Host "Running npm ci..." -ForegroundColor Green

    npm ci

    Assert-Success "npm ci"
}
else {

    Write-Host ""
    Write-Host "Running npm install..." -ForegroundColor Yellow

    npm install

    Assert-Success "npm install"
}

Write-Host ""
Write-Host "Building Vite..." -ForegroundColor Green

npm run build

Assert-Success "npm run build"

if (-not (Test-Path ".\public\build\manifest.json")) {

    throw "public\build\manifest.json was not generated."
}

Write-Host ""
Write-Host "Vite build successful." -ForegroundColor Green

# ============================================================
# GIT COMMIT
# ============================================================

Write-Step "3/7 - Committing application"

git add -A

Assert-Success "git add"

$changes = git status --porcelain

if ($changes) {

    Write-Host "Creating Git commit..." -ForegroundColor Green

    git commit -m $CommitMessage

    Assert-Success "git commit"
}
else {

    Write-Host "No new changes to commit." -ForegroundColor Yellow
}

$LocalCommit = (git rev-parse --short HEAD).Trim()

# ============================================================
# GITHUB PUSH
# ============================================================

Write-Step "4/7 - Pushing main to GitHub"

git push $GitRemote $GitBranch

Assert-Success "git push"

Write-Host ""
Write-Host "GitHub commit: $LocalCommit" -ForegroundColor Green

# ============================================================
# CREATE REMOTE DEPLOY COMMAND
# ============================================================

Write-Step "5/7 - Preparing StackCP deployment"

#
# IMPORTANT:
# We deliberately use ONE SSH invocation.
#
# Previous script:
#   SSH test -> success
#   second SSH with piped bash -> authentication failure
#
# This version performs the entire deployment through one
# authenticated SSH command.
#

$RemoteCommand = @"
set -e;

echo '============================================================';
echo 'SCENTS BY AAMIR PRODUCTION DEPLOYMENT';
echo '============================================================';

echo '';
echo 'SSH CONNECTED';
pwd;

echo '';
echo '--- Entering production directory ---';
cd '$ServerPath';
pwd;

echo '';
echo '--- Fetching GitHub main ---';
git fetch '$GitRemote';

echo '';
echo '--- Checking out main ---';
git checkout '$GitBranch';

echo '';
echo '--- Resetting production to origin/main ---';
git reset --hard '$GitRemote/$GitBranch';

echo '';
echo '--- Production commit ---';
git rev-parse --short HEAD;

echo '';
echo '--- Installing Composer dependencies ---';
'$Php' '$Composer' install --no-dev --no-interaction --prefer-dist --optimize-autoloader;

echo '';
echo '--- Clearing old Laravel caches ---';
'$Php' artisan optimize:clear;

echo '';
echo '--- Running database migrations ---';
'$Php' artisan migrate --force;

echo '';
echo '--- Ensuring storage link exists ---';
'$Php' artisan storage:link || true;

echo '';
echo '--- Clearing runtime caches ---';
'$Php' artisan optimize:clear;

echo '';
echo '--- Building route cache ---';
'$Php' artisan route:cache;

echo '';
echo '--- Building Blade view cache ---';
'$Php' artisan view:cache;

echo '';
echo '--- Checking Vite production manifest ---';

if [ ! -f 'public/build/manifest.json' ]; then
    echo 'ERROR: public/build/manifest.json is missing.';
    exit 20;
fi;

echo 'Vite manifest OK';

echo '';
echo '--- Migration status ---';
'$Php' artisan migrate:status;

echo '';
echo '--- Laravel environment ---';
'$Php' artisan about --only=environment;

echo '';
echo '--- Admin order routes ---';
'$Php' artisan route:list --path=admin/orders;

echo '';
echo '--- Final Git commit ---';
git rev-parse --short HEAD;

echo '';
echo '============================================================';
echo 'PRODUCTION DEPLOYMENT SUCCESSFUL';
echo '============================================================';
"@

# Convert command to Base64.
#
# This avoids:
# - PowerShell pipe/STDIN issues
# - bash -s second SSH issue
# - multiline quoting problems
# - CRLF problems
#

$RemoteBytes = [System.Text.Encoding]::UTF8.GetBytes($RemoteCommand)

$RemoteBase64 = [Convert]::ToBase64String($RemoteBytes)

$ServerExecutor = "echo '$RemoteBase64' | base64 -d | bash"

# ============================================================
# ONE SSH SESSION
# ============================================================

Write-Step "6/7 - Deploying to StackCP"

Write-Host ""
Write-Host "Connecting to:" -ForegroundColor Green
Write-Host "$SshUser@$SshHost"
Write-Host ""

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

# ============================================================
# COMPLETE
# ============================================================

Write-Step "7/7 - Deployment completed"

Write-Host ""
Write-Host "DEPLOYMENT SUCCESSFUL" -ForegroundColor Green

Write-Host ""
Write-Host "Project : Scents by Aamir"
Write-Host "Branch  : $GitBranch"
Write-Host "Commit  : $LocalCommit"
Write-Host "Server  : $SshHost"
Write-Host "Path    : $ServerPath"

Write-Host ""
Write-Host "Production:" -ForegroundColor Green
Write-Host $ProductionUrl -ForegroundColor Green

Write-Host ""
Write-Host "Deployment flow:" -ForegroundColor Cyan
Write-Host "Local npm ci"
Write-Host "Local Vite build"
Write-Host "Git commit"
Write-Host "GitHub main push"
Write-Host "ONE StackCP SSH session"
Write-Host "git reset origin/main"
Write-Host "Composer install"
Write-Host "Laravel migrate"
Write-Host "Laravel cache clear"
Write-Host "route:cache"
Write-Host "view:cache"
Write-Host "production verification"

Write-Host ""
Write-Host "Server npm is NOT required." -ForegroundColor Yellow
Write-Host "config:cache is intentionally NOT used." -ForegroundColor Yellow
Write-Host ""
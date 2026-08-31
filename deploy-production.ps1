param(
    [string]$CommitMessage = "Deploy Scents by Aamir production",
    [switch]$SkipNpm
)

$ErrorActionPreference = "Stop"

# ============================================================
# Scents by Aamir - One Command Production Deployment
# Place this file in:
# E:\ScentsByAamirLaravel\frontend\deploy-production.ps1
#
# Run:
# powershell -ExecutionPolicy Bypass -File .\deploy-production.ps1
#
# Optional:
# powershell -ExecutionPolicy Bypass -File .\deploy-production.ps1 -CommitMessage "Fix order invoice"
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

function Write-Step {
    param([string]$Message)
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor DarkGray
    Write-Host $Message -ForegroundColor Cyan
    Write-Host "============================================================" -ForegroundColor DarkGray
}

function Assert-LastExitCode {
    param([string]$Step)
    if ($LASTEXITCODE -ne 0) {
        throw "$Step failed with exit code $LASTEXITCODE."
    }
}

Set-Location $ProjectRoot

Write-Step "1/7 - Checking local project"

if (-not (Test-Path ".\artisan")) {
    throw "artisan not found. Put this script inside E:\ScentsByAamirLaravel\frontend"
}

if (-not (Test-Path $SshKey)) {
    throw "SSH private key not found: $SshKey"
}

git rev-parse --is-inside-work-tree | Out-Null
Assert-LastExitCode "Git repository check"

$currentBranch = (git branch --show-current).Trim()
Assert-LastExitCode "Git branch check"

if ($currentBranch -ne $GitBranch) {
    Write-Host "Switching from '$currentBranch' to '$GitBranch'..." -ForegroundColor Yellow
    git checkout $GitBranch
    Assert-LastExitCode "git checkout $GitBranch"
}

Write-Step "2/7 - Staging and committing local changes"

git add .
Assert-LastExitCode "git add"

$pendingChanges = git status --porcelain

if ($pendingChanges) {
    Write-Host "Changes detected. Creating commit..." -ForegroundColor Green
    git commit -m $CommitMessage
    Assert-LastExitCode "git commit"
}
else {
    Write-Host "No local changes to commit. Continuing with current HEAD." -ForegroundColor Yellow
}

Write-Step "3/7 - Pushing main branch to GitHub"

git push $GitRemote $GitBranch
Assert-LastExitCode "git push"

Write-Step "4/7 - Testing SSH connection"

ssh `
    -o BatchMode=yes `
    -o IdentitiesOnly=yes `
    -o StrictHostKeyChecking=accept-new `
    -i $SshKey `
    "$SshUser@$SshHost" `
    "echo 'SSH connected successfully'; pwd"

Assert-LastExitCode "SSH connection"

Write-Step "5/7 - Updating production source and dependencies"

$NpmCommands = ""
if (-not $SkipNpm) {
    $NpmCommands = @"
echo '--- Installing frontend dependencies ---'
npm ci

echo '--- Building Vite assets ---'
npm run build
"@
}
else {
    $NpmCommands = "echo '--- npm install/build skipped by -SkipNpm ---'"
}

$RemoteScript = @"
set -e

cd '$ServerPath'

echo '--- Current production path ---'
pwd

echo '--- Fetching GitHub main ---'
git fetch '$GitRemote'
git checkout '$GitBranch'
git reset --hard '$GitRemote/$GitBranch'

echo '--- Installing Composer production dependencies ---'
'$Php' '$Composer' install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo '--- Clearing Laravel caches before migration ---'
'$Php' artisan optimize:clear

echo '--- Running all pending database migrations ---'
'$Php' artisan migrate --force

$NpmCommands

echo '--- Re-clearing stale runtime caches ---'
'$Php' artisan optimize:clear

echo '--- Caching routes ---'
'$Php' artisan route:cache

echo '--- Caching Blade views ---'
'$Php' artisan view:cache

echo '--- Migration status ---'
'$Php' artisan migrate:status

echo '--- Deployment complete ---'
git rev-parse --short HEAD
"@

$RemoteScript | ssh `
    -o BatchMode=yes `
    -o IdentitiesOnly=yes `
    -o StrictHostKeyChecking=accept-new `
    -i $SshKey `
    "$SshUser@$SshHost" `
    "bash -s"

Assert-LastExitCode "Remote production deployment"

Write-Step "6/7 - Checking production Laravel endpoint"

ssh `
    -o BatchMode=yes `
    -o IdentitiesOnly=yes `
    -i $SshKey `
    "$SshUser@$SshHost" `
    "cd '$ServerPath' && '$Php' artisan about --only=environment"

Assert-LastExitCode "Production Laravel health check"

Write-Step "7/7 - Finished"

Write-Host ""
Write-Host "DEPLOYMENT SUCCESSFUL" -ForegroundColor Green
Write-Host "Branch : $GitBranch"
Write-Host "Server : $SshHost"
Write-Host "Path   : $ServerPath"
Write-Host "URL    : https://shop.scentsbyaamir.com" -ForegroundColor Green
Write-Host ""
Write-Host "Important: this script intentionally does NOT run 'php artisan config:cache'." -ForegroundColor Yellow

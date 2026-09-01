$ErrorActionPreference = "Stop"

$SshHost = "ssh.gb.stackcp.com"
$SshUser = "scentsbyaamir.com"
$SshKey  = "C:\Users\hp\.ssh\scentsbyaamir_github_actions_nopass"
$ServerPath = "/home/sites/41b/8/81d92349b7/public_html/shop/laravel12"
$Php = "/usr/php84/usr/bin/php"

$remote = @"
set -e
cd '$ServerPath'
echo 'PROJECT PATH:'
pwd
echo ''
'$Php' artisan optimize:clear
'$Php' artisan migrate:status
'$Php' artisan route:list --path=account
'$Php' artisan storefront:mail-check
"@

$encoded = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($remote))
ssh -T -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new -i "$SshKey" "$SshUser@$SshHost" "echo '$encoded' | base64 -d | bash"

if ($LASTEXITCODE -ne 0) {
    throw "Server production check failed."
}

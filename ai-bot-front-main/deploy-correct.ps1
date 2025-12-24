# Deploy script
# Usage: .\deploy-correct.ps1

$ErrorActionPreference = "Stop"

# Configuration
$SSH_USER = "root"
$SSH_HOST = "193.111.63.104"
$SSH_PASS = "HJjuUJ73j0qE18hF2w"
$PROJECT_DIR = "D:\project\Subcloudy\ai-bot-front-main"
$REMOTE_PUBLIC = "/var/www/subcloudy/backend/public"
$PLINK = "C:\Program Files\PuTTY\plink.exe"
$PSCP = "C:\Program Files\PuTTY\pscp.exe"

Write-Host "=== Deployment Process ===" -ForegroundColor Green
Write-Host ""

# 1. Build project
Write-Host "1. Building project..." -ForegroundColor Yellow
Set-Location $PROJECT_DIR
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Build failed!" -ForegroundColor Red
    exit 1
}
Write-Host "Build complete" -ForegroundColor Green
Write-Host ""

# 2. Backup on server
Write-Host "2. Creating backup on server..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupCmd = "cd $REMOTE_PUBLIC; cp -r . ../public.backup.$timestamp; echo 'Backup created'"
& $PLINK -ssh "${SSH_USER}@${SSH_HOST}" -pw $SSH_PASS $backupCmd
Write-Host "Backup created" -ForegroundColor Green
Write-Host ""

# 3. Upload files
Write-Host "3. Uploading files to server..." -ForegroundColor Yellow
& $PSCP -pw $SSH_PASS -r "${PROJECT_DIR}\dist\*" "${SSH_USER}@${SSH_HOST}:${REMOTE_PUBLIC}/"
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Upload failed!" -ForegroundColor Red
    exit 1
}
Write-Host "Files uploaded" -ForegroundColor Green
Write-Host ""

# 4. Set permissions
Write-Host "4. Setting permissions..." -ForegroundColor Yellow
$permCmd = "chown -R www-data:www-data $REMOTE_PUBLIC; find $REMOTE_PUBLIC -type d -exec chmod 755 {} \;; find $REMOTE_PUBLIC -type f -exec chmod 644 {} \;; echo 'Permissions set'"
& $PLINK -ssh "${SSH_USER}@${SSH_HOST}" -pw $SSH_PASS $permCmd
Write-Host "Permissions set" -ForegroundColor Green
Write-Host ""

# 5. Verification
Write-Host "5. Verifying deployment..." -ForegroundColor Yellow
$checkCmd = "test -f ${REMOTE_PUBLIC}/index.html; ls -lh ${REMOTE_PUBLIC}/assets/index-*.js | head -1"
$result = & $PLINK -ssh "${SSH_USER}@${SSH_HOST}" -pw $SSH_PASS $checkCmd
Write-Host $result
if ($LASTEXITCODE -eq 0) {
    Write-Host "Deployment successful!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Check site: https://subcloudy.com/" -ForegroundColor Cyan
    Write-Host "Don't forget to clear browser cache (Ctrl+Shift+R)" -ForegroundColor Yellow
} else {
    Write-Host "ERROR: Verification failed!" -ForegroundColor Red
    exit 1
}

# nkhoj — First-time setup script (run from PowerShell in C:\project\nkhoj)
# Requires XAMPP installed at C:\xampp (PHP 8.2) and Composer on PATH

param(
    [string]$DbName     = "nkhoj",
    [string]$DbUser     = "root",
    [string]$DbPassword = ""
)

$PHP     = "C:\xampp\php\php.exe"
$MYSQL   = "C:\xampp\mysql\bin\mysql.exe"
$MYSQLD  = "C:\xampp\mysql\bin\mysqld.exe"

Write-Host "`n=== nkhoj Setup ===" -ForegroundColor Cyan

# 1. Check PHP
if (-not (Test-Path $PHP)) {
    Write-Error "PHP not found at $PHP. Please install XAMPP first."
    exit 1
}
Write-Host "[OK] PHP: $( & $PHP -r 'echo PHP_VERSION;' )" -ForegroundColor Green

# 2. Composer install
Write-Host "`n[...] Installing Composer dependencies..." -ForegroundColor Yellow
composer install --no-interaction
if ($LASTEXITCODE -ne 0) { Write-Error "Composer install failed."; exit 1 }
Write-Host "[OK] Composer dependencies installed" -ForegroundColor Green

# 3. Copy .env
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "[OK] .env created from .env.example" -ForegroundColor Green
} else {
    Write-Host "[--] .env already exists, skipping" -ForegroundColor Gray
}

# 4. Generate app key
& $PHP artisan key:generate
Write-Host "[OK] App key generated" -ForegroundColor Green

# 5. Create database
Write-Host "`n[...] Creating database '$DbName'..." -ForegroundColor Yellow
$createDb = "CREATE DATABASE IF NOT EXISTS \`$DbName\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($DbPassword -ne "") {
    & $MYSQL -u $DbUser -p$DbPassword -e $createDb
} else {
    & $MYSQL -u $DbUser -e $createDb
}
if ($LASTEXITCODE -ne 0) {
    Write-Warning "Could not create database automatically. Make sure XAMPP MySQL is running."
    Write-Host "Run manually: CREATE DATABASE nkhoj CHARACTER SET utf8mb4;" -ForegroundColor Yellow
} else {
    Write-Host "[OK] Database '$DbName' ready" -ForegroundColor Green
}

# 6. Run migrations
Write-Host "`n[...] Running migrations..." -ForegroundColor Yellow
& $PHP artisan migrate --force
Write-Host "[OK] Migrations complete" -ForegroundColor Green

# 7. Seed database
Write-Host "`n[...] Seeding database (categories, tags, AI prompts)..." -ForegroundColor Yellow
& $PHP artisan db:seed --force
Write-Host "[OK] Database seeded" -ForegroundColor Green

# 8. Create storage symlink
& $PHP artisan storage:link 2>$null
Write-Host "[OK] Storage link created" -ForegroundColor Green

Write-Host "`n=== Setup Complete! ===" -ForegroundColor Green
Write-Host "Start the dev server with:" -ForegroundColor Cyan
Write-Host "  C:\xampp\php\php.exe artisan serve" -ForegroundColor White
Write-Host "  Open: http://127.0.0.1:8000`n" -ForegroundColor White

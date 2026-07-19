<#
.SYNOPSIS
    HotelHub HMS — License Server Setup (Windows Server)

.DESCRIPTION
    Complete setup script for HotelHub license server on Windows Server
    with IIS or XAMPP stack. Performs PHP checks, MariaDB setup, repo clone,
    .env configuration, RSA key generation, scheduled tasks, and credential output.

.PARAMETER LicenseDomain
    The FQDN for the license server (e.g. license.hotelhub.id).

.PARAMETER AdminEmail
    Email for the initial admin user.

.PARAMETER RepoUrl
    Git repository URL to clone.

.PARAMETER RepoBranch
    Git branch to checkout.

.EXAMPLE
    .\setup.ps1 -LicenseDomain "license.hotelhub.id" -AdminEmail "ops@hotelhub.id"
#>

[CmdletBinding()]
param(
    [string]$LicenseDomain = "license.hotelhub.id",
    [string]$AdminEmail = "ops@hotelhub.id",
    [string]$AdminPassword,
    [string]$RepoUrl = "https://github.com/hotelhub/hms.git",
    [string]$RepoBranch = "main",
    [string]$AppDir = "C:\inetpub\wwwroot\hotelhub-license",
    [string]$DBHost = "127.0.0.1",
    [string]$DBPort = "3306",
    [string]$DBDatabase = "hotel_license",
    [string]$DBUsername = "hotel"
)

$ErrorActionPreference = "Stop"
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

# ── Helper functions ────────────────────────────────────────────────
function Write-Step($msg) {
    Write-Host "`n▶ $msg" -ForegroundColor Cyan
}
function Write-OK($msg) {
    Write-Host "[OK]  $msg" -ForegroundColor Green
}
function Write-Warn($msg) {
    Write-Host "[WARN] $msg" -ForegroundColor Yellow
}
function Write-Err($msg) {
    Write-Host "[ERROR] $msg" -ForegroundColor Red
}

# ── Check admin rights ──────────────────────────────────────────────
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Err "This script must be run as Administrator."
    exit 1
}

# ── Interactive prompts for missing values ──────────────────────────
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-Host "  HotelHub License Server — Windows Setup"
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
Write-Host ""

if (-not $LicenseDomain) { $LicenseDomain = Read-Host "License server domain" }
if (-not $AdminEmail)    { $AdminEmail = Read-Host "Admin email" }
if (-not $AdminPassword) {
    $securePass = Read-Host "Admin password" -AsSecureString
    $AdminPassword = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass)
    )
}

if (-not $AdminPassword) {
    $AdminPassword = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 24 | ForEach-Object { [char]$_ })
}

$secureMysqlRoot = Read-Host "MySQL root password [auto-generate if blank]" -AsSecureString
$MysqlRootPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secureMysqlRoot)
)
if (-not $MysqlRootPass) {
    $MysqlRootPass = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 24 | ForEach-Object { [char]$_ })
}

$secureMysqlApp = Read-Host "MySQL app password [auto-generate if blank]" -AsSecureString
$MysqlAppPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secureMysqlApp)
)
if (-not $MysqlAppPass) {
    $MysqlAppPass = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 24 | ForEach-Object { [char]$_ })
}

# ═══════════════════════════════════════════════════════════════════
# 1. Check PHP 8.3
# ═══════════════════════════════════════════════════════════════════
Write-Step "1/10  Checking PHP 8.3"
$phpExe = $null
$phpPaths = @(
    "C:\php83\php.exe",
    "C:\Program Files\PHP\php8.3\php.exe",
    "C:\xampp\php\php.exe",
    "C:\laragon\bin\php\php-8.3\php.exe"
)

foreach ($p in $phpPaths) {
    if (Test-Path $p) { $phpExe = $p; break }
}

if (-not $phpExe) {
    # Try from PATH
    $phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
}

if (-not $phpExe) {
    Write-Warn "PHP 8.3 not found in common locations."
    Write-Warn "Please install PHP 8.3 from https://windows.php.net/download/"
    Write-Warn "Or install XAMPP/Laragon with PHP 8.3 and re-run this script."
    $manual = Read-Host "Enter full path to php.exe (or press Enter to abort)"
    if ($manual) { $phpExe = $manual } else { exit 1 }
}

$phpVersion = & $phpExe -r "echo PHP_VERSION;"
Write-OK "PHP $phpVersion found at: $phpExe"

# Check required extensions
$requiredExt = @("pdo_mysql", "openssl", "bcmath", "mbstring", "redis", "gd", "gmp", "curl", "fileinfo")
$missingExt = @()
foreach ($ext in $requiredExt) {
    $loaded = & $phpExe -r "echo extension_loaded('$ext') ? '1' : '0';"
    if ($loaded -ne "1") { $missingExt += $ext }
}
if ($missingExt.Count -gt 0) {
    Write-Warn "Missing PHP extensions: $($missingExt -join ', ')"
    Write-Warn "Enable them in php.ini and re-run."
}

# ═══════════════════════════════════════════════════════════════════
# 2. Check MariaDB/MySQL
# ═══════════════════════════════════════════════════════════════════
Write-Step "2/10  Checking MariaDB/MySQL"
$mysqlExe = (Get-Command mysql -ErrorAction SilentlyContinue).Source
if (-not $mysqlExe) {
    $mysqlPaths = @(
        "C:\Program Files\MariaDB 10.11\bin\mysql.exe",
        "C:\Program Files\MariaDB 11\bin\mysql.exe",
        "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe",
        "C:\xampp\mysql\bin\mysql.exe",
        "C:\laragon\bin\mysql\mysql-8.0\bin\mysql.exe"
    )
    foreach ($p in $mysqlPaths) {
        if (Test-Path $p) { $mysqlExe = $p; break }
    }
}

if (-not $mysqlExe) {
    Write-Warn "MySQL/MariaDB client not found. Install MariaDB 10.11+ and re-run."
    Write-Warn "Download: https://mariadb.org/download/"
} else {
    Write-OK "MySQL client found at: $mysqlExe"

    # Create database
    $env:MYSQL_PWD = $MysqlRootPass
    $createDbSql = @"
CREATE DATABASE IF NOT EXISTS \`${DBDatabase}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DBUsername}'@'localhost' IDENTIFIED BY '${MysqlAppPass}';
GRANT ALL PRIVILEGES ON \`${DBDatabase}\`.* TO '${DBUsername}'@'localhost';
FLUSH PRIVILEGES;
"@
    try {
        & $mysqlExe -u root -e $createDbSql
        Write-OK "Database '${DBDatabase}' created."
    } catch {
        Write-Warn "Could not create database automatically: $_"
        Write-Warn "Please create it manually before proceeding."
    }
    $env:MYSQL_PWD = $null
}

# ═══════════════════════════════════════════════════════════════════
# 3. Check Composer
# ═══════════════════════════════════════════════════════════════════
Write-Step "3/10  Checking Composer"
$composerExe = (Get-Command composer -ErrorAction SilentlyContinue).Source
if (-not $composerExe) {
    Write-Warn "Composer not found. Download from https://getcomposer.org/download/"
    $manual = Read-Host "Enter full path to composer.phar (or press Enter to install automatically)"
    if ($manual) {
        $composerExe = "php $manual"
    } else {
        Write-Host "Downloading Composer..."
        Invoke-WebRequest -Uri "https://getcomposer.org/composer.phar" -OutFile "$env:TEMP\composer.phar"
        $composerExe = "php $env:TEMP\composer.phar"
    }
} else {
    Write-OK "Composer found at: $composerExe"
}

# ═══════════════════════════════════════════════════════════════════
# 4. Clone repository
# ═══════════════════════════════════════════════════════════════════
Write-Step "4/10  Cloning repository"
if (Test-Path $AppDir) {
    Write-Warn "$AppDir already exists."
    $reclone = Read-Host "Remove and re-clone? [y/N]"
    if ($reclone -eq "y") {
        Remove-Item -Recurse -Force $AppDir
        git clone --branch $RepoBranch --depth 1 $RepoUrl $AppDir
    }
} else {
    git clone --branch $RepoBranch --depth 1 $RepoUrl $AppDir
}
Write-OK "Repository cloned to: $AppDir"

# ═══════════════════════════════════════════════════════════════════
# 5. Configure .env
# ═══════════════════════════════════════════════════════════════════
Write-Step "5/10  Configuring .env"
Push-Location $AppDir

# Copy .env.example or template
if (Test-Path "$ScriptDir\.env.example") {
    Copy-Item "$ScriptDir\.env.example" ".env" -Force
} elseif (Test-Path ".env.example") {
    Copy-Item ".env.example" ".env" -Force
}

# Generate APP_KEY
$appKey = & $phpExe artisan key:generate --show --no-interaction 2>$null
if (-not $appKey) {
    $appKey = & $phpExe artisan key:generate --show 2>$null
}

# Write .env
$envContent = @"
APP_MODE=vendor
APP_ENV=production
APP_KEY=${appKey}
APP_URL=https://${LicenseDomain}
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=en
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=${DBHost}
DB_PORT=${DBPort}
DB_DATABASE=${DBDatabase}
DB_USERNAME=${DBUsername}
DB_PASSWORD=${MysqlAppPass}

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

LICENSE_SERVER_ROLE=issuer
LICENSE_TOKEN_TTL=31536000
LICENSE_VENDOR_BASE_URL=https://${LicenseDomain}
LICENSE_PUBLIC_KEY_PATH=storage/app/vendor-public.pem
LICENSE_PUBLIC_KEY_HASH=

ADMIN_NAME="HotelHub Admin"
ADMIN_EMAIL=${AdminEmail}
ADMIN_PASSWORD=${AdminPassword}

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@hotelhub.id"
MAIL_FROM_NAME="HotelHub License"
"@

Set-Content -Path ".env" -Value $envContent -Encoding UTF8
Write-OK ".env configured."

# ═══════════════════════════════════════════════════════════════════
# 6. Composer install + migrations
# ═══════════════════════════════════════════════════════════════════
Write-Step "6/10  Installing dependencies + running migrations"
Invoke-Expression "$phpExe $composerExe install --no-dev --optimize-autoloader --no-interaction --no-progress"
Invoke-Expression "$phpExe artisan migrate --force --no-interaction"
Invoke-Expression "$phpExe artisan db:seed --class=RolesAndPermissionsSeeder --force --no-interaction 2>`$null"
Invoke-Expression "$phpExe artisan db:seed --class=PlansSeeder --force --no-interaction 2>`$null"
Write-OK "Dependencies installed and database migrated."

# ═══════════════════════════════════════════════════════════════════
# 7. Generate RSA keypair
# ═══════════════════════════════════════════════════════════════════
Write-Step "7/10  Generating RSA keypair"
$privateKeyPath = "$AppDir\storage\app\vendor-private.pem"
$publicKeyPath = "$AppDir\storage\app\vendor-public.pem"

$storageDir = Split-Path $privateKeyPath -Parent
if (-not (Test-Path $storageDir)) {
    New-Item -ItemType Directory -Path $storageDir -Force | Out-Null
}

if (Test-Path $privateKeyPath) {
    Write-Warn "Existing keypair found. Backing up..."
    $timestamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    Move-Item $privateKeyPath "${privateKeyPath}.bak.${timestamp}" -Force
    if (Test-Path $publicKeyPath) {
        Move-Item $publicKeyPath "${publicKeyPath}.bak.${timestamp}" -Force
    }
}

# Use PHP's openssl to generate (most reliable on Windows)
$genScript = @"
\$config = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];
\$key = openssl_pkey_new(\$config);
openssl_pkey_export(\$key, \$privateKeyOut);
file_put_contents('${privateKeyPath}', \$privateKeyOut);
\$publicKeyOut = openssl_pkey_get_details(\$key)['key'];
file_put_contents('${publicKeyPath}', \$publicKeyOut);
echo 'OK';
"@

$genResult = & $phpExe -r $genScript 2>&1
if ($genResult -eq "OK") {
    Write-OK "RSA keypair generated via PHP openssl."
} else {
    Write-Warn "PHP openssl generation failed: $genResult"
    Write-Warn "Trying openssl.exe..."
    $opensslExe = (Get-Command openssl -ErrorAction SilentlyContinue).Source
    if ($opensslExe) {
        & $opensslExe genrsa -out $privateKeyPath 2048
        & $opensslExe rsa -in $privateKeyPath -pubout -out $publicKeyPath
        Write-OK "RSA keypair generated via openssl.exe."
    } else {
        Write-Err "Cannot generate RSA keys. Install OpenSSL or enable PHP openssl extension."
        Pop-Location
        exit 1
    }
}

# Set strict permissions
try {
    icacls $privateKeyPath /inheritance:r /grant "SYSTEM:(R)" /grant "Administrators:(R)" 2>$null
    icacls $publicKeyPath /inheritance:r /grant "SYSTEM:(R)" /grant "Administrators:(R)" /grant "Everyone:(R)" 2>$null
} catch { Write-Warn "Could not set ACLs: $_" }

# Compute SHA256 hash
$pubKeyContent = Get-Content $publicKeyPath -Raw
$sha256 = [System.Security.Cryptography.SHA256]::Create()
$hashBytes = $sha256.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($pubKeyContent))
$pubKeyHash = ($hashBytes | ForEach-Object { $_.ToString("x2") }) -join ""

# Update .env with hash
Add-Content -Path ".env" -Value "LICENSE_PUBLIC_KEY_HASH=${pubKeyHash}" -Encoding UTF8
Write-OK "Public key hash: ${pubKeyHash}"

# ═══════════════════════════════════════════════════════════════════
# 8. Cache optimization
# ═══════════════════════════════════════════════════════════════════
Write-Step "8/10  Optimizing caches"
& $phpExe artisan config:cache
& $phpExe artisan route:cache
& $phpExe artisan view:cache
& $phpExe artisan event:cache
Write-OK "Caches warmed."

# ═══════════════════════════════════════════════════════════════════
# 9. Setup Scheduled Tasks (Windows equivalent of cron)
# ═══════════════════════════════════════════════════════════════════
Write-Step "9/10  Setting up Scheduled Tasks"

$taskName = "HotelHub License Scheduler"
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existingTask) {
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

$action = New-ScheduledTaskAction -Execute $phpExe `
    -Argument "artisan schedule:run" `
    -WorkingDirectory $AppDir

$trigger = New-ScheduledTaskTrigger -Daily -At "00:00" -RepetitionInterval (New-TimeSpan -Minutes 1) `
    -RepetitionDuration (New-TimeSpan -Hours 23 -Minutes 59)

$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries `
    -StartWhenAvailable -RestartCount 3 -RestartInterval (New-TimeSpan -Minutes 5)

Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger `
    -Principal $principal -Settings $settings -Force | Out-Null
Write-OK "Scheduled task '${taskName}' created (runs every minute)."

# Also create dedicated tasks for specific commands
$dedicatedTasks = @(
    @{Name="HotelHub License Expiry Check"; Cmd="license:check-expired"; Interval="0:05"; Desc="Every 5 min"},
    @{Name="HotelHub Audit Checkpoint"; Cmd="audit:checkpoint"; Interval="1:00"; Desc="Hourly"},
    @{Name="HotelHub Telemetry Aggregate"; Cmd="telemetry:aggregate"; Interval="1:00"; Desc="Hourly"}
)

foreach ($dt in $dedicatedTasks) {
    $existing = Get-ScheduledTask -TaskName $dt.Name -ErrorAction SilentlyContinue
    if ($existing) { Unregister-ScheduledTask -TaskName $dt.Name -Confirm:$false }
    
    $dtAction = New-ScheduledTaskAction -Execute $phpExe `
        -Argument "artisan $($dt.Cmd)" `
        -WorkingDirectory $AppDir
    $dtTrigger = New-ScheduledTaskTrigger -Once -At (Get-Date) `
        -RepetitionInterval (New-TimeSpan -Minutes ([int]($dt.Interval.Split(':')[0]) * 60 + [int]($dt.Interval.Split(':')[1])))
    Register-ScheduledTask -TaskName $dt.Name -Action $dtAction -Trigger $dtTrigger `
        -Principal $principal -Settings $settings -Force | Out-Null
    Write-OK "Task '($($dt.Name))' created (${($dt.Desc)})."
}

# ═══════════════════════════════════════════════════════════════════
# 10. Create admin user
# ═══════════════════════════════════════════════════════════════════
Write-Step "10/10  Creating admin user"
$tinkerScript = @"
`$admin = \App\Models\User::updateOrCreate(
    ['email' => '${AdminEmail}'],
    [
        'name' => 'HotelHub Admin',
        'password' => bcrypt('${AdminPassword}'),
        'email_verified_at' => now(),
    ]
);
`$admin->assignRole('super-admin');
echo 'Admin user: ' . `$admin->email . PHP_EOL;
"@

try {
    & $phpExe artisan tinker --execute=$tinkerScript 2>&1
    Write-OK "Admin user created."
} catch {
    Write-Warn "Could not create admin via tinker. Create manually:"
    Write-Warn "  php artisan license:create-admin --email=${AdminEmail}"
}

Pop-Location

# ═══════════════════════════════════════════════════════════════════
# IIS Configuration (if IIS is present)
# ═══════════════════════════════════════════════════════════════════
$iisInstalled = Get-WindowsFeature -Name Web-Server -ErrorAction SilentlyContinue
if ($iisInstalled -and $iisInstalled.Installed) {
    Write-Step "  Configuring IIS"

    # Install PHP via FastCGI if not already
    $fastCgiInstalled = Get-WindowsFeature -Name Web-CGI -ErrorAction SilentlyContinue
    if ($fastCgiInstalled -and -not $fastCgiInstalled.Installed) {
        Install-WindowsFeature -Name Web-CGI, Web-ASP, Web-Asp-Net45
    }

    # Create application pool
    $appPoolName = "HotelHubLicense"
    Remove-WebAppPool -Name $appPoolName -ErrorAction SilentlyContinue
    New-WebAppPool -Name $appPoolName -Force
    Set-ItemProperty -Path "IIS:\AppPools\${appPoolName}" -Name processModel.identityType -Value "ApplicationPoolIdentity"
    Set-ItemProperty -Path "IIS:\AppPools\${appPoolName}" -Name managedRuntimeVersion -Value ""

    # Create website
    Remove-Website -Name $appPoolName -ErrorAction SilentlyContinue
    New-Website -Name $appPoolName -PhysicalPath "${AppDir}\public" -ApplicationPool $appPoolName -Port 80

    Write-OK "IIS website '${appPoolName}' created."

    Write-Warn "IMPORTANT: Configure IIS URL Rewrite rules for Laravel:"
    Write-Warn "  1. Install URL Rewrite module: https://www.iis.net/downloads/microsoft/url-rewrite"
    Write-Warn "  2. Add web.config in ${AppDir}\public with rewrite rules"
    Write-Warn "  3. Set Handler Mapping for *.php → FastCGI (${phpExe})"
}

# ═══════════════════════════════════════════════════════════════════
# Print credentials summary
# ═══════════════════════════════════════════════════════════════════
Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║     HotelHub License Server — Setup Complete!              ║" -ForegroundColor Green
Write-Host "╠════════════════════════════════════════════════════════════╣" -ForegroundColor Green
Write-Host ("║  URL:       https://{0}" -f $LicenseDomain.PadRight(42)) -ForegroundColor Green
Write-Host ("║  App Path:  {0}" -f $AppDir.PadRight(42)) -ForegroundColor Green
Write-Host ("║                                                            ║") -ForegroundColor Green
Write-Host ("║  Admin Email: {0}" -f $AdminEmail.PadRight(38)) -ForegroundColor Green
Write-Host ("║  Admin Pass:  {0}" -f $AdminPassword.PadRight(38)) -ForegroundColor Green
Write-Host ("║                                                            ║") -ForegroundColor Green
Write-Host ("║  MySQL Root:  {0}" -f $MysqlRootPass.PadRight(38)) -ForegroundColor Green
Write-Host ("║  MySQL App:   {0}" -f $MysqlAppPass.PadRight(38)) -ForegroundColor Green
Write-Host ("║  Database:    {0}" -f $DBDatabase.PadRight(38)) -ForegroundColor Green
Write-Host ("║                                                            ║") -ForegroundColor Green
Write-Host ("║  RSA Public Key Hash:                                      ║") -ForegroundColor Green
Write-Host ("║  ${pubKeyHash}" -f $pubKeyHash) -ForegroundColor Green
Write-Host ("║                                                            ║") -ForegroundColor Green
Write-Host ("║  IMPORTANT — Save these credentials securely!              ║") -ForegroundColor Green
Write-Host ("╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green

# Save credentials file
$credsFile = "$AppDir\storage\app\.credentials.txt"
@"
HotelHub License Server Credentials
====================================
Date: $(Get-Date -Format "yyyy-MM-ddTHH:mm:ssZ")
Domain: https://${LicenseDomain}
Admin Email: ${AdminEmail}
Admin Password: ${AdminPassword}
MySQL Root: ${MysqlRootPass}
MySQL App: ${MysqlAppPass}
Database: ${DBDatabase}
RSA Public Key Hash: ${pubKeyHash}
"@ | Set-Content -Path $credsFile -Encoding UTF8

Write-OK "Credentials saved to: ${credsFile}"
Write-OK "Setup complete!"

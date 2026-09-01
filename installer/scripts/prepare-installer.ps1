# Prepare installer/staging for Inno Setup compile (run before compiling Darzibaba.iss)
param(
    [string]$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path,
    [string]$StagingDir = (Join-Path $PSScriptRoot '..\staging'),
    [string]$PhpDesktopZipUrl = 'https://github.com/cztomczak/phpdesktop/releases/download/chrome-v130.1/phpdesktop-chrome-130.1-php-8.3.zip',
    [switch]$SkipDownload
)

$ErrorActionPreference = 'Stop'

$installerDir = Join-Path $ProjectRoot 'installer'
$cacheZip = Join-Path $installerDir 'cache\phpdesktop-chrome.zip'
$cacheDir = Join-Path $installerDir 'cache\phpdesktop-extract'

if (Test-Path -LiteralPath $StagingDir) {
    Remove-Item -LiteralPath $StagingDir -Recurse -Force
}
New-Item -ItemType Directory -Path $StagingDir -Force | Out-Null

if (-not $SkipDownload) {
    if (-not (Test-Path -LiteralPath $cacheZip)) {
        New-Item -ItemType Directory -Path (Split-Path $cacheZip) -Force | Out-Null
        Write-Host "Downloading PHP Desktop..."
        Invoke-WebRequest -Uri $PhpDesktopZipUrl -OutFile $cacheZip -UseBasicParsing
    }
    if (Test-Path -LiteralPath $cacheDir) {
        Remove-Item -LiteralPath $cacheDir -Recurse -Force
    }
    Expand-Archive -LiteralPath $cacheZip -DestinationPath $cacheDir -Force
}

$chromeExe = Get-ChildItem -LiteralPath $cacheDir -Recurse -Filter 'phpdesktop-chrome.exe' -File -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $chromeExe) {
    throw "phpdesktop-chrome.exe not found in extract. Delete installer\cache and re-run."
}
$pdRoot = $chromeExe.Directory.FullName
Write-Host "PHP Desktop root: $pdRoot"

Get-ChildItem -LiteralPath $pdRoot -Force | ForEach-Object {
    if ($_.Name -eq 'www') { return }
    Copy-Item -LiteralPath $_.FullName -Destination (Join-Path $StagingDir $_.Name) -Recurse -Force
}

$settingsSrc = Join-Path $installerDir 'phpdesktop\settings.json'
if (Test-Path -LiteralPath $settingsSrc) {
    Copy-Item -LiteralPath $settingsSrc -Destination (Join-Path $StagingDir 'settings.json') -Force
}

$wwwDir = Join-Path $StagingDir 'www'
New-Item -ItemType Directory -Path $wwwDir -Force | Out-Null

$excludeDirs = @('installer', '.git', '.cursor', 'agent-transcripts', 'tests', 'views - Copy', 'node_modules', 'user_guide', 'vendor')
$robocopyArgs = @(
    $ProjectRoot,
    $wwwDir,
    '/E',
    '/XD', 'installer', '.git', '.cursor', 'agent-transcripts', 'tests', 'views - Copy', 'node_modules', 'user_guide',
    '/XF', 'readme.rst',
    '/NFL', '/NDL', '/NJH', '/NJS', '/NC', '/NS', '/NP'
)
& robocopy @robocopyArgs | Out-Null
if ($LASTEXITCODE -ge 8) {
    throw "robocopy failed with exit code $LASTEXITCODE"
}
# Copy only runtime-needed vendor subsets if full vendor excluded — include vendor for mpdf
$vendorSrc = Join-Path $ProjectRoot 'vendor'
if (Test-Path -LiteralPath $vendorSrc) {
    $vendorDest = Join-Path $wwwDir 'vendor'
    & robocopy $vendorSrc $vendorDest '/E' '/XD', 'tests', 'test', 'docs', '/NFL', '/NDL', '/NJH', '/NJS', '/NC', '/NS', '/NP' | Out-Null
}

$phpIni = Join-Path $StagingDir 'php\php.ini'
if (Test-Path -LiteralPath $phpIni) {
    & (Join-Path $PSScriptRoot 'enable-php-mysqli.ps1') -PhpIniPath $phpIni
}

$chromeExe = Join-Path $StagingDir 'phpdesktop-chrome.exe'
$darziExe = Join-Path $StagingDir 'Darzibaba.exe'
if (Test-Path -LiteralPath $chromeExe) {
    Copy-Item -LiteralPath $chromeExe -Destination $darziExe -Force
}

Copy-Item -LiteralPath (Join-Path $installerDir 'start-darzibaba.bat') -Destination (Join-Path $StagingDir 'start-darzibaba.bat') -Force
Copy-Item -LiteralPath (Join-Path $installerDir 'stop-darzibaba.bat') -Destination (Join-Path $StagingDir 'stop-darzibaba.bat') -Force

Write-Host "Staging ready: $StagingDir"
Write-Host "Next: Compile installer\Darzibaba.iss with Inno Setup"

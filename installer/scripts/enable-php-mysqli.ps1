param(
    [Parameter(Mandatory = $true)]
    [string]$PhpIniPath
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $PhpIniPath)) {
    Write-Warning "php.ini not found: $PhpIniPath"
    exit 0
}

$content = Get-Content -LiteralPath $PhpIniPath -Raw -Encoding UTF8

# PHP Desktop ships php_*.dll names; only uncomment existing lines (no duplicate short names).
$dllMap = @{
    'mysqli'   = 'php_mysqli.dll'
    'mbstring' = 'php_mbstring.dll'
    'openssl'  = 'php_openssl.dll'
    'curl'     = 'php_curl.dll'
    'fileinfo' = 'php_fileinfo.dll'
    'gd'       = 'php_gd2.dll'
}

foreach ($short in $dllMap.Keys) {
    $dll = $dllMap[$short]
    if ($content -match "(?m)^\s*extension\s*=\s*$([regex]::Escape($dll))\s*$") {
        continue
    }
    $content = $content -replace ";extension\s*=\s*$([regex]::Escape($dll))", "extension=$dll"
    if ($content -notmatch "(?m)^\s*extension\s*=\s*$([regex]::Escape($dll))\s*$") {
        $content = $content.TrimEnd() + "`r`nextension=$dll`r`n"
    }
}

# Remove legacy duplicate short extension lines added by older prepare runs.
foreach ($short in $dllMap.Keys) {
    $dll = $dllMap[$short]
    if ($content -match "(?m)^\s*extension\s*=\s*$([regex]::Escape($dll))\s*$") {
        $content = $content -replace "(?m)^\s*extension\s*=\s*$short\s*$\r?\n?", ''
    }
}

$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($PhpIniPath, $content, $utf8NoBom)
Write-Host "Enabled PHP extensions in $PhpIniPath"

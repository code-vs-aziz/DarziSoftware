param(
    [Parameter(Mandatory = $true)]
    [string]$DatabasePhpPath,
    [string]$Hostname = 'localhost',
    [string]$Port = '3310',
    [string]$Username = 'root',
    [string]$Password = '',
    [string]$Database = 'alibaba'
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $DatabasePhpPath)) {
    Write-Error "database.php not found: $DatabasePhpPath"
}

$content = Get-Content -LiteralPath $DatabasePhpPath -Raw -Encoding UTF8

function Set-DbKey {
    param([string]$Text, [string]$Key, [string]$Value)
    $safe = $Value -replace "'", "\'"
    $pattern = "('$Key'\s*=>\s*)'[^']*'"
    return [regex]::Replace($Text, $pattern, ('${1}''' + $safe + ''''), 1)
}

$content = Set-DbKey $content 'hostname' $Hostname
$content = Set-DbKey $content 'port' $Port
$content = Set-DbKey $content 'username' $Username
$content = Set-DbKey $content 'password' $Password
$content = Set-DbKey $content 'database' $Database

$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($DatabasePhpPath, $content, $utf8NoBom)
Write-Host "Updated database.php: host=$Hostname port=$Port database=$Database"

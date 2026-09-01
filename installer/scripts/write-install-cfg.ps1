param(
    [Parameter(Mandatory = $true)]
    [string]$CfgPath,
    [Parameter(Mandatory = $true)]
    [string]$XamppRoot,
    [string]$Hostname = 'localhost',
    [string]$Port = '3310',
    [string]$Username = 'root',
    [string]$Password = '',
    [string]$Database = 'alibaba',
    [string]$AppDir = ''
)

$ErrorActionPreference = 'Stop'

$lines = @(
    '; Darzibaba desktop install config — do not edit unless you know XAMPP paths',
    '[XAMPP]',
    "Root=$XamppRoot",
    '[Database]',
    "Hostname=$Hostname",
    "Port=$Port",
    "Username=$Username",
    "Password=$Password",
    "Database=$Database",
    '[App]',
    "InstallDir=$AppDir"
)

$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($CfgPath, ($lines -join "`r`n") + "`r`n", $utf8NoBom)
Write-Host "Wrote $CfgPath"

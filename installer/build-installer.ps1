# One-step: prepare staging folder for Inno Setup
$ErrorActionPreference = 'Stop'
$here = Split-Path -Parent $MyInvocation.MyCommand.Path
& (Join-Path $here 'scripts\prepare-installer.ps1') @args
Write-Host ""
Write-Host "Open installer\Darzibaba.iss in Inno Setup 6 and click Compile."
Write-Host "Output: installer\output\Darzibaba-Setup.exe"

# Darzibaba Desktop Installer (PHP Desktop + XAMPP MySQL)

Desktop app with an embedded window (not a normal browser tab). **XAMPP is used only for MySQL** — pick `C:\xampp` or `D:\xampp` at install time. **No database import**; the installer only writes connection settings.

## What gets installed

| Item | Location |
|------|----------|
| App + PHP Desktop | `C:\Program Files\Darzibaba` (or chosen folder) |
| CodeIgniter app | `{InstallDir}\www\` |
| Desktop launcher | `Darzibaba.exe` (PHP Desktop) |
| Shortcut | `start-darzibaba.bat` → starts XAMPP MySQL, then the desktop window |
| Config | `install.cfg` (XAMPP path + DB settings) |

## Before you install (end user)

1. Install **XAMPP** on the PC (`C:\xampp` or `D:\xampp`).
2. Start **MySQL** in XAMPP Control Panel (or use the Darzibaba shortcut, which runs `mysql_start.bat`).
3. Create/import database **`alibaba`** (or your name) — phpMyAdmin, mysqldump, etc.
4. Note the **MySQL port** in `xampp\mysql\bin\my.ini` (often `3306` or `3310`).

## Build the setup `.exe` (developer)

### Requirements

- Windows 10/11
- [Inno Setup 6](https://jrsoftware.org/isinfo.php)
- PowerShell 5+

### Steps

1. Open PowerShell in the project root (`Darzibaba`).

2. Prepare staging (downloads PHP Desktop ~170 MB first time):

```powershell
cd installer\scripts
.\prepare-installer.ps1
```

3. Compile installer:

- Open `installer\Darzibaba.iss` in Inno Setup → **Build → Compile**, or from PowerShell:

```powershell
& "${env:ProgramFiles(x86)}\Inno Setup 6\ISCC.exe" "installer\Darzibaba.iss"
```

- Output: `installer\output\Darzibaba-Setup.exe`

4. Distribute `Darzibaba-Setup.exe` to users.

### Rebuild without re-downloading PHP Desktop

```powershell
.\prepare-installer.ps1 -SkipDownload
```

## Install wizard (end user)

1. Run **Darzibaba-Setup.exe**.
2. Choose install folder (default `Program Files\Darzibaba`).
3. Enter **XAMPP root** (e.g. `D:\xampp`).
4. Enter **MySQL port**, database name, user, password.
5. Finish → optional launch.

## Daily use

- **Start:** Desktop shortcut **Darzibaba** (or Start Menu).
- **Stop:** **Stop Darzibaba** shortcut (optional; stops app and MySQL).

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Blank / DB error | Start MySQL in XAMPP; confirm port in `install.cfg` matches `my.ini` |
| `Darzibaba.exe` missing | Re-run `prepare-installer.ps1` before compiling setup |
| mysqli error | Re-run `prepare-installer.ps1` to enable extensions in bundled `php\php.ini` |
| Wrong language/URL | App uses PHP Desktop on `127.0.0.1`; not `htdocs` |

## Uninstall

Windows Settings → Apps → Darzibaba. Removes the install folder only; **does not** remove XAMPP or your MySQL data.

## File map

```
installer/
  Darzibaba.iss          Inno Setup script
  start-darzibaba.bat    Launcher (copied to install dir)
  stop-darzibaba.bat
  phpdesktop/settings.json
  scripts/
    prepare-installer.ps1
    patch-database.ps1
    write-install-cfg.ps1
    enable-php-mysqli.ps1
  staging/               Created by prepare-installer.ps1 (not in git)
  cache/                 Downloaded PHP Desktop zip
  output/                Compiled Darzibaba-Setup.exe
```

/**
 * Darzi Baba Desktop — wraps the REAL PHP ERP (same UI).
 * Starts local PHP server automatically so Wi‑Fi / remote server
 * being off does not matter (DB must be local MariaDB).
 */
const { app, BrowserWindow, dialog, shell } = require('electron');
const { spawn } = require('child_process');
const path = require('path');
const http = require('http');
const fs = require('fs');

const HOST = '127.0.0.1';
const PORT = process.env.DARZI_DESKTOP_PORT || '8080';
const ROOT = path.resolve(__dirname, '..');
const START_URL = `http://${HOST}:${PORT}/index.php/Login`;
const HOME_URL = `http://${HOST}:${PORT}/index.php/Welcome`;
const APP_ICON = path.join(__dirname, 'build', 'icon.png');

let phpProc = null;
let mainWindow = null;
let startedPhpOurselves = false;

function resolveAppIcon() {
	if (fs.existsSync(APP_ICON)) {
		return APP_ICON;
	}
	const fallback = path.join(ROOT, 'images', 'favicon-512.png');
	return fs.existsSync(fallback) ? fallback : undefined;
}

function phpBinary() {
  const candidates = [
    process.env.DARZI_PHP_BIN,
    '/usr/local/bin/php',
    '/opt/homebrew/bin/php',
    '/usr/bin/php',
    'php',
  ].filter(Boolean);
  for (const bin of candidates) {
    if (bin === 'php') return bin;
    if (fs.existsSync(bin)) return bin;
  }
  return 'php';
}

function probe(url, timeoutMs = 1500) {
  return new Promise((resolve) => {
    const req = http.get(url, (res) => {
      res.resume();
      resolve(res.statusCode > 0);
    });
    req.on('error', () => resolve(false));
    req.setTimeout(timeoutMs, () => {
      req.destroy();
      resolve(false);
    });
  });
}

async function waitUntilUp(maxMs = 20000) {
  const started = Date.now();
  while (Date.now() - started < maxMs) {
    // Any response from PHP built-in server means it's listening
    const ok = await probe(`http://${HOST}:${PORT}/offline-app.html`);
    if (ok) return true;
    await new Promise((r) => setTimeout(r, 400));
  }
  return false;
}

function startPhpIfNeeded() {
  return probe(`http://${HOST}:${PORT}/`).then(async (already) => {
    if (already) {
      startedPhpOurselves = false;
      return true;
    }
    const bin = phpBinary();
    // Larger upload / memory for Configuration → Database Backup import/export
    // (shop dumps can exceed several hundred MB when gzipped)
    const args = [
      '-d', 'upload_max_filesize=2048M',
      '-d', 'post_max_size=2048M',
      '-d', 'memory_limit=512M',
      '-d', 'max_execution_time=0',
      '-S', `${HOST}:${PORT}`,
      '-t', ROOT,
    ];
    phpProc = spawn(bin, args, {
      cwd: ROOT,
      stdio: 'ignore',
      detached: false,
      env: process.env,
    });
    startedPhpOurselves = true;
    phpProc.on('exit', () => {
      phpProc = null;
    });
    return waitUntilUp();
  });
}

function stopPhp() {
  if (phpProc && startedPhpOurselves) {
    try {
      phpProc.kill('SIGTERM');
    } catch (_) {
      /* ignore */
    }
    phpProc = null;
  }
}

function createWindow() {
  const icon = resolveAppIcon();
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 860,
    minWidth: 980,
    minHeight: 640,
    title: 'Darzi Baba ERP',
    backgroundColor: '#eef2f6',
    ...(icon ? { icon } : {}),
    webPreferences: {
      contextIsolation: true,
      nodeIntegration: false,
      sandbox: true,
    },
  });

  if (process.platform === 'darwin' && icon && app.dock) {
    try {
      app.dock.setIcon(icon);
    } catch (_) {
      /* ignore */
    }
  }

  mainWindow.loadURL(START_URL);

  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    shell.openExternal(url);
    return { action: 'deny' };
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.whenReady().then(async () => {
  const up = await startPhpIfNeeded();
  if (!up) {
    dialog.showErrorBox(
      'Darzi Baba ERP',
      'Local PHP server start nahi hua.\n\nCheck: php installed hai? Port ' +
        PORT +
        ' free hai?\nDB local (127.0.0.1) hona chahiye — internet ki zaroorat nahi.'
    );
  }
  createWindow();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    } else if (mainWindow) {
      mainWindow.loadURL(HOME_URL);
    }
  });
});

app.on('window-all-closed', () => {
  stopPhp();
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('before-quit', () => {
  stopPhp();
});

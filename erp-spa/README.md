# Darzi Baba — Offline SPA ERP

**Separate project** on branch `feature/offline-spa-erp`.  
Classic PHP ERP (`application/`, `offline-app.html`, etc.) is **not replaced**.

## Why this exists

The PHP ERP UI needs a running PHP server to render pages.  
This SPA is a **standalone React PWA** that:

- Runs without PHP (after first install/cache)
- Saves customers / orders / cash / delivery / transfer into IndexedDB
- Syncs to the same `/api/erp_sync/*` endpoints when classic ERP is online

## Run (dev)

```bash
# Terminal 1 — classic ERP (for sync / token)
cd /path/to/Darzibaba
php -S 127.0.0.1:8080

# Terminal 2 — SPA
cd erp-spa
npm install
npm run dev
```

Open `http://127.0.0.1:5174`

1. Login once in classic ERP (`http://127.0.0.1:8080`) in the **same browser**
2. In SPA click **Sync Now** (issues sync token + bootstrap cache)
3. Stop PHP — SPA still works offline; queue stays local until next Sync

## Build / Install as app

```bash
cd erp-spa
npm run build
npm run preview
```

Then Chrome → Install app (PWA).

## Coexistence

| App | Path | Role |
|-----|------|------|
| Classic PHP | `/index.php/...` | Full shop UI + reports + WhatsApp |
| Offline Desk (static) | `/offline-app.html` | Lightweight cold-start desk |
| **This SPA** | `erp-spa/` (port 5174 / its host) | Full offline-first staff UI (growing) |

Merge to main only when you are ready; until then stay on `feature/offline-spa-erp`.

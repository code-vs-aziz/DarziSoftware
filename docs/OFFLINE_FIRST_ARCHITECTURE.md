# Offline-First Architecture — Darzi Baba ERP

**System:** CodeIgniter 3 shop ERP (server-rendered PHP + jQuery) + MariaDB  
**Status:** Production foundation (Phase 1) implemented  
**Priority order:** Data integrity → Reliability → Security → Sync accuracy → Recovery → UX → Performance

---

## 1. Feasibility verdict

| Question | Answer |
|----------|--------|
| Can this ERP support offline-first? | **Yes**, with a **PWA + IndexedDB + Sync API** layer on top of the existing PHP/MariaDB source of truth. |
| Full rewrite required? | **No.** Keep the live ERP as authority; add client persistence + idempotent sync. |
| Is LocalStorage enough? | **No.** Business data and the sync queue use **IndexedDB**. |
| Can cash / delivery / transfers run offline? | **No (by design).** Shared money and collaborative workflow stay **online-only**. |

---

## 2. Current stack (constraints)

```
Browser (jQuery / PHP views)
  → CodeIgniter controllers (session + RBAC + CSRF)
    → Models / MariaDB (auto-increment PKs: customer.code, order_mst.trnno, …)
```

- Staff writes are largely **full-page form POSTs**, not SPA JSON.
- Public `/api/*` is for the Next.js customer site — **not** staff orders.
- Sequential business numbers (`man_napno`, `trnno`) are **server-assigned**.

Offline-first therefore requires:

1. A **staff Sync API** (idempotent, token-authenticated).
2. A **persistent browser database** (IndexedDB).
3. A **Service Worker** for shell caching + background sync hooks.
4. **Client UUIDs** mapped to server auto-increment IDs on sync.

---

## 3. Target architecture

```
┌──────────────────────────┐
│  ERP UI (existing PHP)   │
│  + Offline status chip   │
│  + Form intercept (off)  │
└────────────┬─────────────┘
             │
     online / offline
             │
┌────────────▼─────────────┐
│  IndexedDB (local DB)    │
│  customers, measurements │
│  orders, meta, conflicts │
│  sync_queue              │
└────────────┬─────────────┘
             │
        Sync Engine
             │
┌────────────▼─────────────┐
│  /api/erp_sync/*         │
│  health · issue_token    │
│  bootstrap · push · pull │
└────────────┬─────────────┘
             │
┌────────────▼─────────────┐
│  Live MariaDB (truth)    │
└──────────────────────────┘
```

---

## 4. Capability matrix

| Feature | Offline mode |
|---------|----------------|
| View cached customers / open orders | **Read-only** |
| Create / edit customer | **Fully offline** → sync queue |
| Create measurement (basic) | **Fully offline** → sync queue |
| Create order | **Fully offline** → sync queue (provisional number) |
| Edit order lines after print | Read-only / online preferred |
| Cash receipt / payment / pending pay | **Fully offline** → sync queue (server re-validates balances) |
| Delivery (`dmst` / `ddtl`) | **Fully offline** → sync queue |
| Transfer stages (HO/CUT/STIT) + bulk | **Fully offline** → sync queue (conflict if already moved) |
| Local Print / PDF (browser print) | **Offline** on already-loaded pages |
| New report date-range queries | **Online preferred** (cached page Print/PDF OK) |
| WhatsApp send | **Online only** |
| RBAC / config / website admin | **Online only** |

When an online-only action is attempted offline, the UI shows a clear reason (not a blank failure).

---

## 5. Connectivity detection

Do **not** trust `navigator.onLine` alone.

| Signal | Meaning |
|--------|---------|
| `navigator.onLine === false` | Treat as offline immediately |
| Periodic `GET /api/erp_sync/health` | Backend reachable |
| Health fail while browser “online” | **Offline (server down)** |

UI states: `online` · `offline` · `pending` · `syncing` · `synced` · `sync_failed`

---

## 6. Local database (IndexedDB)

Database name: `darzi_erp_offline` (versioned).

| Store | Purpose |
|-------|---------|
| `meta` | last sync, device id, cursor, auth token meta |
| `customers` | snapshot + offline drafts |
| `measurements` | snapshot + offline drafts |
| `orders` | snapshot + offline drafts |
| `masters` | suit types, styles, buttons (read cache) |
| `sync_queue` | durable outbound ops |
| `conflicts` | records needing manual resolution |
| `id_map` | client_uuid → server_id |

Queue row fields: `op_id`, `entity_type`, `entity_client_id`, `op_type`, `payload`, `created_at`, `status`, `retry_count`, `last_attempt_at`, `last_error`, `idempotency_key`.

Survives refresh, browser restart, and OS restart (same browser profile).

---

## 7. Unique IDs & numbering

| Concern | Strategy |
|---------|----------|
| Primary identity offline | **UUID v4** (`client_uuid`) on every create |
| Server PK | Remains AUTO_INCREMENT; stored in `id_map` after sync |
| Display while offline | Provisional: `OFF-C-######`, `OFF-O-######` |
| `man_napno` | Client may suggest; server validates uniqueness; conflicts → remapped or conflict queue |
| Idempotency | Unique `(device_id, idempotency_key)` on server `erp_sync_ops` |

Never rely on client-side auto-increment as the permanent PK.

---

## 8. Sync protocol

### Push (local → server)

1. Check health.
2. Authenticate (session cookie **or** offline sync Bearer token).
3. Send pending queue ops in dependency order: customer → measurement → order.
4. Each op applied inside a **DB transaction**; result recorded by `idempotency_key`.
5. Retries are safe: already-applied keys return the original server result (**idempotent**).
6. On success: mark queue row `synced`, update local entity + `id_map`.
7. Partial failure: stop or continue remaining independent ops; never drop successfully applied rows.

### Pull (server → local)

- Cursor: `updated_at` / sync watermark per entity.
- Download changed customers, measurements, recent open orders, master data.
- Merge into IndexedDB without wiping pending local drafts.

### Triggers

- **Automatic** when health flips offline → online (debounced).
- **Manual** “Sync Now” button (always available when pending > 0 or user forces).

---

## 9. Conflict resolution (not global LWW)

| Entity / field | Policy |
|----------------|--------|
| Create with same `client_uuid` | Idempotent — no conflict |
| Customer phone / name / CNIC both changed | **Conflict queue** — staff must choose |
| Customer city / remarks only one side | Auto-merge non-overlapping fields |
| Order create | Server accepts; no overwrite of existing `trnno` |
| Order already delivered on server while local edit pending | Server **rejects**; local marked conflict |
| Cash / transfer / delivery | Never queued offline |

Sensitive money/workflow mutations are never last-write-wins.

---

## 10. Offline authentication

| Rule | Detail |
|------|--------|
| Passwords | **Never** stored in the browser |
| Sync token | Issued while online; opaque random; **SHA-256** hash stored server-side |
| Storage | Token in IndexedDB (same-origin); not in LocalStorage plaintext lists |
| Validity | Configurable (default **7 days**); refresh when online |
| Device binding | `device_id` UUID stored with token |
| Logout | Revokes server token + clears local auth meta (queue data kept until synced or user confirms wipe) |
| Expired while offline | Read cached data + create queued drafts allowed; **push blocked** until re-login online |

---

## 11. Reliability guarantees

- Queue is **durable** (IndexedDB).
- Ops are **idempotent** via `idempotency_key`.
- Push is **retryable**; crash mid-sync resumes without duplicates.
- Server applies each op in a transaction; partial batch ≠ partial corrupt rows.
- Service Worker does **not** invent business writes — only the Sync Engine does.

---

## 12. Phased delivery

| Phase | Scope |
|-------|--------|
| **1** | Health, IndexedDB, queue, status UI, Sync Now, token auth, bootstrap/pull, customer/order create, SW shell |
| **2** | Offline cash, pending pay, delivery, transfer (+ bulk); local Print/PDF; WhatsApp stays online-only |
| **3 (current)** | **Offline Desk** PWA (`offline-app.html`) — cold start without PHP; SW serves desk when server is down |
| **4** | Measurement offline editor, conflict UI, Background Sync API, multi-device cursors |

---

## 13. Files map

| Area | Path |
|------|------|
| Plan (this doc) | `docs/OFFLINE_FIRST_ARCHITECTURE.md` |
| SQL | `application/sql/offline_sync_schema.sql` |
| Sync API | `application/controllers/api/Erp_sync.php` |
| Sync service | `application/libraries/Erp_sync_service.php` |
| Model | `application/models/Erp_sync_model.php` |
| Client | `js/offline/*.js` |
| Service worker | `sw.js` |
| Manifest | `manifest.webmanifest` |
| UI chip | `application/views/partials/offline_status_chip.php` |

---

## 14. Ops checklist

1. Run `application/sql/offline_sync_schema.sql` on the live DB.
2. Ensure HTTPS (required for Service Workers in production).
3. Confirm `/api/erp_sync/health` returns JSON from the shop network.
4. Test scenarios in `docs/OFFLINE_FIRST_TEST_PLAN.md`.

---

*Server MariaDB remains the central source of truth. The browser is a resilient edge cache + write-ahead sync queue for permitted operations.*

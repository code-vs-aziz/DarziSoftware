# Darzi ERP — Online/Offline Reliability Audit Report

**Date:** 2026-11-23 (updated — remaining limitations pass)  
**Scope:** Existing offline architecture (`darzi_erp_offline`, sync API, SW **v34**)  
**Constraint:** No architecture rebuild; same IndexedDB name; additive schema only

---

## A. Existing Architecture

Staff ERP remains server-rendered PHP. Offline layer:

1. **IndexedDB** `darzi_erp_offline` (v2) — `customers`, `measurements`, `orders`, `masters` (via `meta`), `meta`, `sync_queue`, `id_map`, `conflicts`  
   - Rows scoped by `owner_user`; server-backed keys use `{user}:srv:{id}` to avoid cross-user collisions
2. **Service Worker** `darzi-erp-safe-v34` — shell + page caches; never intercepts POST
3. **Connectivity** — `navigator.onLine` + `/api/erp_sync/health`
4. **Sync engine** — token auth → push queue (dependency-ordered) → pull/bootstrap deltas
5. **Form intercept** — **queueable** writes are durable local-first online *and* offline; then immediate push when online
6. **Queueable:** customer, measurement, order, cash, pending_pay, delivery, transfer  
   **Online-only:** WhatsApp, RBAC, configuration, website, new report queries

Flow: `UI → IndexedDB + sync_queue → /api/erp_sync/push → MariaDB → pull → IndexedDB`

---

## B. Issues Found (original + remaining)

| ID | Severity | Issue | Status |
|----|----------|-------|--------|
| C1 | Critical | Failed push marked synced via `duplicate` | **Fixed** (prior pass) |
| C2 | Critical | Pull overwrote pending drafts | **Fixed** (prior pass) |
| C3 | Critical | Shared IndexedDB across users | **Fixed** (this pass — owner scope) |
| H1–H6 / M1–M4 | High/Med | Sync lock, conflicts, delivery, FK rewrite, retries | **Fixed** (prior pass) |
| R1 | High | Conflict UI list-only (no Keep Local/Server) | **Fixed** (this pass) |
| R2 | High | Online mid-POST disconnect lost writes | **Fixed** (queue-first) |
| R3 | High | Multi-user snapshot bleed in same browser | **Fixed** (owner + scoped keys) |
| R4 | Medium | `row_version` / sync columns unverified | **Addressed** (ensure + `schema_status` API; live DB **Not Tested**) |
| M5 | Low | Docs said cash never queued | **Clarified** here — cash **is** queueable |

---

## C. Fixes Implemented

### Prior pass (critical sync integrity)

| File | Purpose |
|------|---------|
| `Erp_sync_service.php` / `Erp_sync_model.php` | Failed-op retry; measurement insert-only; delivery already-delivered conflict |
| `js/offline/sync-engine.js` | `res.ok` only; pending draft protection; locks; FK enrich; retry cap |

### This pass (remaining limitations)

| File | Purpose |
|------|---------|
| `js/offline/ui.js` + `css/offline-erp.css` | Conflict panel: **Keep Local** / **Keep Server**; schema status line |
| `js/offline/sync-engine.js` | `resolveConflict()`; preserve `local_payload`; `fetchSchemaStatus()`; user activate on token |
| `js/offline/forms.js` | Queue-first for queueable forms **online and offline**; immediate sync when online |
| `js/offline/db.js` | DB v2; `owner_user`; `activateUser` / scoped meta; `scopedLocalId` |
| `js/offline/bootstrap.js` | Activate session user; schema check after online warm |
| `Erp_sync_service.php` | `force_keep_local`; `resolve_conflict()`; `schema_status()` |
| `Erp_sync_model.php` | `resolve_open_conflicts()` (history retained) |
| `api/Erp_sync.php` + `routes.php` | `/resolve_conflict`, `/schema_status`, `/lists` |
| `application/sql/offline_sync_columns_safe.sql` | Documented additive ALTERs (no deletes) |
| `sw.js` / `Head.php` / `offline-app.html` | Cache/script bump **v34** |
| `js/offline/sync-integrity-check.js` | 20 static + logic checks |
| `application/language/english/offline_lang.php` | Conflict / schema / synced strings |

---

## D. Testing Results

| Test | Mode | Result | Notes |
|---|---|---|---|
| Static integrity guards (20 checks) | Both | **Pass** | `node js/offline/sync-integrity-check.js` |
| PHP syntax lint | Server | **Pass** | Modified PHP files |
| User isolation logic (scoped keys / owner filter) | Unit | **Pass** | In integrity script |
| Conflict Keep Local / Keep Server code paths | Code review | **Pass** | UI + client resolve + server API |
| Online queue-first + mid-sync disconnect handling | Code review | **Pass** | Local queue retained if push fails |
| Schema ensure + `schema_status` API | Code review | **Pass** | Additive `ensure_schema()` |
| Live MariaDB column inspection | Server | **Not Tested** | MySQL `127.0.0.1:3310` unreachable in this environment |
| Login (browser) | Online | **Not Tested** | No live ERP HTTP session here |
| Offline order create + sync | Offline→Online | **Not Tested** | Requires Chrome + running shop |
| Offline payment / ledger | Offline | **Not Tested** | Requires Chrome + running shop |
| Disconnect during active API request | Transition | **Not Tested** | Code path ready; needs DevTools offline |
| Repeated reconnect / interrupted sync | Transition | **Code review Pass** | Lock + retry cap + pending preserved |
| Two users same Chrome profile | Both | **Code review Pass** | `activateUser` + owner filter; needs manual QA |
| Two tabs simultaneous sync | Both | **Code review Pass** | `navigator.locks`; needs manual QA |
| Duplicate financial transactions | Both | **Code review Pass** | Idempotency + delivery guard |

---

## E. Data Safety

- Pending queue rows stay until `res.ok`, conflict resolve, or `failed_permanent`.
- Conflict rows keep `local_payload` / history; status becomes `resolved_keep_*` (not deleted).
- Keep Local re-queues with `force_keep_local` (new idempotency key). Keep Server discards the op only after confirmation.
- Delivery/cash conflicts allow **Keep Server** only (forcing local re-delivery is unsafe).
- Online queueable submits are persisted **before** sync; network loss mid-push leaves durable pending work.
- Logout warns if pending; does **not** wipe queue or snapshots.
- Per-user: reads filter `owner_user`; writes stamp owner; keys scoped — other users’ local business data is not shown.
- Schema migration is additive only (`ensure_schema` / `offline_sync_columns_safe.sql`).

---

## F. Remaining Risks

1. **Interactive Chrome QA still required** on a staging shop (orders, cash, dual-user, dual-tab, DevTools offline mid-request).
2. **Live DB schema** not inspected here — run `/api/erp_sync/schema_status` after login once MySQL is up; apply ensure if `missing` is non-empty.
3. **Queueable online writes** now go through the sync API path (not classic `Order::process` redirects). Behavior matches offline sync implementations; edge-case PHP-only side effects (if any) should be spot-checked.
4. **Conflict UX** is confirm-via-button only (no field-level merge editor).
5. **Legacy unowned IndexedDB rows** are claimed by the first user after upgrade; later users do not see them.

---

## G. Final Verdict

### **Production Ready with Known Limitations**

All previously listed remaining limitations have **code fixes** and static verification.  
Interactive browser and live-database scenarios remain **Not Tested** in this environment — complete the manual QA list below before calling the shop fully certified.

### Manual QA (staging)

1. Login → open Sync panel → confirm “Sync schema ready”  
2. Offline: create customer → measurement → order → cash → refresh → Sync Now online  
3. Online: submit order, toggle DevTools Offline during sync — expect pending, then sync on reconnect (no duplicate)  
4. Force a customer conflict → Keep Local and Keep Server once each  
5. User A pending logout → User B login — B must not see A’s customers/queue  
6. Two tabs Sync Now with pending queue — no duplicate cash/orders  

### Commands

```bash
node js/offline/sync-integrity-check.js
# After deploy / hard refresh: Service Worker darzi-erp-safe-v34
```

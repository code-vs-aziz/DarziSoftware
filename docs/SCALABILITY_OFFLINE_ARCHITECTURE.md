# Scalability, Performance & Offline Architecture Review

**System:** DarziSoftware (CodeIgniter 3 + MariaDB/MySQL + server-rendered PHP)  
**Audience:** Owner + technical stakeholders  
**Date:** 11 September 2026  

---

## 1. Executive verdict

| Question | Answer |
|----------|--------|
| Can today’s system run a **single shop** with ~5–20 concurrent staff? | **Yes**, if indexes are applied and the fixes below stay in place. |
| Will it stay smooth at **100k+ orders** / heavier concurrency without changes? | **No** — several screens still load too much data into PHP/browser. |
| Is full **offline-first local DB** the right next architecture? | **Yes as a phased PWA layer** — see `docs/OFFLINE_FIRST_ARCHITECTURE.md`. Live MariaDB remains source of truth; IndexedDB + sync queue for permitted shop writes. Cash/delivery/transfers stay online-only. |
| What should we do now? | Apply `offline_sync_schema.sql`, verify `/api/erp_sync/health`, run the offline test plan. Keep bounded queries / indexes from this review. |

---

## 2. Current architecture (reality check)

```
Browser (jQuery / DataTables)
    → PHP CodeIgniter controllers (sync request/response)
        → Models / SQL (MariaDB)
        → Optional local WhatsApp Node bridge (Chromium)
```

- **Not** a mobile/API-first app with many thin clients.
- Most “API calls” are **full page / AJAX HTML** round-trips to the same PHP app.
- Sessions & cache are **files on one machine** — fine for one shop server; not multi-node SaaS.
- WhatsApp is a **local process**, not a horizontally scalable cloud service.

This is a classic **shop-floor ERP monolith**. Offline-first sync is a different product class (field apps, POS tablets). For a shared order/cash system, **the database on the server must remain the source of truth**.

---

## 3. Bottlenecks found (ranked)

### Critical

1. **Transfer / workflow lists** historically loaded the full active queue into PHP + browser (client-side DataTables).  
   - **Fix applied:** SQL `LIMIT 500` on `get_order_order_Detai2`.  
   - **Still needed:** true server-side DataTables (page/search in SQL).

2. **Ready-for-delivery** scanned all matching lines, then sliced in PHP.  
   - **Fix applied:** two-phase query — cap distinct order IDs in SQL (≤500/1000), then load lines only for those IDs.

3. **WhatsApp / notifications** can block PHP workers (HTTP waits up to 20s × N messages). Header status polling added load when bridge was down.  
   - **Fix applied:** web process batch 5 (was 30); status cached 15s; slower poll intervals.  
   - **Still needed:** cron/CLI worker so UI only enqueues.

4. **Delivery write race** used `MAX(trnno)` for `dmst` → concurrent deliveries could corrupt `ddtl` links.  
   - **Fix applied:** `insert_id()` + DB transaction around delivery/cash/ddtl/transfer updates.

### High

5. **Reports** can materialize large result sets + PDF in memory (was up to ~3 years).  
   - **Fix applied:** interactive range capped to **366 days**.  
   - **Still needed:** async export for huge CSV/PDF; stream downloads.

6. **Indexes** incomplete for latest-transfer / notification claim paths.  
   - **Fix applied:** added composites in `application/sql/performance_indexes.sql` — **run this on production DB**.

7. **Dashboard cache** invalidated inside order-line loops; short TTL.  
   - **Fix applied:** clear once after order save; TTL **300s**.

8. **Client-side DataTables** still default on many screens — browser holds all rows.

### Medium

9. File sessions serialize same-user tabs; single-node only.  
10. N+1 patterns (print multi-garment, transfer bulk, reminder scan).  
11. No Redis / job queue / observability (slow-query log, APM).

---

## 4. Fixes implemented in this pass

| Change | File(s) | Effect |
|--------|---------|--------|
| Ready-delivery SQL ID cap | `Order_query_model.php` | Less RAM/CPU as history grows |
| Transfer list hard LIMIT 500 | `Order_query_model.php` | Prevents full-table hydrate |
| Delivery `insert_id` + transaction | `order.php` `process_del` | Concurrency safety |
| Dashboard cache once + 300s TTL | `order.php`, `Dashboard_data_model.php` | Fewer stampede queries |
| Extra indexes | `performance_indexes.sql` | Faster joins / queue claim |
| Notify batch 5 | `Notifications.php` | Shorter web request blocks |
| Bridge status cache 15s + slower poll | `Whatsapp_bridge.php`, `bridge-status-indicator.js` | Fewer worker waits |
| Report date max 1 year | `Report_registry.php` | Memory/timeout guard |

**Action required on live DB:**

```bash
mysql ... < application/sql/performance_indexes.sql
```

---

## 5. Scale roadmap (recommended order)

### Phase A — Next 2–4 weeks (must for growth)
1. Server-side pagination for Transfer, Order status, Ready delivery, large reports.  
2. CLI cron for WhatsApp queue (`php index.php cli/process_notifications`).  
3. Verify indexes with `EXPLAIN` on ready/transfer queries.  
4. Cap DataTables page-length UI to ≤500.  
5. Wrap remaining cash payment updates in `SELECT … FOR UPDATE` transactions.

### Phase B — 1–3 months
1. “Current workflow state” table (tiny indexed) vs scanning transfer history.  
2. Redis sessions/cache if multi-user load or second app server.  
3. Async report export job + download link.  
4. Slow query log + weekly review.

### Phase C — Only if product needs it
1. Multi-branch tenancy (separate review).  
2. Read replicas for heavy reports.  
3. Optional **read-only offline pack** for tablets (see below).

---

## 6. Offline / local database idea — architect evaluation

### Your proposed flow

`Server DB → API → Local DB` · write local · sync later

### Is it recommended for DarziSoftware?

**Not as the default architecture for the whole ERP.**

Reasons specific to *this* system:

1. **Shared mutable money data** — advances, deliveries, cash ledger, balances. Two counters creating the same delivery offline = financial corruption.  
2. **Workflow state** — HO → CUT → STIT is collaborative; offline forks create conflicts (“already transferred”).  
3. **Current stack is server-rendered PHP**, not a thick client with a local SQLite engine. Offline-first implies a **new client** (Electron/PWA/Flutter) + sync API rewrite — months of work.  
4. **Shop LAN** usually has the server on-site; outages are rarer than field-sales apps. Fix **server reliability + backups** first.  
5. WhatsApp bridge already depends on a **local Node process**; full ERP offline does not remove that dependency for messaging.

### When local storage *is* recommended

| Approach | Use when | Recommendation |
|----------|----------|----------------|
| **HTTP/browser cache + short TTL** | Repeated dropdowns, dashboard KPIs | **Yes now** (already partially done) |
| **Server file/Redis cache** | Expensive aggregates | **Yes** |
| **Read-only offline snapshot** (customers + open orders for viewing) | Frequent internet drops, view-only need | **Maybe later** |
| **Full offline-first bidirectional sync** | Field agents offline hours; eventual consistency OK | **No for core cash/orders** unless you rebuild the client |

### Answers to your 10 questions

1. **Recommended?** Prefer **server source of truth + caching**. Full offline-first: only for a future companion app, not the main CI3 UI.  
2. **Pros/cons of full local DB**  
   - Pros: works offline; snappy reads.  
   - Cons: sync bugs, duplicates, conflict UX, security of local data, large rewrite, hard audits.  
3. **Local DB vs cache vs both**  
   - **Now:** server cache + bounded queries.  
   - **Later (optional):** read-only local cache (IndexedDB/SQLite) for lookups.  
   - **Avoid:** local authoritative writes for cash/orders without a long sync design.  
4. **Offline-capable implementation (pragmatic)**  
   - Keep booking/delivery **online-required**.  
   - Optional: PWA caches last customer search results / open transfer list as **read-only**.  
   - Queue WhatsApp sends server-side (already a queue table) — not client-side.  
5. **Sync model if you ever do writes offline**  
   - Assign **client UUID** per draft order.  
   - Server accepts idempotent `PUT /orders/{uuid}`.  
   - Pull changes via `updated_at` cursor / version vector.  
6. **Conflicts**  
   - Last-write-wins is **unsafe** for money.  
   - Prefer: server rejects conflicting state transitions; UI shows “already delivered”.  
   - Manual resolve queue for rare cases.  
7. **Prevent duplicate orders**  
   - Idempotency keys (`client_request_id` unique).  
   - Never generate IDs only on client; server AUTO_INCREMENT remains canonical.  
8. **Server down behavior**  
   - Show clear “Server unavailable”.  
   - Allow **read-only** cached lists if present.  
   - Block payment/delivery/transfer mutations.  
9. **Auto sync on reconnect**  
   - Flush outbound queue with idempotency; pull deltas; refresh caches.  
10. **Changes required for full sync architecture**  
   - REST/JSON API layer, auth tokens, `updated_at` on all tables, conflict policy, new client with SQLite/IndexedDB, background sync worker, admin conflict UI, extensive tests. **Estimate: large product project, not a patch.**

### Recommended target architecture (future-proof without overbuilding)

```
[Source of truth: MariaDB]
        ↑↓
[PHP app + bounded queries + Redis cache (optional)]
        ↑↓
[Browser: progressive enhancement]
   - Online: normal ERP
   - Degraded: read-only cached screens + “reconnect” banner
        ↓
[Async workers]
   - WhatsApp queue
   - Big report exports
```

---

## 7. Reliability checklist (ops)

- Nightly DB backup + restore drill.  
- MariaDB on SSD; `innodb_buffer_pool` sized for data.  
- PHP-FPM worker count matched to CPU; request timeout for reports.  
- Keep WhatsApp bridge supervised (`launchd`/`pm2`) on shop PC.  
- Monitor disk for Chromium/session growth under `bridge/whatsapp-bridge/.wwebjs_auth`.  
- Apply `performance_indexes.sql` after every schema change.  
- **WhatsApp cron (required):**  
  `* * * * * cd /path/to/Darzibaba && php index.php cron process_notifications 40 >> /tmp/darzi-wa-cron.log 2>&1`  
  Optional HTTP: set `CRON_HTTP_SECRET` then hit `/index.php/cron/process_notifications?secret=...`  
- Transfer pending lists use **server-side DataTables** (page size ≤ 100).  

---

## 8. Bottom line

**Performance/scalability:** The system can serve a live single shop **if** we keep bounding data, apply indexes, and move WhatsApp off the request path. Unbounded lists and sync WhatsApp were the main crash/slowness risks; several are already mitigated in code.

**Offline local DB:** Architecturally attractive for field apps, but **wrong default** for a shared tailor ERP with cash and workflow. Invest first in **server performance + backups + async jobs + caching**; add **read-only offline** later only if real outages demand it.

---

*End of architecture review.*

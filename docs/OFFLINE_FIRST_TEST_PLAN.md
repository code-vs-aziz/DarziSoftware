# Offline-First Test Plan

Run these against a staging shop browser profile after applying `offline_sync_schema.sql`.

| # | Scenario | Expected |
|---|----------|----------|
| 0 | Kill PHP, open desktop PWA / `offline-app.html` | Offline Desk loads from cache; customer/order/cash forms work; Sync waits until server back |
| 1 | Disconnect network mid customer create | Customer saved locally; UI “Saved Offline — Pending Sync”; queue +1 |
| 2 | Disconnect mid order create | Order draft in IndexedDB + queue; provisional `OFF-O-…` number |
| 3 | Kill PHP/backend while internet up | Health fails → Offline (server); writes still queue |
| 4 | Close browser while offline with pending ops | Reopen: pending count restored from IndexedDB |
| 5 | Restart OS while offline | Same as #4 for same browser profile |
| 6 | Restore connectivity | Auto-sync starts; status → Syncing → Synced |
| 7 | Click Sync Now | Health → auth → push → pull → summary toast |
| 8 | Kill connection mid sync batch | Applied ops stay synced; remaining stay pending; retry does not duplicate |
| 9 | Edit same customer phone offline + on server | Conflict recorded; no silent overwrite of phone |
| 10 | Create multiple customers/orders offline then sync | All appear on server once; id_map filled |
| 11 | Retry same idempotency_key | Server returns original result; no second row |
| 12 | Open ERP on second device after sync | Server data visible; offline drafts from device A not on device B until synced |

**Integrity checks**

- No duplicate `order_mst` / `customer` for one `client_uuid`
- No lost queue rows after crash
- Cash / transfer / delivery queue offline and apply once on sync (idempotent)
- WhatsApp blocked offline with message; Print/PDF still works on loaded pages
- Logout revokes sync token; push requires re-login

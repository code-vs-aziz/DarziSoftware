# DarziSoftware

Tailoring shop ERP built on **CodeIgniter 3** — orders, measurements, transfer pipeline (HO → Cutting → Stitching), cash ledger, customers, employees, and reports.

## Requirements

- PHP 7.4+ (8.x recommended)
- MariaDB / MySQL
- Web server (Apache/Nginx) or PHP built-in server for local dev

## Setup

1. Copy `.env.example` to `.env` and set database credentials.
2. Point the web root to this directory (`index.php`).
3. Import your database schema / use existing `alibaba` dump.
4. Run `application/sql/performance_indexes.sql` on production DB if not already applied.
5. For offline-first staff ERP: run `application/sql/offline_sync_schema.sql` (or rely on auto-ensure on first `/api/erp_sync` call). Serve over HTTPS in production so the Service Worker can register.

## Offline-first (shop ERP)

When the internet or live server is unavailable, logged-in staff can keep creating/editing **customers** and creating **orders** on the device. Changes queue in **IndexedDB** and sync via `/api/erp_sync/*` when the backend is healthy again.

- Architecture: `docs/OFFLINE_FIRST_ARCHITECTURE.md`
- Test plan: `docs/OFFLINE_FIRST_TEST_PLAN.md`
- UI: status chip + **Sync Now** in the header
- Online-only by design: cash, delivery, transfers, reports, WhatsApp, RBAC

## Local dev

```bash
php -S 127.0.0.1:8080
```

Default login depends on your `users` table (see deployment notes).

### Darzi Baba public website

Customer-facing Next.js site in `website/` (measurements portal, tutorials, shop). Uses the same MySQL database via `/api/*`.

1. Run `application/sql/website_schema.sql`
2. Set `WEBSITE_CORS_ORIGINS` / optional `WEBSITE_OTP_DEBUG` in `.env`
3. `cd website && npm install && npm run dev` (see `website/README.md`)
4. ERP menu: **Manage Website**

Architecture notes: `docs/WEBSITE_ARCHITECTURE.md`

## Structure

- `application/controllers/` — HTTP entry points
- `application/models/` — Domain models (`Order_query_model`, `Cash_ledger_model`, etc.)
- `application/views/` — UI templates
- `website/` — Darzi Baba public Next.js site
- `css/dz-design-system.css` — Design system
- `docs/` — Architecture and coding standards

## License

Private — Aziz ur Rehman.

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

## Local dev

```bash
php -S 127.0.0.1:8080
```

Default login depends on your `users` table (see deployment notes).

## Structure

- `application/controllers/` — HTTP entry points
- `application/models/` — Domain models (`Order_query_model`, `Cash_ledger_model`, etc.)
- `application/views/` — UI templates
- `css/dz-design-system.css` — Design system
- `docs/` — Architecture and coding standards

## License

Private — Aziz ur Rehman.

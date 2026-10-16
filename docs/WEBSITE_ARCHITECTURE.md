# Darzi Baba website integration

## Architecture

```
Next.js (website/)  →  CI3 /api/*  →  MySQL alibaba (shared)
ERP Manage Website  →  same tables/services
```

- **Customers / measurements**: existing `customer` + `measurement` tables (no duplicate master).
- **Website submissions**: `web_measurement_submissions` → staff approve → write live `measurement`.
- **Shop orders**: `web_orders` / `web_order_items` (separate from tailoring `order_mst`).
- **Auth**: WhatsApp OTP → Bearer token in `web_customer_sessions`.

## SQL

Run `application/sql/website_schema.sql`.

## Key PHP files

- `application/core/Api_Controller.php`
- `application/controllers/Api/{Auth,Measurements,Content,Shop}.php`
- `application/controllers/Website.php` (ERP admin)
- `application/libraries/Website_{auth,otp,measurement}_service.php`
- `application/models/Website_model.php`

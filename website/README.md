# Darzi Baba public website

Next.js (App Router) customer-facing site. Talks to the existing CodeIgniter ERP via `/api/*` on the **same MySQL database**.

## Setup

1. Apply ERP SQL: `application/sql/website_schema.sql`
2. Copy `.env.example` → `.env.local` and set `NEXT_PUBLIC_API_BASE_URL` to your CI `index.php` base (e.g. `http://127.0.0.1:8080/index.php`)
3. In ERP `.env`, set `WEBSITE_CORS_ORIGINS=http://localhost:3000` and optionally `WEBSITE_OTP_DEBUG=true` for local OTP without WhatsApp
4. Install & run:

```bash
cd website
npm install
npm run dev
```

## Routes

| Path | Purpose | Indexable |
|------|---------|-----------|
| `/` | Homepage | Yes |
| `/measurements` | OTP portal + view/submit | **No** (`robots` disallow) |
| `/tutorials` | Measurement guides | Yes |
| `/shop`, `/shop/[slug]` | Catalog | Yes |
| `/cart` | Cart + COD checkout | No |
| `/contact` | Contact | Yes |

## ERP admin

**Manage Website** in the ERP sidebar: content, tutorials, measurement approvals, products, orders.

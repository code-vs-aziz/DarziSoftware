# Darzibaba — Architecture Overview

Tailoring ERP on **CodeIgniter 3** + **MySQL/MariaDB** + jQuery/Bootstrap.

## Layers

```
Browser → index.php → Hooks (language, RBAC) → Controller → Model → MySQL
                                      ↓
                                    View (PHP + components)
```

## Key paths

| Path | Role |
|------|------|
| `application/controllers/` | HTTP entry (16 controllers) |
| `application/models/gatedata.php` | Legacy facade — prefer new domain models |
| `application/models/Dashboard_data_model.php` | Dashboard KPIs + file cache |
| `application/core/MY_Controller.php` | Auth base + report helpers |
| `application/hooks/` | Language bootstrap, RBAC enforcement |
| `application/language/` | i18n strings (EN / Urdu / Arabic) |
| `application/config/app_languages.php` | Language registry |
| `application/config/env_bootstrap.php` | `.env` loader |
| `js/app-ajax-shell.js` | Partial-page navigation |
| `css/dz-design-system.css` | UI design system |

## Auth flow

1. `Login/process` → session `user` + bcrypt password
2. `Rbac_hook` → route → permission key
3. `MY_Controller` → `require_login()` (defense in depth)

## Caching

- File cache: `application/cache/` (dashboard, master tables)
- TTL: 120–300s in models
- No Redis in default deployment

## Database

- Config via `.env` → `application/config/database.php`
- Manual migrations: `application/sql/*.sql`
- **Run indexes:** `application/sql/performance_indexes.sql`

## Scale notes

- **Single shop / LAN:** current architecture is sufficient
- **Large data:** apply indexes, archive old orders, optimize reports
- **High concurrency / multi-server:** needs Redis sessions, read replica, queue for PDF — not in default stack

See `docs/CODING_STANDARDS.md` for development rules.

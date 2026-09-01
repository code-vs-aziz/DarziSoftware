# Darzibaba — Coding Standards

This project follows **CodeIgniter 3** conventions plus the rules below.

## PHP

- **Indent:** tabs (see `.editorconfig`)
- **Line endings:** LF, UTF-8
- **Style:** [CI3 Style Guide](https://codeigniter.com/user_guide/general/styleguide.html) — Allman braces, readable spacing
- **Strict types:** not required (PHP 7.4 codebase); avoid PHP 8-only syntax until upgrade
- **Opening line:** every PHP file starts with `<?php` and `defined('BASEPATH') OR exit(...)` except `index.php` and `env_bootstrap.php`

### Controllers

- Extend `MY_Controller` for authenticated pages
- Extend `CI_Controller` only for public routes (`Login`, `Language`)
- No business SQL in controllers — use models
- Use `t('key')` for user-visible strings; never hardcode labels in views when a lang key exists

### Models

- One domain per model file (`Customer_query_model`, `Cash_ledger_model`, …)
- Do not add new logic to `gatedata.php` — add a dedicated model and delegate via `__call` if needed
- Use Query Builder; never concatenate user input into SQL

### Views

- Escape output: `esc_html()` for text, `esc_attr()` for attributes
- Reuse components: `application/views/components/`, `ui_helper.php`
- No `mysql_*` or raw `$_POST` in views

### Security

- CSRF on all state-changing forms (`csrf_field` partial)
- Passwords via `password_helper.php` only
- Permissions via `Rbac_hook` + `$this->rbac->can('permission.key')`
- Secrets in `.env` only — never commit `.env`

## JavaScript

- `'use strict'` in app modules
- CSRF: use `window.AppCsrf` / meta token from `app-csrf.js`
- Prefer event delegation for AJAX-loaded content (`app:ajax-loaded`)

## CSS

- Design tokens in `css/dz-design-system.css` (`--dz-*` variables)
- Legacy overrides only in `css/app-unified-theme.css`
- No new gradients; use sky-blue Material-style tokens

## Git

- Do not commit: `.env`, `application/cache/*`, `application/sessions/*`, `uploads/orders/*.pdf`
- SQL migrations: add files under `application/sql/` with README note

## Environment

Copy `.env.example` → `.env` and set:

| Variable | Purpose |
|----------|---------|
| `CI_ENV` | `development` / `production` |
| `DB_*` | Database connection |
| `COOKIE_SECURE` | `true` on HTTPS |

## Adding a feature (checklist)

1. Lang keys in `english/`, `urdu/`, `arabic/`
2. RBAC permission in `Rbac_model` route map if new module
3. Model method + thin controller action
4. View using `dz-card` / `ui_btn` components
5. Index in `application/sql/performance_indexes.sql` if new query patterns

# Darzibaba SQL migrations

Manual schema patches for the `alibaba` database. Run in order on new environments or when upgrading.

## Core schema

| File | Purpose |
|------|---------|
| `rbac_schema.sql` | Roles, permissions, user-role tables |
| `business_configuration.sql` | Shop settings table |
| `order_snapshot_schema.sql` | Order measurement/customer snapshot columns |
| `order_predefined.sql` | Predefined order field options |
| `order_staff_empno.sql` | Staff employee on orders |

## Performance and views

| File | Purpose |
|------|---------|
| `performance_indexes.sql` | Recommended indexes for large tables |
| `v_cash_ledger.sql` | Cash ledger database view |

## Cash ledger

| File | Purpose |
|------|---------|
| `cash_ledger_opening_cache.sql` | Opening balance cache table |
| `dmst_paid_cash_trnno.sql` | Delivery/payment link column |

## Fixes and one-offs

| File | Purpose |
|------|---------|
| `fix_snap_customer_name.sql` | Repair snapshot customer names |
| `fix_snap_customer_name_utf8.sql` | UTF-8 fix for snapshot names |
| `order_predefined_fix_encoding.sql` | Encoding fix for predefined options |
| `employee_status_staff_type.sql` | Employee status/staff type columns |
| `fiscal_year_revoke.sql` | Fiscal year permission rollback |
| `user_password_varchar255.sql` | Expand `users.UPASS` for bcrypt (Phase D) |

## Environment

Database connection is configured via `.env` (see `.env.example` at project root). SQL files assume database name `alibaba` unless you change `DB_NAME`.

## Application scripts (not raw SQL)

| Script | Purpose |
|--------|---------|
| `../scripts/migrate_passwords.php` | Hash plaintext user passwords |

## Notes

- Always back up the database before running patches.
- MariaDB 10.4+ / MySQL 5.7+ compatible.
- After schema changes, clear `application/cache/` if query caching causes stale reads.

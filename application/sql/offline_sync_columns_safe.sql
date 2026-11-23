-- Safe additive migration for offline sync columns.
-- Does NOT drop tables or delete business data.
-- Prefer Erp_sync_model::ensure_schema() which checks information_schema first.
-- Run manually only if PHP ensure cannot run; ignore "Duplicate column" errors.

ALTER TABLE customer ADD COLUMN client_uuid CHAR(36) NULL DEFAULT NULL;
ALTER TABLE customer ADD COLUMN row_version INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE customer ADD COLUMN updated_at DATETIME NULL DEFAULT NULL;

ALTER TABLE measurement ADD COLUMN client_uuid CHAR(36) NULL DEFAULT NULL;
ALTER TABLE measurement ADD COLUMN row_version INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE measurement ADD COLUMN updated_at DATETIME NULL DEFAULT NULL;

ALTER TABLE order_mst ADD COLUMN client_uuid CHAR(36) NULL DEFAULT NULL;
ALTER TABLE order_mst ADD COLUMN row_version INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE order_mst ADD COLUMN updated_at DATETIME NULL DEFAULT NULL;

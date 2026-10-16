-- Offline-first sync schema for Darzi Baba ERP staff clients.
-- Safe to re-run (IF NOT EXISTS / additive columns guarded in comments).
-- Run on the same MariaDB/MySQL database as the ERP (e.g. alibaba).

-- ---------------------------------------------------------------------------
-- Devices & offline sync tokens (no plaintext passwords)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS erp_sync_devices (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  device_id CHAR(36) NOT NULL,
  user_name VARCHAR(120) NOT NULL,
  label VARCHAR(160) NOT NULL DEFAULT '',
  user_agent VARCHAR(255) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  last_seen_at DATETIME NULL DEFAULT NULL,
  revoked_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_erp_sync_devices_device (device_id),
  KEY idx_erp_sync_devices_user (user_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erp_sync_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  device_id CHAR(36) NOT NULL,
  user_name VARCHAR(120) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  issued_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  last_used_at DATETIME NULL DEFAULT NULL,
  revoked_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_erp_sync_tokens_hash (token_hash),
  KEY idx_erp_sync_tokens_device (device_id),
  KEY idx_erp_sync_tokens_user (user_name),
  KEY idx_erp_sync_tokens_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Idempotent operation log (survives client retries)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS erp_sync_ops (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  device_id CHAR(36) NOT NULL,
  idempotency_key CHAR(64) NOT NULL,
  op_id CHAR(36) NOT NULL,
  entity_type VARCHAR(40) NOT NULL,
  entity_client_id CHAR(36) NOT NULL DEFAULT '',
  op_type VARCHAR(40) NOT NULL,
  request_json MEDIUMTEXT NULL,
  result_json MEDIUMTEXT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'applied',
  server_entity_id VARCHAR(64) NOT NULL DEFAULT '',
  error_message VARCHAR(500) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  applied_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_erp_sync_ops_idem (device_id, idempotency_key),
  KEY idx_erp_sync_ops_entity (entity_type, entity_client_id),
  KEY idx_erp_sync_ops_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Conflict queue (manual resolution for sensitive field clashes)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS erp_sync_conflicts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  device_id CHAR(36) NOT NULL,
  entity_type VARCHAR(40) NOT NULL,
  entity_client_id CHAR(36) NOT NULL DEFAULT '',
  server_entity_id VARCHAR(64) NOT NULL DEFAULT '',
  local_json MEDIUMTEXT NULL,
  server_json MEDIUMTEXT NULL,
  conflict_fields VARCHAR(500) NOT NULL DEFAULT '',
  status VARCHAR(20) NOT NULL DEFAULT 'open',
  resolution VARCHAR(40) NOT NULL DEFAULT '',
  resolved_by VARCHAR(120) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  resolved_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_erp_sync_conflicts_status (status, created_at),
  KEY idx_erp_sync_conflicts_entity (entity_type, server_entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Client UUID map (offline UUID ↔ server PK)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS erp_sync_id_map (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  entity_type VARCHAR(40) NOT NULL,
  client_uuid CHAR(36) NOT NULL,
  server_id VARCHAR(64) NOT NULL,
  device_id CHAR(36) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_erp_sync_id_map_client (entity_type, client_uuid),
  KEY idx_erp_sync_id_map_server (entity_type, server_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Additive columns on core tables (run each once; ignore duplicate-column errors)
-- ---------------------------------------------------------------------------
-- ALTER TABLE customer ADD COLUMN client_uuid CHAR(36) NULL DEFAULT NULL;
-- ALTER TABLE customer ADD COLUMN row_version INT UNSIGNED NOT NULL DEFAULT 1;
-- ALTER TABLE customer ADD COLUMN updated_at DATETIME NULL DEFAULT NULL;
-- ALTER TABLE customer ADD UNIQUE KEY uq_customer_client_uuid (client_uuid);

-- ALTER TABLE measurement ADD COLUMN client_uuid CHAR(36) NULL DEFAULT NULL;
-- ALTER TABLE measurement ADD COLUMN row_version INT UNSIGNED NOT NULL DEFAULT 1;
-- ALTER TABLE measurement ADD COLUMN updated_at DATETIME NULL DEFAULT NULL;

-- ALTER TABLE order_mst ADD COLUMN client_uuid CHAR(36) NULL DEFAULT NULL;
-- ALTER TABLE order_mst ADD COLUMN row_version INT UNSIGNED NOT NULL DEFAULT 1;
-- ALTER TABLE order_mst ADD COLUMN updated_at DATETIME NULL DEFAULT NULL;
-- ALTER TABLE order_mst ADD UNIQUE KEY uq_order_mst_client_uuid (client_uuid);

-- Prefer the PHP installer helper (Sync schema ensure) which checks information_schema
-- before ALTER, so re-runs stay safe.

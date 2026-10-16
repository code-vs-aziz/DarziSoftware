-- WhatsApp broadcast campaigns (slow, safe queue for local bridge).
-- Safe to re-run.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS wa_broadcast_campaigns (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL DEFAULT '',
  message MEDIUMTEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft',
  -- draft | queued | running | paused | completed | cancelled
  delay_min_sec INT UNSIGNED NOT NULL DEFAULT 55,
  delay_max_sec INT UNSIGNED NOT NULL DEFAULT 95,
  daily_cap INT UNSIGNED NOT NULL DEFAULT 40,
  hour_cap INT UNSIGNED NOT NULL DEFAULT 20,
  send_window_start TINYINT UNSIGNED NOT NULL DEFAULT 10,
  send_window_end TINYINT UNSIGNED NOT NULL DEFAULT 20,
  risk_acknowledged TINYINT(1) NOT NULL DEFAULT 0,
  total_recipients INT UNSIGNED NOT NULL DEFAULT 0,
  sent_count INT UNSIGNED NOT NULL DEFAULT 0,
  fail_count INT UNSIGNED NOT NULL DEFAULT 0,
  skip_count INT UNSIGNED NOT NULL DEFAULT 0,
  consecutive_failures INT UNSIGNED NOT NULL DEFAULT 0,
  last_sent_at DATETIME NULL DEFAULT NULL,
  started_at DATETIME NULL DEFAULT NULL,
  finished_at DATETIME NULL DEFAULT NULL,
  created_by VARCHAR(80) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_wa_broadcast_status (status, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wa_broadcast_recipients (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  campaign_id INT UNSIGNED NOT NULL,
  customer_code INT UNSIGNED NOT NULL DEFAULT 0,
  customer_name VARCHAR(200) NOT NULL DEFAULT '',
  phone VARCHAR(24) NOT NULL DEFAULT '',
  phone_normalized VARCHAR(24) NOT NULL DEFAULT '',
  personalized_message MEDIUMTEXT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'pending',
  -- pending | sent | failed | skipped
  error_message VARCHAR(255) NOT NULL DEFAULT '',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  sent_at DATETIME NULL DEFAULT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_wa_broadcast_campaign_phone (campaign_id, phone_normalized),
  KEY idx_wa_broadcast_recipients_pending (campaign_id, status, id),
  KEY idx_wa_broadcast_recipients_customer (customer_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO rbac_permissions (permission_key, permission_label, legacy_page, category) VALUES
('broadcast.manage', 'WhatsApp Broadcast', NULL, 'Messaging');

INSERT IGNORE INTO rbac_role_permissions (role_id, permission_key)
SELECT r.id, 'broadcast.manage'
FROM rbac_roles r
WHERE r.role_name IN ('Admin', 'Owner', 'Manager')
   OR r.id = 1;

-- Device lockdown: only approved browser devices may use the ERP when enabled.
-- Browsers cannot expose MAC addresses; we use a long-lived HttpOnly device token.

CREATE TABLE IF NOT EXISTS `device_auth_settings` (
  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `lock_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `device_auth_settings` (`id`, `lock_enabled`, `updated_at`)
VALUES (1, 0, NOW())
ON DUPLICATE KEY UPDATE `id` = `id`;

CREATE TABLE IF NOT EXISTS `authorized_devices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `device_name` VARCHAR(120) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `user_agent` VARCHAR(512) NULL DEFAULT NULL,
  `register_ip` VARCHAR(45) NULL DEFAULT NULL,
  `last_ip` VARCHAR(45) NULL DEFAULT NULL,
  `last_seen_at` DATETIME NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_authorized_devices_token` (`token_hash`),
  KEY `idx_authorized_devices_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `device_enrollment_codes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code_hash` CHAR(64) NOT NULL,
  `label` VARCHAR(120) NULL DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL DEFAULT NULL,
  `created_by` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_device_enrollment_code` (`code_hash`),
  KEY `idx_device_enrollment_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

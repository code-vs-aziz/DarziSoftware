-- Customer message automation (WhatsApp / SMS queue)
-- Run once on your MariaDB database (e.g. alibaba).

CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` tinyint unsigned NOT NULL DEFAULT 1,
  `enabled_order_booked` tinyint(1) NOT NULL DEFAULT 1,
  `enabled_order_ready` tinyint(1) NOT NULL DEFAULT 1,
  `enabled_payment_reminder` tinyint(1) NOT NULL DEFAULT 1,
  `enabled_delivery_confirm` tinyint(1) NOT NULL DEFAULT 1,
  `auto_open_whatsapp` tinyint(1) NOT NULL DEFAULT 1,
  `dispatch_mode` varchar(16) NOT NULL DEFAULT 'whatsapp_link',
  `payment_reminder_days_before` tinyint unsigned NOT NULL DEFAULT 1,
  `template_order_booked` text,
  `template_order_ready` text,
  `template_payment_reminder` text,
  `template_delivery_confirm` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notification_settings` (`id`)
SELECT 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `notification_settings` WHERE `id` = 1);

CREATE TABLE IF NOT EXISTS `notification_queue` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varchar(32) NOT NULL,
  `order_id` int unsigned NOT NULL DEFAULT 0,
  `customer_id` int unsigned NOT NULL DEFAULT 0,
  `phone` varchar(24) DEFAULT NULL,
  `message` text,
  `channel` varchar(16) NOT NULL DEFAULT 'whatsapp',
  `status` varchar(16) NOT NULL DEFAULT 'pending',
  `whatsapp_url` text,
  `attachment_path` varchar(512) DEFAULT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  `trigger_source` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notification_queue_status` (`status`),
  KEY `idx_notification_queue_order_event` (`order_id`, `event_type`),
  KEY `idx_notification_queue_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Existing installs: add attachment column for queued PDF / document sends
SET @db := DATABASE();
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'notification_queue' AND COLUMN_NAME = 'attachment_path') = 0,
  'ALTER TABLE `notification_queue` ADD COLUMN `attachment_path` varchar(512) DEFAULT NULL AFTER `whatsapp_url`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

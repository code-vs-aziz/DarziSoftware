-- Who / when / IP for order deactivate, reactivate, and similar actions.
CREATE TABLE IF NOT EXISTS order_action_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT NOT NULL,
  action VARCHAR(32) NOT NULL,
  username VARCHAR(100) NOT NULL DEFAULT '',
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  user_agent VARCHAR(255) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_order_action_order (order_id, created_at),
  KEY idx_order_action_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

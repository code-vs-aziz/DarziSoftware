CREATE TABLE IF NOT EXISTS business_configuration (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  location_name VARCHAR(150) NULL,
  phone VARCHAR(80) NULL,
  address_urdu TEXT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE business_configuration
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE business_configuration
  MODIFY location_name VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  MODIFY phone VARCHAR(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  MODIFY address_urdu TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

INSERT IGNORE INTO business_configuration (id, location_name, phone, address_urdu)
VALUES (1, 'Main Location', '', '');

INSERT IGNORE INTO rbac_permissions (permission_key, permission_label, legacy_page, category)
VALUES ('configuration.manage', 'Business Configuration', NULL, 'Administration');

INSERT IGNORE INTO rbac_role_permissions (role_id, permission_key)
SELECT id, 'configuration.manage'
FROM rbac_roles
WHERE LOWER(role_name) = 'super admin';

-- Style / Embroidery / Suit Type: Active/Inactive flag for order dropdowns.
-- Safe to re-run.

SET NAMES utf8mb4;

SET @db := DATABASE();

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'style' AND COLUMN_NAME = 'is_active') = 0,
  'ALTER TABLE style ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'embroidery' AND COLUMN_NAME = 'is_active') = 0,
  'ALTER TABLE embroidery ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'suit' AND COLUMN_NAME = 'is_active') = 0,
  'ALTER TABLE suit ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE style SET is_active = 1 WHERE is_active IS NULL;
UPDATE embroidery SET is_active = 1 WHERE is_active IS NULL;
UPDATE suit SET is_active = 1 WHERE is_active IS NULL;

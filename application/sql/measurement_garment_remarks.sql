-- Per-garment measurement notes (unique note per suit type).
-- Safe to re-run: adds columns only when missing.

SET @db := DATABASE();

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'measurement' AND COLUMN_NAME = 'remarks_kurta'),
    'SELECT 1',
    'ALTER TABLE measurement ADD COLUMN remarks_kurta VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER remarks'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'measurement' AND COLUMN_NAME = 'remarks_pajama'),
    'SELECT 1',
    'ALTER TABLE measurement ADD COLUMN remarks_pajama VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER remarks_kurta'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'measurement' AND COLUMN_NAME = 'remarks_pent'),
    'SELECT 1',
    'ALTER TABLE measurement ADD COLUMN remarks_pent VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER remarks_pajama'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'measurement' AND COLUMN_NAME = 'remarks_shirt'),
    'SELECT 1',
    'ALTER TABLE measurement ADD COLUMN remarks_shirt VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER remarks_pent'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'measurement' AND COLUMN_NAME = 'remarks_coat'),
    'SELECT 1',
    'ALTER TABLE measurement ADD COLUMN remarks_coat VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER remarks_shirt'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'measurement' AND COLUMN_NAME = 'remarks_waistcoat'),
    'SELECT 1',
    'ALTER TABLE measurement ADD COLUMN remarks_waistcoat VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER remarks_coat'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Migrate legacy single note into kameez/shalwar note when empty.
UPDATE measurement
SET remarks_kurta = remarks
WHERE (remarks_kurta IS NULL OR TRIM(remarks_kurta) = '')
  AND remarks IS NOT NULL
  AND TRIM(remarks) != '';

-- Snap table (optional — ignore if table missing).
SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_measurement_snap')
    AND NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_measurement_snap' AND COLUMN_NAME = 'remarks_kurta'),
    'ALTER TABLE order_measurement_snap
      ADD COLUMN remarks_kurta TEXT NULL,
      ADD COLUMN remarks_pajama TEXT NULL,
      ADD COLUMN remarks_pent TEXT NULL,
      ADD COLUMN remarks_shirt TEXT NULL,
      ADD COLUMN remarks_coat TEXT NULL,
      ADD COLUMN remarks_waistcoat TEXT NULL',
    'SELECT 1'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

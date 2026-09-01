-- Employee: Staff type support + Active/Inactive status.
-- Safe to re-run.

SET NAMES utf8mb4;

ALTER TABLE employee
  MODIFY type VARCHAR(20) NOT NULL DEFAULT '';

SET @db := DATABASE();

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'employee' AND COLUMN_NAME = 'status') = 0,
  'ALTER TABLE employee ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT \'Active\' AFTER type',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE employee SET status = 'Active' WHERE status IS NULL OR TRIM(status) = '';

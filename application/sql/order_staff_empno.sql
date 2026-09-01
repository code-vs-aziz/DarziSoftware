-- Order header: staff employee who took the order.
-- Safe to re-run.

SET @db := DATABASE();

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_mst' AND COLUMN_NAME = 'staff_empno') = 0,
  'ALTER TABLE order_mst ADD COLUMN staff_empno INT NULL DEFAULT NULL AFTER qty',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

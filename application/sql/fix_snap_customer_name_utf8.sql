-- Fix snap_customer_name column charset (latin1 cannot store Urdu; shows as ????).
-- Run once, then run fix_snap_customer_name.sql or the UPDATE below.

SET NAMES utf8mb4;

ALTER TABLE order_mst
  MODIFY snap_customer_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

UPDATE order_mst o
INNER JOIN measurement m ON m.code = o.naap_no
INNER JOIN customer c ON c.code = m.customer_code
SET o.snap_customer_name = c.name
WHERE o.snap_customer_name IS NULL
   OR TRIM(o.snap_customer_name) = ''
   OR o.snap_customer_name REGEXP '^[?[:space:]/]+$';

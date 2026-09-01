-- Repair order header snapshots where customer name was saved as "????" placeholders.
-- Safe to run multiple times.

UPDATE order_mst o
INNER JOIN measurement m ON m.code = o.naap_no
INNER JOIN customer c ON c.code = m.customer_code
SET o.snap_customer_name = c.name
WHERE o.snap_customer_name IS NULL
   OR TRIM(o.snap_customer_name) = ''
   OR o.snap_customer_name REGEXP '^[?[:space:]/]+$';

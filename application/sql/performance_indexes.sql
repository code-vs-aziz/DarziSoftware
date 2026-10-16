-- Darzi Baba / Alibaba app: suggested indexes for joins and filters used in gatedata.
-- Safe to re-run (MariaDB 10.1+ / MySQL 8.0.29+): skips indexes that already exist.
--
-- Command line:
--   mysql -h 127.0.0.1 -P 3310 -u root alibaba < application/sql/performance_indexes.sql
--
-- phpMyAdmin: open this file, paste into SQL tab, click Go.
-- "Duplicate key name" on old ALTER TABLE scripts meant the index was already there — that is OK.

USE alibaba;

-- Customer list / lookups

CREATE INDEX IF NOT EXISTS idx_customer_man_napno ON customer (man_napno);
CREATE INDEX IF NOT EXISTS idx_customer_cnic ON customer (cnic);
CREATE INDEX IF NOT EXISTS idx_customer_name ON customer (name(100));

-- Measurement join from customer
CREATE INDEX IF NOT EXISTS idx_measurement_code ON measurement (code);
CREATE INDEX IF NOT EXISTS idx_measurement_customer_code ON measurement (customer_code);

-- Orders (run application/sql/order_snapshot_schema.sql first if snap_man_napno is missing)
CREATE INDEX IF NOT EXISTS idx_order_mst_naap_no ON order_mst (naap_no);
CREATE INDEX IF NOT EXISTS idx_order_mst_edate ON order_mst (EDATE);
CREATE INDEX IF NOT EXISTS idx_order_mst_rdate ON order_mst (RDATE);
CREATE INDEX IF NOT EXISTS idx_order_mst_edate_ostatus ON order_mst (EDATE, ostatus);
CREATE INDEX IF NOT EXISTS idx_order_mst_rdate_ostatus ON order_mst (RDATE, ostatus);
CREATE INDEX IF NOT EXISTS idx_order_mst_ostatus_trnno ON order_mst (ostatus, trnno);
CREATE INDEX IF NOT EXISTS idx_order_mst_trnno ON order_mst (trnno);

SET @db := DATABASE();
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_mst' AND COLUMN_NAME = 'snap_man_napno') > 0,
  'CREATE INDEX IF NOT EXISTS idx_order_mst_snap_man_napno ON order_mst (snap_man_napno)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Transfers
CREATE INDEX IF NOT EXISTS idx_transfer_mst_order_id ON transfer_mst (order_id);
CREATE INDEX IF NOT EXISTS idx_transfer_mst_dept_status ON transfer_mst (dept, status);
CREATE INDEX IF NOT EXISTS idx_transfer_mst_sr_dept ON transfer_mst (sr, dept);
CREATE INDEX IF NOT EXISTS idx_transfer_mst_dashboard ON transfer_mst (dept, status, order_id, sr);
CREATE INDEX IF NOT EXISTS idx_transfer_mst_order_status ON transfer_mst (order_id, sr, status, dept);
CREATE INDEX IF NOT EXISTS idx_transfer_mst_employee_ledger ON transfer_mst (empno, EDATE, dept, status, order_id, sr);
-- Ready-for-delivery / latest-transfer lookups (include trnno for MAX() subquery)
CREATE INDEX IF NOT EXISTS idx_transfer_mst_latest_stit ON transfer_mst (order_id, sr, dept, status, trnno);

-- Order detail line joins
CREATE INDEX IF NOT EXISTS idx_order_dtl_order_sr ON order_dtl (order_trnno, trnno);

-- Deliveries / cash received against delivered orders
CREATE INDEX IF NOT EXISTS idx_dmst_edate_order_id ON dmst (EDATE, order_id);
CREATE INDEX IF NOT EXISTS idx_dmst_pending_customer_ledger ON dmst (pending, paidstatus, NPAID_dATE, balance);
CREATE INDEX IF NOT EXISTS idx_dmst_delivery_report ON dmst (EDATE, PAID, order_id);
CREATE INDEX IF NOT EXISTS idx_ddtl_order_date ON ddtl (ORDER_TRNNO, ord_dtl_date);

-- Employee ledger cash filters
CREATE INDEX IF NOT EXISTS idx_cash_employee_ledger ON cash (emp_id, EDATE, type);

-- Customer ledger cash filters
CREATE INDEX IF NOT EXISTS idx_cash_customer_ledger ON cash (customer_id, EDATE, type);

-- Cash ledger date/type filters
CREATE INDEX IF NOT EXISTS idx_cash_ledger_date_type ON cash (EDATE, type);
CREATE INDEX IF NOT EXISTS idx_cash_type_edate ON cash (type, EDATE);
CREATE INDEX IF NOT EXISTS idx_order_mst_edate_advance ON order_mst (EDATE, advance);

-- Notification queue claim / duplicate checks (table may not exist on older DBs)
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'notification_queue') > 0,
  'CREATE INDEX IF NOT EXISTS idx_notify_queue_status_id ON notification_queue (status, id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'notification_queue') > 0,
  'CREATE INDEX IF NOT EXISTS idx_notify_queue_dup ON notification_queue (order_id, event_type, created_at, status)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Employee-wise sale / STIT delivery reports
CREATE INDEX IF NOT EXISTS idx_transfer_mst_emp_sale ON transfer_mst (dept, status, EDATE, empno);

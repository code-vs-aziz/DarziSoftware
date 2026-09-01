-- Optional table (not used by live reports — opening is always computed fresh).
-- Safe to drop: DROP TABLE IF EXISTS cash_ledger_opening_cache;

CREATE TABLE IF NOT EXISTS cash_ledger_opening_cache (
  opening_date DATE NOT NULL,
  opening_balance DECIMAL(18,2) NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (opening_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Opening-balance indexes (skip line if "Duplicate key name").
ALTER TABLE cash ADD INDEX idx_cash_opening_dedup (type, customer_id, EDATE, amount);
ALTER TABLE order_mst ADD INDEX idx_order_mst_opening (EDATE, advance, naap_no);

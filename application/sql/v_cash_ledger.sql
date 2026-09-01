-- Cash ledger views for SQL/phpMyAdmin inspection only.
-- PHP report code does NOT query these views (uses date-filtered inline SQL + optional cache).
-- Run once per environment: mysql -u root alibaba < application/sql/v_cash_ledger.sql
--
-- Required indexes for report speed (see performance_indexes.sql + cash_ledger_opening_cache.sql):
--   idx_order_mst_edate, idx_cash_ledger_date_type, idx_cash_customer_ledger

DROP VIEW IF EXISTS v_cash_ledger_opening_lines;
DROP VIEW IF EXISTS v_cash_ledger;

-- Period ledger rows (same four UNION branches as legacy get_data_cash).
CREATE VIEW v_cash_ledger AS
SELECT
	order_mst.advance AS RECEIPT,
	order_mst.EDATE AS ENTRYDATE,
	2 AS type,
	CONCAT(
		'Advance Received Cash Against Order ',
		order_mst.trnno,
		' Naap No : ',
		customer.man_napno,
		'  ',
		customer.name
	) AS NARRATION,
	order_mst.trnno AS SORTNO,
	'advance' AS ledger_source
FROM order_mst
INNER JOIN measurement ON measurement.code = order_mst.naap_no
INNER JOIN customer ON customer.code = measurement.customer_code
WHERE order_mst.advance != 0

UNION ALL

SELECT
	cash.amount AS RECEIPT,
	cash.EDATE AS ENTRYDATE,
	cash.type,
	CONCAT(cash.narration, ' Naap No : ', customer.man_napno, '  ', customer.name) AS NARRATION,
	cash.TRNNO AS SORTNO,
	'customer_cash' AS ledger_source
FROM cash
INNER JOIN customer ON customer.code = cash.customer_id
WHERE cash.type != 7

UNION ALL

SELECT
	cash.amount AS RECEIPT,
	cash.EDATE AS ENTRYDATE,
	cash.type,
	CONCAT(cash.narration, '  ', employee.name) AS NARRATION,
	cash.TRNNO AS SORTNO,
	'employee_cash' AS ledger_source
FROM cash
INNER JOIN employee ON employee.empno = cash.emp_id
WHERE cash.type != 7
  AND (cash.customer_id IS NULL OR cash.customer_id = 0)

UNION ALL

SELECT
	cash.amount AS RECEIPT,
	cash.EDATE AS ENTRYDATE,
	cash.type,
	cash.narration AS NARRATION,
	cash.TRNNO AS SORTNO,
	'other_cash' AS ledger_source
FROM cash
WHERE cash.type != 1
  AND cash.type != 2
  AND cash.type != 7
  AND (cash.customer_id IS NULL OR cash.customer_id = 0)
  AND (cash.emp_id IS NULL OR cash.emp_id = 0);

-- Diagnostic view only (no date filter — do not use for live reports).
-- PHP uses cash_ledger_opening_cache + optimized date-filtered query in get_opeing_cash().
CREATE VIEW v_cash_ledger_opening_lines AS
SELECT
	o.advance AS amount,
	o.EDATE AS entry_date,
	1 AS is_receipt
FROM order_mst o
INNER JOIN measurement m ON m.code = o.naap_no
LEFT JOIN cash c ON c.type = 2
  AND c.customer_id = m.customer_code
  AND c.EDATE = o.EDATE
  AND c.amount = o.advance
WHERE o.advance != 0
  AND c.TRNNO IS NULL

UNION ALL

SELECT
	c.amount AS amount,
	c.EDATE AS entry_date,
	1 AS is_receipt
FROM cash c
LEFT JOIN order_mst o ON o.advance != 0
  AND o.advance = c.amount
  AND o.EDATE = c.EDATE
LEFT JOIN measurement m ON m.code = o.naap_no
  AND m.customer_code = c.customer_id
WHERE c.type = 2
  AND o.trnno IS NULL

UNION ALL

SELECT
	cash.amount AS amount,
	cash.EDATE AS entry_date,
	0 AS is_receipt
FROM cash
WHERE cash.type <> 2;

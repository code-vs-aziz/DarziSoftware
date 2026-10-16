<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cash_ledger_model extends CI_Model

{
  function has_cash_ledger_opening_cache_table()
  {
    static $has = NULL;
    if ($has === NULL)
    {
      $has = $this->db->table_exists('cash_ledger_opening_cache');
      if ($has)
      {
        $probe = $this->db->query('SELECT opening_date FROM cash_ledger_opening_cache LIMIT 1');
        if ($probe === FALSE)
        {
          $has = FALSE;
          $err = $this->db->error();
          log_message('error', 'cash_ledger_opening_cache unavailable: '.(isset($err['message']) ? $err['message'] : 'query failed'));
        }
      }
    }
    return (bool) $has;
  }

  function invalidate_cash_ledger_opening_cache($transaction_date = NULL)
  {
    if ( ! $this->has_cash_ledger_opening_cache_table())
    {
      return;
    }

    // Opening formula depends on all history; clear whole cache on any cash/order change.
    if ($this->db->truncate('cash_ledger_opening_cache') === FALSE)
    {
      $err = $this->db->error();
      log_message('error', 'cash_ledger_opening_cache truncate failed: '.(isset($err['message']) ? $err['message'] : 'unknown'));
    }
  }

  function store_cash_ledger_opening_cache($opening_date, $opening_balance)
  {
    if ( ! $this->has_cash_ledger_opening_cache_table())
    {
      return;
    }

    $opening_date = trim((string) $opening_date);
    if ($opening_date === '')
    {
      return;
    }

    if ($this->db->replace('cash_ledger_opening_cache', array(
      'opening_date' => $opening_date,
      'opening_balance' => round((float) $opening_balance, 2),
      'updated_at' => date('Y-m-d H:i:s'),
    )) === FALSE)
    {
      $err = $this->db->error();
      log_message('error', 'cash_ledger_opening_cache store failed: '.(isset($err['message']) ? $err['message'] : 'unknown'));
    }
  }

  function compute_cash_opening_balance($date)
  {
    $date = trim((string) $date);
    if ($date === '')
    {
      return 0;
    }

    // Legacy opening formula; joins on advances match printed ledger, aggregates only (no row expansion).
    $sql = '
      SELECT
        COALESCE((
          SELECT SUM(o.advance)
          FROM order_mst o
          INNER JOIN measurement m ON m.code = o.naap_no
          INNER JOIN customer cu ON cu.code = m.customer_code
          WHERE o.advance != 0
            AND o.EDATE < ?
        ), 0)
        + COALESCE((
          SELECT SUM(c.amount)
          FROM cash c
          INNER JOIN customer cu ON cu.code = c.customer_id
          WHERE c.EDATE < ?
            AND c.type = 2
        ), 0)
        - COALESCE((
          SELECT SUM(c.amount)
          FROM cash c
          WHERE c.EDATE < ?
            AND c.type <> 2
        ), 0) AS opening_balance
    ';
    $q = $this->db->query($sql, array($date, $date, $date));
    if ($q === FALSE)
    {
      $err = $this->db->error();
      log_message('error', 'compute_cash_opening_balance failed: '.(isset($err['message']) ? $err['message'] : 'query failed'));
      return 0;
    }
    $row = $q->row();
    return $row ? (float) $row->opening_balance : 0;
  }

   function get_opeing_cash($date)
  {
	$date = trim((string) $date);
	if ($date === '')
	{
		return 0;
	}

	if ($this->has_cash_ledger_opening_cache_table())
	{
		$cache_q = $this->db->get_where('cash_ledger_opening_cache', array('opening_date' => $date), 1);
		if ($cache_q !== FALSE)
		{
			$cached = $cache_q->row();
			if ($cached)
			{
				return (float) $cached->opening_balance;
			}
		}
	}

	$balance = $this->compute_cash_opening_balance($date);
	$this->store_cash_ledger_opening_cache($date, $balance);
	return $balance;
  }
  
   function get_opeing_emp($date,$emplyee)
  {
	$date = trim((string) $date);
	$emplyee = (int) $emplyee;
	if ($date === '' || $emplyee <= 0)
	{
		return 0;
	}

	$sql = '
		SELECT
			COALESCE((
				SELECT SUM(transfer_mst.price)
				FROM transfer_mst
				INNER JOIN order_dtl ON order_dtl.trnno = transfer_mst.sr AND order_dtl.order_trnno = transfer_mst.order_id
				WHERE transfer_mst.empno = ?
				  AND transfer_mst.EDATE < ?
				  AND (
					(transfer_mst.dept = "CUT" AND (transfer_mst.status = "Deactive" OR transfer_mst.status = "DELIVER"))
					OR (transfer_mst.dept = "STIT" AND (transfer_mst.status = "Deactive" OR transfer_mst.status = "DELIVER"))
				  )
			), 0)
			- COALESCE((
				SELECT SUM(cash.amount)
				FROM cash
				WHERE cash.emp_id = ?
				  AND cash.EDATE < ?
				  AND cash.type != 7
				  AND cash.type != 2
			), 0)
			+ COALESCE((
				SELECT SUM(ABS(cash.amount))
				FROM cash
				WHERE cash.emp_id = ?
				  AND cash.EDATE < ?
				  AND (cash.type = 7 OR cash.type = 2)
			), 0) AS opening_balance
	';
	$row = $this->db->query($sql, array($emplyee, $date, $emplyee, $date, $emplyee, $date))->row();
	return $row ? (float) $row->opening_balance : 0;
  }
  
  
  
  
  
  function get_customer_info($code)
  {
    $this->db->select('code, name, man_napno, Phone');
    $this->db->from('customer');
    $this->db->where('code', (int) $code);
    $row = $this->db->get()->row();
    return $row ? $row : NULL;
  }

    function get_opeing($date,$emplyee)
  {
	$date = trim((string) $date);
	$emplyee = (int) $emplyee;
	if ($date === '' || $emplyee <= 0)
	{
		return 0;
	}

	$sql = '
		SELECT
			COALESCE((
				SELECT SUM(order_mst.total - order_mst.advance - order_mst.discount)
				FROM order_mst
				INNER JOIN measurement ON order_mst.naap_no = measurement.code
				WHERE measurement.customer_code = ?
				  AND order_mst.EDATE < ?
			), 0)
			- COALESCE((
				SELECT SUM(cash.amount)
				FROM cash
				WHERE cash.customer_id = ?
				  AND cash.EDATE < ?
				  AND cash.type = 2
			), 0) AS opening_balance
	';
	$row = $this->db->query($sql, array($emplyee, $date, $emplyee, $date))->row();
	return $row ? (float) $row->opening_balance : 0;
  }
  
    function get_data_customer($date,$emplyee)
  {
	$date = trim((string) $date);
	$emplyee = (int) $emplyee;
	if ($date === '' || $emplyee <= 0)
	{
		return array();
	}

	$sql = '
		SELECT *
		FROM (
			SELECT
				order_mst.EDATE AS LEDGERDATE,
				order_mst.trnno AS ORDERNO,
				CONCAT("Order #", order_mst.trnno, " booking") AS NARRATION,
				0 AS type,
				0 AS amount,
				order_mst.total AS debit,
				order_mst.advance AS credit,
				order_mst.trnno AS SORTNO
			FROM order_mst
			INNER JOIN measurement ON order_mst.naap_no = measurement.code
			WHERE measurement.customer_code = ?
			  AND order_mst.EDATE >= ?
			UNION ALL
			SELECT
				cash.EDATE AS LEDGERDATE,
				cash.order_no AS ORDERNO,
				cash.narration AS NARRATION,
				cash.type AS type,
				cash.amount AS amount,
				CASE WHEN cash.type = 1 THEN cash.amount ELSE 0 END AS debit,
				CASE WHEN cash.type = 2 THEN cash.amount ELSE 0 END AS credit,
				cash.TRNNO AS SORTNO
			FROM cash
			WHERE cash.customer_id = ?
			  AND cash.EDATE >= ?
		) customer_ledger_rows
		ORDER BY LEDGERDATE ASC, SORTNO ASC
	';
	$q = $this->db->query($sql, array($emplyee, $date, $emplyee, $date));
	return $q->num_rows() > 0 ? $q->result() : array();
  }
  
  
  
  
  
  
  	 function sortByWeight($a, $b)
{
    $a = $a->ENDATE;
    $b = $b->ENDATE;


    if ($a == $b) return 0;
     return ($a < $b) ? -1 : 1;
	
   
}
  
   function get_data_employee($date,$date1,$emplyee)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	$emplyee = (int) $emplyee;
	if ($date === '' || $date1 === '' || $emplyee <= 0)
	{
		return array();
	}

	$sql = '
		SELECT *
		FROM (
			SELECT
				transfer_mst.EDATE AS ENDATE,
				transfer_mst.RDATE AS RDATE,
				order_mst.trnno AS ORDERNO,
				order_mst.EDATE AS ORDEREDATE,
				customer.name AS CUSTOMERNAME,
				order_mst.total AS total,
				order_dtl.order_remarks AS order_remarks,
				customer.man_napno AS man_napno,
				"" AS narration,
				0 AS type,
				0 AS amount,
				transfer_mst.price AS price,
				transfer_mst.trnno AS SORTNO
			FROM transfer_mst
			INNER JOIN order_dtl ON order_dtl.trnno = transfer_mst.sr AND order_dtl.order_trnno = transfer_mst.order_id
			INNER JOIN order_mst ON order_mst.trnno = order_dtl.order_trnno
			INNER JOIN measurement ON measurement.code = order_mst.naap_no
			INNER JOIN customer ON customer.code = measurement.customer_code
			WHERE transfer_mst.empno = ?
			  AND transfer_mst.EDATE BETWEEN ? AND ?
			  AND (
				(transfer_mst.dept = "CUT" AND (transfer_mst.status = "Deactive" OR transfer_mst.status = "DELIVER"))
				OR (transfer_mst.dept = "STIT" AND (transfer_mst.status = "Deactive" OR transfer_mst.status = "DELIVER"))
			  )
			UNION ALL
			SELECT
				cash.EDATE AS ENDATE,
				NULL AS RDATE,
				cash.order_no AS ORDERNO,
				cash.EDATE AS ORDEREDATE,
				"" AS CUSTOMERNAME,
				0 AS total,
				"" AS order_remarks,
				"" AS man_napno,
				cash.narration AS narration,
				cash.type AS type,
				cash.amount AS amount,
				0 AS price,
				cash.trnno AS SORTNO
			FROM cash
			WHERE cash.emp_id = ?
			  AND cash.EDATE BETWEEN ? AND ?
		) employee_ledger_rows
		ORDER BY ENDATE ASC, SORTNO ASC
	';
	$q = $this->db->query($sql, array($emplyee, $date, $date1, $emplyee, $date, $date1));
	return $q->num_rows() > 0 ? $q->result() : array();
  }

  /**
   * Employee summary report — one row per employee with aggregated suit/salary/payment.
   * Replaces correlated subqueries in employee_report_MD.
   */
  function get_data_employee_summary($date, $date1)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	if ($date === '' || $date1 === '')
	{
		return array();
	}

	$sql = '
		SELECT
			e.empno,
			e.name,
			COALESCE(paid.paid_amt, 0) AS Paid,
			CASE
				WHEN e.empno = 10 THEN COALESCE(sal_cash.sal_amt, 0)
				ELSE COALESCE(tm.salary_amt, 0)
			END AS Salary,
			COALESCE(tm.suit_cnt, 0) AS Total_SUIT
		FROM employee e
		LEFT JOIN (
			SELECT emp_id,
				SUM(CASE WHEN type = 2 THEN amount * -1 ELSE amount END) AS paid_amt
			FROM cash
			WHERE EDATE BETWEEN ? AND ? AND type IN (1, 2)
			GROUP BY emp_id
		) paid ON paid.emp_id = e.empno
		LEFT JOIN (
			SELECT emp_id, SUM(amount) AS sal_amt
			FROM cash
			WHERE EDATE BETWEEN ? AND ? AND type = 7
			GROUP BY emp_id
		) sal_cash ON sal_cash.emp_id = e.empno
		LEFT JOIN (
			SELECT t.empno,
				SUM(t.price) AS salary_amt,
				COUNT(d.trnno) AS suit_cnt
			FROM transfer_mst t
			INNER JOIN order_dtl d ON d.trnno = t.sr AND d.order_trnno = t.order_id
			WHERE t.EDATE BETWEEN ? AND ?
			  AND (
				(t.dept = "CUT" AND t.status IN ("Deactive", "DELIVER"))
				OR (t.dept = "STIT" AND t.status IN ("Deactive", "DELIVER"))
			  )
			GROUP BY t.empno
		) tm ON tm.empno = e.empno
		ORDER BY e.name ASC
	';
	$q = $this->db->query($sql, array($date, $date1, $date, $date1, $date, $date1));
	return $q->num_rows() > 0 ? $q->result() : array();
  }
  
  
  function get_data_report_del_order($date,$date1)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	if ($date === '' || $date1 === '')
	{
		return array();
	}
	$from_ts = strtotime($date);
	$to_ts = strtotime($date1);
	if ($from_ts !== FALSE && $to_ts !== FALSE && $to_ts >= $from_ts)
	{
		$max_days = 92;
		$span = (int) floor(($to_ts - $from_ts) / 86400);
		if ($span > $max_days)
		{
			$date = date('Y-m-d', $to_ts - ($max_days * 86400));
		}
	}
	$this->db->select(
		'dmst.PAID,
		"Delivered" AS status,
		dmst.EDATE AS ENDATE,
		order_mst.trnno AS ORDERNO,
		customer.name AS CUSTOMERNAME,
		customer.man_napno,
		measurement.remarks AS narration,
		COALESCE(ddtl_cnt.suit_count, 0) AS COunt_suit',
		FALSE
	);
	$this->db->from('dmst');
	$this->db->join('order_mst', 'order_mst.trnno = dmst.order_id');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->join(
		'(SELECT ORDER_TRNNO, ord_dtl_date, COUNT(*) AS suit_count FROM ddtl WHERE ord_dtl_date >= '.$this->db->escape($date).' AND ord_dtl_date <= '.$this->db->escape($date1).' GROUP BY ORDER_TRNNO, ord_dtl_date) ddtl_cnt',
		'ddtl_cnt.ORDER_TRNNO = dmst.order_id AND ddtl_cnt.ord_dtl_date = dmst.EDATE',
		'left',
		FALSE
	);
  // $this->db->join('employee', 'employee.empno = transfer_mst.empno');
   // $this->db->where('measurement.customer_code = '.$emplyee.'' );
    $this->db->where('dmst.EDATE >=', $date);
    $this->db->where('dmst.EDATE <=', $date1);
	// Include all deliveries in range (including zero-paid) for accurate sheets.
	$this->db->order_by("dmst.EDATE");
	$this->db->order_by("order_mst.naap_no");
	$this->db->limit(1500);
	$q = $this->db->get();
	return ($q === FALSE) ? array() : $q->result();
  }
  
  
   function get_data_report_all_month($date,$date1,$emplyee,$rtype = '')
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	$rtype = trim((string) $rtype);
	if ($date === '' || $date1 === '')
	{
		return array();
	}

	// Select only report columns — never SELECT * (order_mst.image blows memory on long ranges).
	$this->db->select(
		'transfer_mst.status,
		transfer_mst.EDATE AS ENDATE,
		order_mst.trnno AS ORDERNO,
		customer.name AS CUSTOMERNAME,
		customer.man_napno,
		employee.name AS name,
		order_dtl.suitrate,
		order_dtl.drate,
		order_dtl.erate,
		order_dtl.order_remarks,
		measurement.remarks AS narration',
		FALSE
	);
	$this->db->from('transfer_mst');
	$this->db->join('order_dtl', 'order_dtl.trnno = transfer_mst.sr AND order_dtl.order_trnno = transfer_mst.order_id');
	$this->db->join('order_mst', 'order_mst.trnno = order_dtl.order_trnno');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->join('employee', 'employee.empno = transfer_mst.empno');
	$this->db->where('transfer_mst.EDATE >=', $date);
	$this->db->where('transfer_mst.EDATE <=', $date1);
	$this->db->where('transfer_mst.dept', 'STIT');
	if ($rtype === 'Ready')
	{
		$this->db->where('transfer_mst.status', 'Deactive');
	}
	elseif ($rtype === 'Del')
	{
		$this->db->where('transfer_mst.status', 'DELIVER');
	}
	else
	{
		$this->db->where_in('transfer_mst.status', array('Deactive', 'DELIVER'));
	}
	$this->db->where('employee.type', 'Stitching');
	$emplyee = trim((string) $emplyee);
	if ($emplyee !== '')
	{
		$this->db->where('transfer_mst.empno', (int) $emplyee);
	}
	$this->db->order_by('transfer_mst.EDATE', 'ASC');
	$this->db->order_by('transfer_mst.trnno', 'ASC');
	$q = $this->db->get();
	return $q->result();
  }
  
     function get_data_exp($date,$date1,$type)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	$type = trim((string) $type);
	if ($date === '' || $date1 === '')
	{
		return array();
	}
	$this->db->select('TRNNO, EDATE, narration, amount, type, emp_id, customer_id');
	$this->db->from('cash');
  //	 $this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
//	$this->db->join('customer', 'customer.code = measurement.customer_code');
    //$this->db->where('measurement.customer_code = order_mst.naap_no );
	$this->db->where('type !=', 2);
	$this->db->where('type !=', 1);
	$this->db->where('EDATE >=', $date);
	$this->db->where('EDATE <=', $date1);
	if($type != '')
	{
		$this->db->where('type', $type);
	}
	$this->db->order_by('EDATE', 'ASC');
	$this->db->order_by('TRNNO', 'ASC');
	$q = $this->db->get();
	return $q->num_rows() > 0 ? $q->result() : array();
    } 
     function get_pending_cash($date,$date1)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	if ($date === '' || $date1 === '')
	{
		return array();
	}

	$sql = '
		SELECT
			dmst.trnno,
			dmst.order_id,
			dmst.EDATE AS DEDATE,
			dmst.NPAID_dATE,
			dmst.balance,
			customer.code AS CUSTOMERNO,
			customer.name,
			customer.man_napno,
			customer.Phone
		FROM dmst
		INNER JOIN customer ON customer.code = dmst.customer_id
		WHERE dmst.balance != 0
		  AND dmst.pending = "YES"
		  AND dmst.NPAID_dATE BETWEEN ? AND ?
		  AND (dmst.paidstatus IS NULL OR dmst.paidstatus != "YES")
		ORDER BY dmst.NPAID_dATE ASC, dmst.trnno ASC
	';
	$q = $this->db->query($sql, array($date, $date1));
	return $q->num_rows() > 0 ? $q->result() : array();
  }

  function get_dmst_pending_row($trnno)
  {
    $trnno = (int) $trnno;
    if ($trnno <= 0)
    {
      return NULL;
    }
    $this->db->from('dmst');
    $this->db->where('trnno', $trnno);
    $this->db->where('pending', 'YES');
    $this->db->where('balance !=', 0);
    $this->db->group_start();
    $this->db->where('paidstatus IS NULL', NULL, FALSE);
    $this->db->or_where('paidstatus !=', 'YES');
    $this->db->group_end();
    $q = $this->db->get();
    return $q->num_rows() > 0 ? $q->row() : NULL;
  }

  function dmst_has_paid_cash_trnno_column()
  {
    static $has = NULL;
    if ($has === NULL)
    {
      $has = $this->db->field_exists('paid_cash_trnno', 'dmst');
    }
    return (bool) $has;
  }

  protected function cash_ledger_is_receipt_sql($column = 'type')
  {
	// Match legacy cash_ledger.php: ($payments->type == 2 or $payments->type == '')
	return '('.$column.' = 2 OR '.$column.' = \'2\' OR '.$column.' = \'\' OR '.$column.' IS NULL OR ('.$column.' + 0 = 0 AND '.$column.' <> \'0\'))';
  }

  /**
   * Legacy printed cash ledger receipt column (loose PHP ==).
   */
  function cash_ledger_is_receipt_type($type)
  {
	return ($type == 2 || $type == '');
  }

  protected function cash_ledger_sum_legacy_rows($rows)
  {
	$receipt = 0;
	$payment = 0;
	$line_count = 0;
	foreach ($rows as $row)
	{
		$amount = isset($row->RECEIPT) ? (float) $row->RECEIPT : 0;
		if ($amount == 0)
		{
			continue;
		}
		$line_count++;
		$type = property_exists($row, 'type') ? $row->type : NULL;
		if ($this->cash_ledger_is_receipt_type($type))
		{
			$receipt += $amount;
		}
		elseif ($type != 2 && $type != '')
		{
			$payment += $amount;
		}
	}
	return array(
		'receipt_total' => $receipt,
		'payment_total' => $payment,
		'period_net' => $receipt - $payment,
		'line_count' => $line_count,
	);
  }

  protected function cash_ledger_filter_legacy_rows($rows, $day = '', $source = '')
  {
	$day = trim((string) $day);
	$source = trim((string) $source);
	if ($day === '' && $source === '')
	{
		return $rows;
	}

	$filtered = array();
	foreach ($rows as $row)
	{
		if ($day !== '' && (!isset($row->ENTRYDATE) || (string) $row->ENTRYDATE !== $day))
		{
			continue;
		}
		if ($source !== '' && in_array($source, array('advance', 'customer', 'employee', 'other'), TRUE))
		{
			if (!isset($row->SOURCE) || (string) $row->SOURCE !== $source)
			{
				continue;
			}
		}
		$filtered[] = $row;
	}
	return $filtered;
  }

  /**
   * Same four overlapping queries as legacy gatedataold::get_data_cash().
   */
  function get_data_cash_legacy($date, $date1)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	$data = array();
	if ($date === '' || $date1 === '')
	{
		return $data;
	}

	$this->db->select('order_mst.advance AS RECEIPT, order_mst.EDATE AS ENTRYDATE, order_mst.trnno AS SORTNO, CONCAT("Advance Recevied Cash Agaisnt Order ", order_mst.trnno, " Naap No : ", customer.man_napno, "  ", customer.name) AS NARRATION', FALSE);
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where('order_mst.advance !=', 0);
	$this->db->where('order_mst.EDATE >=', $date);
	$this->db->where('order_mst.EDATE <=', $date1);
	$q = $this->db->get();
	if ($q->num_rows() > 0)
	{
		foreach ($q->result() as $row)
		{
			$row->SOURCE = 'advance';
			$data[] = $row;
		}
	}

	$this->db->select('cash.amount AS RECEIPT, cash.EDATE AS ENTRYDATE, cash.type, cash.TRNNO AS SORTNO, CONCAT(cash.narration, " Naap No : ", customer.man_napno, "  ", customer.name) AS NARRATION', FALSE);
	$this->db->from('cash');
	$this->db->join('customer', 'customer.code = cash.customer_id');
	$this->db->where('cash.EDATE >=', $date);
	$this->db->where('cash.EDATE <=', $date1);
	$this->db->where('cash.type !=', 7);
	$q1 = $this->db->get();
	if ($q1->num_rows() > 0)
	{
		foreach ($q1->result() as $row1)
		{
			$row1->SOURCE = 'customer';
			$data[] = $row1;
		}
	}

	$this->db->select('cash.amount AS RECEIPT, cash.EDATE AS ENTRYDATE, cash.type, cash.TRNNO AS SORTNO, CONCAT(cash.narration, "  ", employee.name) AS NARRATION', FALSE);
	$this->db->from('cash');
	$this->db->join('employee', 'employee.empno = cash.emp_id');
	$this->db->where('cash.EDATE >=', $date);
	$this->db->where('cash.EDATE <=', $date1);
	$this->db->where('cash.type !=', 7);
	$q2 = $this->db->get();
	if ($q2->num_rows() > 0)
	{
		foreach ($q2->result() as $row2)
		{
			$row2->SOURCE = 'employee';
			$data[] = $row2;
		}
	}

	$this->db->select('cash.amount AS RECEIPT, cash.EDATE AS ENTRYDATE, cash.type, cash.TRNNO AS SORTNO, cash.narration AS NARRATION', FALSE);
	$this->db->from('cash');
	$this->db->where('cash.EDATE >=', $date);
	$this->db->where('cash.EDATE <=', $date1);
	$this->db->where('cash.type !=', 1);
	$this->db->where('cash.type !=', 2);
	$this->db->where('cash.type !=', 7);
	$q3 = $this->db->get();
	if ($q3->num_rows() > 0)
	{
		foreach ($q3->result() as $row3)
		{
			$row3->SOURCE = 'other';
			$data[] = $row3;
		}
	}

	usort($data, function ($a, $b) {
		$a_date = isset($a->ENTRYDATE) ? $a->ENTRYDATE : '';
		$b_date = isset($b->ENTRYDATE) ? $b->ENTRYDATE : '';
		if ($a_date === $b_date)
		{
			$a_sort = isset($a->SORTNO) ? (int) $a->SORTNO : 0;
			$b_sort = isset($b->SORTNO) ? (int) $b->SORTNO : 0;
			if ($a_sort === $b_sort)
			{
				return 0;
			}
			return ($a_sort < $b_sort) ? -1 : 1;
		}
		return ($a_date < $b_date) ? -1 : 1;
	});

	return $data;
  }

  protected function cash_ledger_detail_filters_sql($day, $source, &$params, $date, $date1)
  {
	$filters = array('RECEIPT != 0');
	$params = $this->cash_ledger_bind_dates($date, $date1);
	if ($day !== '')
	{
		$filters[] = 'ENTRYDATE = ?';
		$params[] = $day;
	}
	if ($source !== '' && in_array($source, array('advance', 'customer', 'employee', 'other'), TRUE))
	{
		$filters[] = 'SOURCE = ?';
		$params[] = $source;
	}
	return implode(' AND ', $filters);
  }

  protected function cash_ledger_date_filter_sql($alias, $date, $date1, $day, &$params)
  {
	$sql = $alias.'.EDATE >= ? AND '.$alias.'.EDATE <= ?';
	$params[] = $date;
	$params[] = $date1;
	if ($day !== '')
	{
		$sql .= ' AND '.$alias.'.EDATE = ?';
		$params[] = $day;
	}
	return $sql;
  }

  protected function cash_ledger_branch_included($source, $branch)
  {
	$source = trim((string) $source);
	if ($source === '')
	{
		return TRUE;
	}
	return $source === $branch;
  }

  /**
   * Fast legacy totals without materializing UNION rows (same totals as old report).
   */
  protected function cash_ledger_period_totals_fast_sql($date, $date1, $day = '', $source = '')
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	$day = trim((string) $day);
	$source = trim((string) $source);
	$receipt_case = $this->cash_ledger_is_receipt_sql('c.type');
	$not_receipt = 'NOT ('.$receipt_case.')';
	$receipt_parts = array();
	$payment_parts = array();
	$count_parts = array();
	$params = array();

	if ($this->cash_ledger_branch_included($source, 'advance'))
	{
		$branch_params = array();
		$where = 'o.advance != 0 AND '.$this->cash_ledger_date_filter_sql('o', $date, $date1, $day, $branch_params);
		$receipt_parts[] = '(SELECT COALESCE(SUM(o.advance), 0) FROM order_mst o
			INNER JOIN measurement m ON m.code = o.naap_no
			INNER JOIN customer cu ON cu.code = m.customer_code
			WHERE '.$where.')';
		$count_parts[] = '(SELECT COUNT(*) FROM order_mst o
			INNER JOIN measurement m ON m.code = o.naap_no
			INNER JOIN customer cu ON cu.code = m.customer_code
			WHERE '.$where.')';
		$params = array_merge($params, $branch_params, $branch_params);
	}

	if ($this->cash_ledger_branch_included($source, 'customer'))
	{
		$branch_params = array();
		$where = 'c.type != 7 AND '.$this->cash_ledger_date_filter_sql('c', $date, $date1, $day, $branch_params);
		$receipt_parts[] = '(SELECT COALESCE(SUM(CASE WHEN '.$receipt_case.' THEN c.amount ELSE 0 END), 0)
			FROM cash c INNER JOIN customer cu ON cu.code = c.customer_id WHERE '.$where.')';
		$payment_parts[] = '(SELECT COALESCE(SUM(CASE WHEN '.$not_receipt.' THEN c.amount ELSE 0 END), 0)
			FROM cash c INNER JOIN customer cu ON cu.code = c.customer_id WHERE '.$where.')';
		$count_parts[] = '(SELECT COUNT(*) FROM cash c INNER JOIN customer cu ON cu.code = c.customer_id WHERE '.$where.')';
		$params = array_merge($params, $branch_params, $branch_params, $branch_params);
	}

	if ($this->cash_ledger_branch_included($source, 'employee'))
	{
		$branch_params = array();
		$where = 'c.type != 7 AND '.$this->cash_ledger_date_filter_sql('c', $date, $date1, $day, $branch_params);
		$receipt_parts[] = '(SELECT COALESCE(SUM(CASE WHEN '.$receipt_case.' THEN c.amount ELSE 0 END), 0)
			FROM cash c INNER JOIN employee e ON e.empno = c.emp_id WHERE '.$where.')';
		$payment_parts[] = '(SELECT COALESCE(SUM(CASE WHEN '.$not_receipt.' THEN c.amount ELSE 0 END), 0)
			FROM cash c INNER JOIN employee e ON e.empno = c.emp_id WHERE '.$where.')';
		$count_parts[] = '(SELECT COUNT(*) FROM cash c INNER JOIN employee e ON e.empno = c.emp_id WHERE '.$where.')';
		$params = array_merge($params, $branch_params, $branch_params, $branch_params);
	}

	if ($this->cash_ledger_branch_included($source, 'other'))
	{
		$branch_params = array();
		$where = 'c.type NOT IN (1, 2, 7) AND '.$this->cash_ledger_date_filter_sql('c', $date, $date1, $day, $branch_params);
		$payment_parts[] = '(SELECT COALESCE(SUM(c.amount), 0) FROM cash c WHERE '.$where.')';
		$count_parts[] = '(SELECT COUNT(*) FROM cash c WHERE '.$where.')';
		$params = array_merge($params, $branch_params, $branch_params);
	}

	if (empty($receipt_parts) && empty($payment_parts))
	{
		return array('receipt_total' => 0, 'payment_total' => 0, 'period_net' => 0, 'line_count' => 0);
	}

	$sql = 'SELECT '
		.(empty($receipt_parts) ? '0' : implode(' + ', $receipt_parts)).' AS receipt_total, '
		.(empty($payment_parts) ? '0' : implode(' + ', $payment_parts)).' AS payment_total, '
		.(empty($count_parts) ? '0' : implode(' + ', $count_parts)).' AS line_count';
	$row = $this->db->query($sql, $params)->row();
	$receipt = $row ? (float) $row->receipt_total : 0;
	$payment = $row ? (float) $row->payment_total : 0;
	return array(
		'receipt_total' => $receipt,
		'payment_total' => $payment,
		'period_net' => $receipt - $payment,
		'line_count' => $row ? (int) $row->line_count : 0,
	);
  }

  protected function cash_ledger_period_totals_sql($date, $date1, $day = '', $source = '')
  {
	return $this->cash_ledger_period_totals_fast_sql($date, $date1, $day, $source);
  }

  function get_cash_ledger_detail_totals($date, $date1, $day = '', $source = '')
  {
	return $this->cash_ledger_period_totals_sql($date, $date1, $day, $source);
  }

  /**
   * Receipt/payment totals per ledger category for the filtered period.
   */
  function get_cash_ledger_category_totals($date, $date1, $day = '', $source = '')
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	$day = trim((string) $day);
	$source = trim((string) $source);
	$categories = array(
		'advance' => array('receipt' => 0, 'payment' => 0, 'line_count' => 0),
		'customer' => array('receipt' => 0, 'payment' => 0, 'line_count' => 0),
		'employee' => array('receipt' => 0, 'payment' => 0, 'line_count' => 0),
		'other' => array('receipt' => 0, 'payment' => 0, 'line_count' => 0),
	);
	if ($date === '' || $date1 === '')
	{
		return $categories;
	}

	$params = array();
	$where = $this->cash_ledger_detail_filters_sql($day, $source, $params, $date, $date1);
	$receipt_case = $this->cash_ledger_is_receipt_sql();
	$sql = '
		SELECT
			SOURCE,
			COALESCE(SUM(CASE WHEN '.$receipt_case.' THEN RECEIPT ELSE 0 END), 0) AS receipt_total,
			COALESCE(SUM(CASE WHEN NOT ('.$receipt_case.') THEN RECEIPT ELSE 0 END), 0) AS payment_total,
			COUNT(*) AS line_count
		FROM (
			'.$this->cash_ledger_rows_inner_sql().'
		) cash_ledger_rows
		WHERE '.$where.'
		GROUP BY SOURCE
	';
	$rows = $this->db->query($sql, $params)->result();
	foreach ($rows as $row)
	{
		$key = isset($row->SOURCE) ? (string) $row->SOURCE : 'other';
		if (!isset($categories[$key]))
		{
			$key = 'other';
		}
		$categories[$key]['receipt'] = (float) $row->receipt_total;
		$categories[$key]['payment'] = (float) $row->payment_total;
		$categories[$key]['line_count'] = (int) $row->line_count;
	}
	return $categories;
  }

  protected function cash_ledger_totals_inner_sql()
  {
	return '
			SELECT
				order_mst.advance AS RECEIPT,
				order_mst.EDATE AS ENTRYDATE,
				NULL AS type,
				\'advance\' AS SOURCE
			FROM order_mst
			INNER JOIN measurement ON measurement.code = order_mst.naap_no
			INNER JOIN customer ON customer.code = measurement.customer_code
			WHERE order_mst.advance != 0
			  AND order_mst.EDATE BETWEEN ? AND ?
			UNION ALL
			SELECT
				cash.amount AS RECEIPT,
				cash.EDATE AS ENTRYDATE,
				cash.type,
				\'customer\' AS SOURCE
			FROM cash
			INNER JOIN customer ON customer.code = cash.customer_id
			WHERE cash.EDATE BETWEEN ? AND ?
			  AND cash.type != 7
			UNION ALL
			SELECT
				cash.amount AS RECEIPT,
				cash.EDATE AS ENTRYDATE,
				cash.type,
				\'employee\' AS SOURCE
			FROM cash
			INNER JOIN employee ON employee.empno = cash.emp_id
			WHERE cash.EDATE BETWEEN ? AND ?
			  AND cash.type != 7
			UNION ALL
			SELECT
				cash.amount AS RECEIPT,
				cash.EDATE AS ENTRYDATE,
				cash.type,
				\'other\' AS SOURCE
			FROM cash
			WHERE cash.EDATE BETWEEN ? AND ?
			  AND cash.type != 1
			  AND cash.type != 2
			  AND cash.type != 7
	';
  }

  protected function cash_ledger_rows_inner_sql()
  {
	return '
			SELECT
				order_mst.advance AS RECEIPT,
				order_mst.EDATE AS ENTRYDATE,
				NULL AS type,
				\'advance\' AS SOURCE,
				CONCAT("Advance Received Cash Against Order ", order_mst.trnno, " Naap No : ", customer.man_napno, "  ", customer.name) AS NARRATION,
				order_mst.trnno AS SORTNO
			FROM order_mst
			INNER JOIN measurement ON measurement.code = order_mst.naap_no
			INNER JOIN customer ON customer.code = measurement.customer_code
			WHERE order_mst.advance != 0
			  AND order_mst.EDATE BETWEEN ? AND ?
			UNION ALL
			SELECT
				cash.amount AS RECEIPT,
				cash.EDATE AS ENTRYDATE,
				cash.type,
				\'customer\' AS SOURCE,
				CONCAT(cash.narration, " Naap No : ", customer.man_napno, "  ", customer.name) AS NARRATION,
				cash.TRNNO AS SORTNO
			FROM cash
			INNER JOIN customer ON customer.code = cash.customer_id
			WHERE cash.EDATE BETWEEN ? AND ?
			  AND cash.type != 7
			UNION ALL
			SELECT
				cash.amount AS RECEIPT,
				cash.EDATE AS ENTRYDATE,
				cash.type,
				\'employee\' AS SOURCE,
				CONCAT(cash.narration, "  ", employee.name) AS NARRATION,
				cash.TRNNO AS SORTNO
			FROM cash
			INNER JOIN employee ON employee.empno = cash.emp_id
			WHERE cash.EDATE BETWEEN ? AND ?
			  AND cash.type != 7
			UNION ALL
			SELECT
				cash.amount AS RECEIPT,
				cash.EDATE AS ENTRYDATE,
				cash.type,
				\'other\' AS SOURCE,
				cash.narration AS NARRATION,
				cash.TRNNO AS SORTNO
			FROM cash
			WHERE cash.EDATE BETWEEN ? AND ?
			  AND cash.type != 1
			  AND cash.type != 2
			  AND cash.type != 7
	';
  }

  protected function cash_ledger_bind_dates($date, $date1)
  {
	return array($date, $date1, $date, $date1, $date, $date1, $date, $date1);
  }

  function get_cash_ledger_summary($date, $date1)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	if ($date === '' || $date1 === '')
	{
		return array(
			'opening' => 0,
			'receipt_total' => 0,
			'payment_total' => 0,
			'period_net' => 0,
			'closing' => 0,
			'line_count' => 0,
		);
	}

	$opening = $this->get_opeing_cash($date);
	$totals = $this->cash_ledger_period_totals_sql($date, $date1);
	return array(
		'opening' => $opening,
		'receipt_total' => $totals['receipt_total'],
		'payment_total' => $totals['payment_total'],
		'period_net' => $totals['period_net'],
		'closing' => $opening + $totals['period_net'],
		'line_count' => $totals['line_count'],
	);
  }

  protected function cash_ledger_group_config($group)
  {
	$group = strtolower(trim((string) $group));
	$map = array(
		'daily' => array(
			'group_by' => 'ENTRYDATE',
			'drill_next' => 'detail',
		),
		'month' => array(
			'group_by' => "DATE_FORMAT(ENTRYDATE, '%Y-%m')",
			'drill_next' => 'daily',
		),
		'quarter' => array(
			'group_by' => "CONCAT(YEAR(ENTRYDATE), '-Q', QUARTER(ENTRYDATE))",
			'drill_next' => 'daily',
		),
		'halfyear' => array(
			'group_by' => "CONCAT(YEAR(ENTRYDATE), '-H', IF(MONTH(ENTRYDATE) <= 6, 1, 2))",
			'drill_next' => 'month',
		),
		'year' => array(
			'group_by' => 'YEAR(ENTRYDATE)',
			'drill_next' => 'month',
		),
	);
	return isset($map[$group]) ? $map[$group] : $map['daily'];
  }

  function cash_ledger_period_label($group, $period_key, $period_from)
  {
	$group = strtolower(trim((string) $group));
	$period_key = (string) $period_key;
	if ($group === 'daily' && $period_from !== '')
	{
		return date('d-m-Y', strtotime($period_from));
	}
	if ($group === 'month' && $period_from !== '')
	{
		return date('M Y', strtotime($period_from));
	}
	if ($group === 'quarter' && preg_match('/^(\d{4})-Q([1-4])$/', $period_key, $m))
	{
		return 'Q'.$m[2].' '.$m[1];
	}
	if ($group === 'halfyear' && preg_match('/^(\d{4})-H([12])$/', $period_key, $m))
	{
		return ($m[2] === '1' ? 'H1' : 'H2').' '.$m[1];
	}
	if ($group === 'year')
	{
		return (string) $period_key;
	}
	return $period_key;
  }

  protected function cash_ledger_period_key_from_date($group, $entry_date)
  {
	$group = strtolower(trim((string) $group));
	$entry_date = trim((string) $entry_date);
	if ($entry_date === '')
	{
		return '';
	}

	$ts = strtotime($entry_date);
	$year = (int) date('Y', $ts);
	$month = (int) date('n', $ts);
	if ($group === 'daily')
	{
		return date('Y-m-d', $ts);
	}
	if ($group === 'month')
	{
		return date('Y-m', $ts);
	}
	if ($group === 'quarter')
	{
		return $year.'-Q'.(int) ceil($month / 3);
	}
	if ($group === 'halfyear')
	{
		return $year.'-H'.($month <= 6 ? 1 : 2);
	}
	if ($group === 'year')
	{
		return (string) $year;
	}
	return $entry_date;
  }

  function get_cash_ledger_grouped_summary($date, $date1, $group = 'daily')
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	if ($date === '' || $date1 === '')
	{
		return array();
	}

	$config = $this->cash_ledger_group_config($group);
	$receipt_case = $this->cash_ledger_is_receipt_sql();
	$sql = '
		SELECT
			'.$config['group_by'].' AS period_key,
			MIN(ENTRYDATE) AS period_from,
			MAX(ENTRYDATE) AS period_to,
			COALESCE(SUM(CASE WHEN '.$receipt_case.' THEN RECEIPT ELSE 0 END), 0) AS receipt_total,
			COALESCE(SUM(CASE WHEN NOT ('.$receipt_case.') THEN RECEIPT ELSE 0 END), 0) AS payment_total,
			COUNT(*) AS line_count
		FROM (
			'.$this->cash_ledger_totals_inner_sql().'
		) cash_ledger_rows
		WHERE RECEIPT != 0
		GROUP BY '.$config['group_by'].'
		ORDER BY period_from ASC
	';
	$rows = $this->db->query($sql, $this->cash_ledger_bind_dates($date, $date1))->result();
	foreach ($rows as $row)
	{
		$row->drill_next = $config['drill_next'];
		$row->period_label = $this->cash_ledger_period_label($group, $row->period_key, $row->period_from);
	}
	return $rows;
  }

  /**
   * Period summary rows grouped by ledger category (year / quarter / month / day).
   */
  function get_cash_ledger_period_summary_by_category($date, $date1, $group = 'daily')
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	if ($date === '' || $date1 === '')
	{
		return array();
	}

	$config = $this->cash_ledger_group_config($group);
	$receipt_case = $this->cash_ledger_is_receipt_sql();
	$sql = '
		SELECT
			SOURCE,
			'.$config['group_by'].' AS period_key,
			MIN(ENTRYDATE) AS period_from,
			MAX(ENTRYDATE) AS period_to,
			COALESCE(SUM(CASE WHEN '.$receipt_case.' THEN RECEIPT ELSE 0 END), 0) AS receipt_total,
			COALESCE(SUM(CASE WHEN NOT ('.$receipt_case.') THEN RECEIPT ELSE 0 END), 0) AS payment_total,
			COUNT(*) AS line_count
		FROM (
			'.$this->cash_ledger_rows_inner_sql().'
		) cash_ledger_rows
		WHERE RECEIPT != 0
		GROUP BY SOURCE, '.$config['group_by'].'
		ORDER BY SOURCE, period_from ASC
	';
	$rows = $this->db->query($sql, $this->cash_ledger_bind_dates($date, $date1))->result();
	foreach ($rows as $row)
	{
		$row->drill_next = $config['drill_next'];
		$row->period_label = $this->cash_ledger_period_label($group, $row->period_key, $row->period_from);
	}
	return $rows;
  }

  function get_cash_ledger_daily_summary($date, $date1)
  {
	return $this->get_cash_ledger_grouped_summary($date, $date1, 'daily');
  }

  function get_cash_ledger_detail($date, $date1, $day = '', $source = '')
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	$day = trim((string) $day);
	$source = trim((string) $source);
	if ($date === '' || $date1 === '')
	{
		return array();
	}

	$params = array();
	$where = $this->cash_ledger_detail_filters_sql($day, $source, $params, $date, $date1);
	$receipt_case = $this->cash_ledger_is_receipt_sql();
	$sql = '
		SELECT
			RECEIPT,
			ENTRYDATE,
			type,
			SOURCE,
			NARRATION,
			SORTNO,
			CASE WHEN '.$receipt_case.' THEN 1 ELSE 0 END AS is_receipt
		FROM (
			'.$this->cash_ledger_rows_inner_sql().'
		) cash_ledger_rows
		WHERE '.$where.'
		ORDER BY ENTRYDATE ASC, SORTNO ASC
	';
	return $this->db->query($sql, $params)->result();
  }

   function get_data_cash($date,$date1)
  {
	return $this->get_cash_ledger_detail($date, $date1);
  }

}

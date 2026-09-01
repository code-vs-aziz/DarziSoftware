<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_data_model extends CI_Model

{
  function count_pending_transfer_lines($dept)
  {
    $dept = trim((string) $dept);
    if ( ! in_array($dept, array('HO', 'CUT', 'HO2', 'STIT'), TRUE))
    {
      return 0;
    }

    $counts = $this->count_pending_transfer_lines_batch(array($dept));
    return isset($counts[$dept]) ? (int) $counts[$dept] : 0;
  }

  /**
   * Fast aggregate counts for transfer pipeline stages (one query for all depts).
   */
  function count_pending_transfer_lines_batch(array $departments = NULL)
  {
    $allowed = array('HO', 'CUT', 'HO2', 'STIT');
    if ($departments === NULL)
    {
      $departments = $allowed;
    }

    $departments = array_values(array_intersect($allowed, array_map('strval', $departments)));
    if ($departments === array())
    {
      return array();
    }

    $dept_list = implode(',', array_map(array($this->db, 'escape'), $departments));
    $sql = '
      SELECT transfer_mst.dept AS dept, COUNT(*) AS cnt
      FROM order_mst
      INNER JOIN order_dtl ON order_mst.trnno = order_dtl.order_trnno
      INNER JOIN transfer_mst
        ON transfer_mst.order_id = order_dtl.order_trnno
        AND transfer_mst.sr = order_dtl.trnno
        AND transfer_mst.dept IN ('.$dept_list.')
        AND transfer_mst.status = "Active"
      WHERE (order_mst.ostatus IS NULL OR order_mst.ostatus != 1)
      GROUP BY transfer_mst.dept
    ';
    $q = $this->db->query($sql);
    $counts = array_fill_keys($departments, 0);
    if ($q && $q->num_rows() > 0)
    {
      foreach ($q->result() as $row)
      {
        $counts[$row->dept] = (int) $row->cnt;
      }
    }
    return $counts;
  }

  /**
   * Ready for delivery lines (matches Order/order_issue list).
   */
  function count_ready_to_deliver_lines()
  {
    $this->db->select('COUNT(DISTINCT transfer_mst.order_id) AS cnt', FALSE);
    $this->db->from('transfer_mst');
    $this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
    $this->db->join('order_dtl', 'order_dtl.order_trnno = transfer_mst.order_id AND order_dtl.trnno = transfer_mst.sr', 'inner');
    $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
    $this->db->join('customer', 'customer.code = measurement.customer_code');
    $this->db->where('transfer_mst.dept', 'STIT');
    $this->db->where('transfer_mst.status', 'Deactive');
    $this->db->where('(order_mst.ostatus IS NULL OR order_mst.ostatus != 1)', NULL, FALSE);
    $this->db->where(
      'transfer_mst.trnno = (SELECT MAX(t2.trnno) FROM transfer_mst t2 WHERE t2.order_id = transfer_mst.order_id AND t2.sr = transfer_mst.sr AND t2.dept = \'STIT\' AND t2.status = \'Deactive\')',
      NULL,
      FALSE
    );
    $row = $this->db->get()->row();

    return $row ? (int) $row->cnt : 0;
  }

  function clear_dashboard_summary_cache()
  {
    $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
    $this->cache->delete('dashboard_summary_v2_'.date('Y-m-d'));
    $this->cache->delete('dashboard_summary_'.date('Y-m-d'));
  }

  function dashboard_summary()
  {
    $today = date('Y-m-d');
    $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
    $cache_key = 'dashboard_summary_v2_'.$today;
    $cached = $this->cache->get($cache_key);
    if ($cached !== FALSE)
    {
      return $cached;
    }

    $summary = array(
      'ready_to_deliver' => 0,
      'today_return' => 0,
      'today_bookings' => 0,
      'today_deliveries' => 0,
      'pending_booking' => 0,
      'pending_cutting' => 0,
      'pending_ho2' => 0,
      'pending_stitching' => 0,
    );

    $summary['ready_to_deliver'] = $this->count_ready_to_deliver_lines();
    $pending_counts = $this->count_pending_transfer_lines_batch(array('HO', 'CUT', 'HO2', 'STIT'));
    $summary['pending_booking'] = isset($pending_counts['HO']) ? (int) $pending_counts['HO'] : 0;
    $summary['pending_cutting'] = isset($pending_counts['CUT']) ? (int) $pending_counts['CUT'] : 0;
    $summary['pending_ho2'] = isset($pending_counts['HO2']) ? (int) $pending_counts['HO2'] : 0;
    $summary['pending_stitching'] = isset($pending_counts['STIT']) ? (int) $pending_counts['STIT'] : 0;

    $today_sql = $this->db->escape($today);
    $this->db->select('
      SUM(CASE WHEN RDATE = '.$today_sql.' AND ostatus != 1 THEN 1 ELSE 0 END) AS today_return,
      SUM(CASE WHEN EDATE = '.$today_sql.' AND ostatus != 1 THEN 1 ELSE 0 END) AS today_bookings
    ', FALSE);
    $this->db->from('order_mst');
    $this->db->group_start();
    $this->db->where('RDATE', $today);
    $this->db->or_where('EDATE', $today);
    $this->db->group_end();
    $this->db->where('ostatus !=', 1);
    $row = $this->db->get()->row();
    if ($row)
    {
      $summary['today_return'] = (int) $row->today_return;
      $summary['today_bookings'] = (int) $row->today_bookings;
    }

    $this->db->select('COUNT(DISTINCT order_id) AS total', FALSE);
    $this->db->from('dmst');
    $this->db->where('EDATE', $today);
    $row = $this->db->get()->row();
    $summary['today_deliveries'] = $row ? (int) $row->total : 0;

    $this->cache->save($cache_key, $summary, 120);
    return $summary;
  }

  function dashboard_top_customers($limit = 5)
  {
    $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
    $limit = max(1, (int) $limit);
    $from = date('Y-m-01');
    $to = date('Y-m-d');
    $cache_key = 'dashboard_top_customers_'.$from.'_'.$to.'_'.$limit;
    $cached = $this->cache->get($cache_key);
    if ($cached !== FALSE)
    {
      return $cached;
    }

    $this->db->select('customer.name, customer.Phone, customer.man_napno, COUNT(DISTINCT order_mst.trnno) AS total_orders, SUM(order_mst.total) AS total_amount', FALSE);
    $this->db->from('order_mst');
    $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
    $this->db->join('customer', 'customer.code = measurement.customer_code');
    $this->db->where('order_mst.EDATE >=', $from);
    $this->db->where('order_mst.EDATE <=', $to);
    $this->db->where('order_mst.ostatus !=', 1);
    $this->db->group_by('customer.code');
    $this->db->order_by('total_amount', 'DESC');
    $this->db->limit($limit);
    $q = $this->db->get();

    if ($q->num_rows() > 0)
    {
      $data = $q->result();
      $this->cache->save($cache_key, $data, 300);
      return $data;
    }
    $this->cache->save($cache_key, array(), 300);
    return array();
  }

  function dashboard_latest_orders($limit = 10)
  {
    $allowed_limits = array(10, 25, 50, 100);
    $limit = (int) $limit;
    if (!in_array($limit, $allowed_limits, TRUE))
    {
      $limit = 10;
    }

    $this->db->select('
      order_mst.trnno,
      order_mst.EDATE,
      order_mst.RDATE,
      order_mst.qty,
      order_mst.total,
      order_mst.advance,
      customer.name,
      customer.Phone,
      customer.man_napno
    ', FALSE);
    $this->db->from('order_mst');
    $this->db->join('measurement', 'measurement.code = order_mst.naap_no', 'left');
    $this->db->join('customer', 'customer.code = measurement.customer_code', 'left');
    $this->db->where('(order_mst.ostatus IS NULL OR order_mst.ostatus != 1)', NULL, FALSE);
    $this->db->order_by('order_mst.trnno', 'DESC');
    $this->db->limit($limit);
    $q = $this->db->get();
    return $q->num_rows() > 0 ? $q->result() : array();
  }

  protected function financial_group_sql($date_field, $group)
  {
    $date_field = (string) $date_field;
    $group = strtolower(trim((string) $group));
    if ($group === 'monthly')
    {
      return array(
        'key' => 'DATE_FORMAT('.$date_field.', "%Y-%m")',
        'label' => 'DATE_FORMAT('.$date_field.', "%M %Y")'
      );
    }
    return array(
      'key' => 'DATE('.$date_field.')',
      'label' => 'DATE_FORMAT('.$date_field.', "%d-%m-%Y")'
    );
  }

  function get_financial_dashboard_summary($date, $date1)
  {
    $date = trim((string) $date);
    $date1 = trim((string) $date1);
    if ($date === '' || $date1 === '')
    {
      return array();
    }

    $summary = array(
      'bookings' => 0,
      'booked_qty' => 0,
      'booked_sales' => 0,
      'advance' => 0,
      'discount' => 0,
      'cash_receipts' => 0,
      'cash_payments' => 0,
      'expenses' => 0,
      'salary' => 0,
      'delivery_paid' => 0,
      'delivered_orders' => 0,
      'pending_receivable' => 0,
      'pending_customers' => 0,
    );

    $sql = '
      SELECT
        SUM(metric.bookings) AS bookings,
        SUM(metric.booked_qty) AS booked_qty,
        SUM(metric.booked_sales) AS booked_sales,
        SUM(metric.advance) AS advance,
        SUM(metric.discount) AS discount,
        SUM(metric.cash_receipts) AS cash_receipts,
        SUM(metric.cash_payments) AS cash_payments,
        SUM(metric.expenses) AS expenses,
        SUM(metric.salary) AS salary,
        SUM(metric.delivery_paid) AS delivery_paid,
        SUM(metric.delivered_orders) AS delivered_orders,
        SUM(metric.pending_receivable) AS pending_receivable,
        SUM(metric.pending_customers) AS pending_customers
      FROM (
        SELECT
          COUNT(*) AS bookings,
          COALESCE(SUM(qty), 0) AS booked_qty,
          COALESCE(SUM(total), 0) AS booked_sales,
          COALESCE(SUM(advance), 0) AS advance,
          COALESCE(SUM(Discount), 0) AS discount,
          0 AS cash_receipts,
          0 AS cash_payments,
          0 AS expenses,
          0 AS salary,
          0 AS delivery_paid,
          0 AS delivered_orders,
          0 AS pending_receivable,
          0 AS pending_customers
        FROM order_mst
        WHERE EDATE BETWEEN ? AND ?
          AND (ostatus IS NULL OR ostatus != 1)
        UNION ALL
        SELECT
          0, 0, 0, 0, 0,
          COALESCE(SUM(CASE WHEN type = 2 THEN amount ELSE 0 END), 0),
          COALESCE(SUM(CASE WHEN type = 1 THEN ABS(amount) ELSE 0 END), 0),
          COALESCE(SUM(CASE WHEN type IN (3,4,5,6) THEN ABS(amount) ELSE 0 END), 0),
          COALESCE(SUM(CASE WHEN type = 7 THEN ABS(amount) ELSE 0 END), 0),
          0, 0, 0, 0
        FROM cash
        WHERE EDATE BETWEEN ? AND ?
        UNION ALL
        SELECT
          0, 0, 0, 0, 0, 0, 0, 0, 0,
          COALESCE(SUM(PAID), 0),
          COUNT(DISTINCT order_id),
          COALESCE(SUM(CASE WHEN pending = "YES" AND (paidstatus IS NULL OR paidstatus != "YES") THEN balance ELSE 0 END), 0),
          COUNT(DISTINCT CASE WHEN pending = "YES" AND (paidstatus IS NULL OR paidstatus != "YES") THEN customer_id END)
        FROM dmst
        WHERE EDATE BETWEEN ? AND ?
      ) metric
    ';
    $row = $this->db->query($sql, array($date, $date1, $date, $date1, $date, $date1))->row();
    if ($row)
    {
      foreach ($summary as $key => $value)
      {
        $summary[$key] = isset($row->$key) ? (float) $row->$key : 0;
      }
    }
    $summary['cash_inflow'] = $summary['advance'] + $summary['cash_receipts'];
    $summary['cash_outflow'] = $summary['cash_payments'] + $summary['expenses'] + $summary['salary'];
    $summary['net_cash'] = $summary['cash_inflow'] - $summary['cash_outflow'];
    $summary['net_profit_estimate'] = $summary['booked_sales'] - $summary['expenses'] - $summary['salary'] - $summary['discount'];
    return $summary;
  }

  function get_financial_top_customers($date, $date1, $limit = 5)
  {
    $date = trim((string) $date);
    $date1 = trim((string) $date1);
    $limit = max(1, (int) $limit);
    if ($date === '' || $date1 === '')
    {
      return array();
    }

    $this->db->select('customer.man_napno, customer.name, customer.Phone, COUNT(DISTINCT order_mst.trnno) AS orders, COALESCE(SUM(order_mst.qty), 0) AS qty, COALESCE(SUM(order_mst.total), 0) AS total_amount', FALSE);
    $this->db->from('order_mst');
    $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
    $this->db->join('customer', 'customer.code = measurement.customer_code');
    $this->db->where('order_mst.EDATE >=', $date);
    $this->db->where('order_mst.EDATE <=', $date1);
    $this->db->where('(order_mst.ostatus IS NULL OR order_mst.ostatus != 1)', NULL, FALSE);
    $this->db->group_by('customer.code');
    $this->db->order_by('total_amount', 'DESC');
    $this->db->limit($limit);
    $q = $this->db->get();
    return $q->num_rows() > 0 ? $q->result() : array();
  }

  function get_financial_grouped_comparison($date, $date1, $group)
  {
    $date = trim((string) $date);
    $date1 = trim((string) $date1);
    $group = strtolower(trim((string) $group)) === 'monthly' ? 'monthly' : 'daily';
    if ($date === '' || $date1 === '')
    {
      return array();
    }

    $order_period = $this->financial_group_sql('EDATE', $group);
    $cash_period = $this->financial_group_sql('EDATE', $group);
    $delivery_period = $this->financial_group_sql('EDATE', $group);
    $sql = '
      SELECT
        period_key,
        MAX(period_label) AS period_label,
        SUM(bookings) AS bookings,
        SUM(booked_qty) AS booked_qty,
        SUM(booked_sales) AS booked_sales,
        SUM(advance) AS advance,
        SUM(discount) AS discount,
        SUM(cash_receipts) AS cash_receipts,
        SUM(cash_payments) AS cash_payments,
        SUM(expenses) AS expenses,
        SUM(salary) AS salary,
        SUM(delivery_paid) AS delivery_paid,
        SUM(delivered_orders) AS delivered_orders,
        SUM(pending_receivable) AS pending_receivable,
        SUM(pending_customers) AS pending_customers
      FROM (
        SELECT
          '.$order_period['key'].' AS period_key,
          '.$order_period['label'].' AS period_label,
          COUNT(*) AS bookings,
          COALESCE(SUM(qty), 0) AS booked_qty,
          COALESCE(SUM(total), 0) AS booked_sales,
          COALESCE(SUM(advance), 0) AS advance,
          COALESCE(SUM(Discount), 0) AS discount,
          0 AS cash_receipts,
          0 AS cash_payments,
          0 AS expenses,
          0 AS salary,
          0 AS delivery_paid,
          0 AS delivered_orders,
          0 AS pending_receivable,
          0 AS pending_customers
        FROM order_mst
        WHERE EDATE BETWEEN ? AND ?
          AND (ostatus IS NULL OR ostatus != 1)
        GROUP BY period_key, period_label
        UNION ALL
        SELECT
          '.$cash_period['key'].' AS period_key,
          '.$cash_period['label'].' AS period_label,
          0, 0, 0, 0, 0,
          COALESCE(SUM(CASE WHEN type = 2 THEN amount ELSE 0 END), 0),
          COALESCE(SUM(CASE WHEN type = 1 THEN ABS(amount) ELSE 0 END), 0),
          COALESCE(SUM(CASE WHEN type IN (3,4,5,6) THEN ABS(amount) ELSE 0 END), 0),
          COALESCE(SUM(CASE WHEN type = 7 THEN ABS(amount) ELSE 0 END), 0),
          0, 0, 0, 0
        FROM cash
        WHERE EDATE BETWEEN ? AND ?
        GROUP BY period_key, period_label
        UNION ALL
        SELECT
          '.$delivery_period['key'].' AS period_key,
          '.$delivery_period['label'].' AS period_label,
          0, 0, 0, 0, 0, 0, 0, 0, 0,
          COALESCE(SUM(PAID), 0),
          COUNT(DISTINCT order_id),
          COALESCE(SUM(CASE WHEN pending = "YES" AND (paidstatus IS NULL OR paidstatus != "YES") THEN balance ELSE 0 END), 0),
          COUNT(DISTINCT CASE WHEN pending = "YES" AND (paidstatus IS NULL OR paidstatus != "YES") THEN customer_id END)
        FROM dmst
        WHERE EDATE BETWEEN ? AND ?
        GROUP BY period_key, period_label
      ) financial_rows
      GROUP BY period_key
      ORDER BY period_key ASC
    ';
    $q = $this->db->query($sql, array($date, $date1, $date, $date1, $date, $date1));
    return $q->num_rows() > 0 ? $q->result() : array();
  }

  function get_financial_cash_flow($date, $date1, $group)
  {
    return $this->get_financial_grouped_comparison($date, $date1, $group);
  }

}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_query_model extends CI_Model

{
  function get_data_customer_sale($date,$date1)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	if ($date === '' || $date1 === '')
	{
		return array();
	}
	$this->db->select('customer.man_napno,customer.name as CUSTOMER_NAME,sum(order_mst.qty) AS TOTAL_QTY,sum(order_mst.total) AS TOTAL_AMOUNT');
	$this->db->from('order_mst');
  	 $this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
	$this->db->join('customer', 'customer.code = measurement.customer_code');
    $this->db->where('order_mst.EDATE >=', $date);
    $this->db->where('order_mst.EDATE <=', $date1);
    $this->db->where('order_mst.total >', 0);
	$this->db->group_by('customer.code');
	$this->db->order_by('TOTAL_QTY', 'DESC');
	$q = $this->db->get();
	return $q->num_rows() > 0 ? $q->result() : array();
    }
 
  function get_order_mst($where)
  {
	$select = 'order_mst.trnno, order_mst.EDATE, order_mst.RDATE, order_mst.qty, order_mst.total, order_mst.advance, order_mst.discount,
		order_mst.naap_no AS MCODE, '.$this->_order_customer_snap_select_sql().', customer.Phone';

	$this->db->distinct();
	$this->db->select($select, FALSE);
	$this->db->from('transfer_mst');
	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where($where);
	$q = $this->db->get();

	if ($q->num_rows() > 0)
	{
		return $q->result();
	}

	$this->db->reset_query();
	$this->db->distinct();
	$this->db->select($select, FALSE);
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where($where);
	$q = $this->db->get();

	return $q->num_rows() > 0 ? $q->result() : array();
  }
  
    function get_naap()
  {
	$this->db->distinct();
    $this->db->select('*');
	$this->db->from('customer');
 	$this->db->join('measurement',  'customer.code = measurement.customer_code');	
 	//$this->db->join('customer', 'customer.code = measurement.customer_code');	
	//$this->db->where($where);
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }
  }
 
 
  function get_order_det()
  {
	return $this->get_order_ready_deliver(array());
  }

  protected function _aggregate_ready_deliver_by_order($rows, $order_limit = 500)
  {
	$map = array();
	foreach ($rows as $row)
	{
		$order_no = (int) (isset($row->ORDERNO) ? $row->ORDERNO : 0);
		if ($order_no < 1)
		{
			continue;
		}
		$line_type = trim((string) (isset($row->line_type) ? $row->line_type : ''));
		if (!isset($map[$order_no]))
		{
			$map[$order_no] = (object) array(
				'ORDERNO' => $order_no,
				'line_count' => 1,
				'line_types' => $line_type !== '' ? array($line_type) : array(),
				'name' => isset($row->name) ? $row->name : '',
				'man_napno' => isset($row->man_napno) ? $row->man_napno : '',
				'Phone' => isset($row->Phone) ? $row->Phone : '',
				'RETURNDATE' => isset($row->RETURNDATE) ? $row->RETURNDATE : '',
				'total' => isset($row->total) ? $row->total : 0,
				'advance' => isset($row->advance) ? $row->advance : 0,
			);
			continue;
		}
		$map[$order_no]->line_count++;
		if ($line_type !== '')
		{
			$map[$order_no]->line_types[] = $line_type;
		}
	}

	$out = array();
	foreach ($map as $order_no => $agg)
	{
		$counts = array();
		foreach ($agg->line_types as $type_name)
		{
			if (!isset($counts[$type_name]))
			{
				$counts[$type_name] = 0;
			}
			$counts[$type_name]++;
		}
		$parts = array();
		foreach ($counts as $type_name => $qty)
		{
			$parts[] = $qty > 1 ? $qty.' × '.$type_name : $type_name;
		}
		$agg->line_type = implode(', ', $parts);
		if ($agg->line_type === '' && $agg->line_count > 0)
		{
			$agg->line_type = (int) $agg->line_count.' item(s)';
		}
		$out[] = $agg;
	}

	$order_limit = max(1, (int) $order_limit);
	if (count($out) > $order_limit)
	{
		$out = array_slice($out, 0, $order_limit);
	}
	return $out;
  }

  /**
   * Orders ready for delivery (STIT + Deactive), one row per order.
   * Caps order IDs in SQL before loading line details (avoids hydrating all history).
   */
  function get_order_ready_deliver($filters = array())
  {
	$filters = $this->_normalize_order_status_filters($filters);

	$order_limit = 100;
	if (isset($filters['row_limit']) && (int) $filters['row_limit'] > 0)
	{
		$order_limit = min(200, (int) $filters['row_limit']);
	}

	// Phase 1: bounded distinct order IDs (latest STIT Deactive transfer per line).
	$this->db->select('order_mst.trnno AS ORDERNO, MIN(order_mst.RDATE) AS RETURNDATE', FALSE);
	$this->db->from('transfer_mst');
	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
	$this->db->where('transfer_mst.dept', 'STIT');
	$this->db->where('transfer_mst.status', 'Deactive');
	$this->db->where('(order_mst.ostatus IS NULL OR order_mst.ostatus != 1)', NULL, FALSE);
	$this->db->where(
		'transfer_mst.trnno = (SELECT MAX(t2.trnno) FROM transfer_mst t2 WHERE t2.order_id = transfer_mst.order_id AND t2.sr = transfer_mst.sr AND t2.dept = \'STIT\' AND t2.status = \'Deactive\')',
		NULL,
		FALSE
	);
	if ($this->_has_order_status_detail_filters($filters))
	{
		$this->db->join('order_dtl', 'order_dtl.order_trnno = transfer_mst.order_id AND order_dtl.trnno = transfer_mst.sr', 'left');
		$this->db->join('measurement', 'measurement.code = order_mst.naap_no', 'left');
		$this->db->join('customer', 'customer.code = measurement.customer_code', 'left');
		$this->_apply_order_status_detail_filters($filters);
	}
	$this->db->group_by('order_mst.trnno');
	$this->db->order_by('RETURNDATE', 'ASC');
	$this->db->order_by('ORDERNO', 'ASC');
	$this->db->limit($order_limit);
	$id_q = $this->db->get();
	if ($id_q === FALSE || $id_q->num_rows() < 1)
	{
		return array();
	}

	$order_ids = array();
	foreach ($id_q->result() as $id_row)
	{
		$oid = (int) $id_row->ORDERNO;
		if ($oid > 0)
		{
			$order_ids[] = $oid;
		}
	}
	if (empty($order_ids))
	{
		return array();
	}

	// Phase 2: line details only for the capped order set.
	$this->db->select('order_mst.trnno AS ORDERNO, transfer_mst.trnno AS TRANSFERID, transfer_mst.sr AS ORDERSR, order_dtl.type AS line_type, '.$this->_order_customer_snap_select_sql().', order_mst.total, order_mst.advance, customer.Phone, order_mst.RDATE AS RETURNDATE');
	$this->db->from('transfer_mst');
	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
	$this->db->join('order_dtl', 'order_dtl.order_trnno = transfer_mst.order_id AND order_dtl.trnno = transfer_mst.sr', 'left');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where('transfer_mst.dept', 'STIT');
	$this->db->where('transfer_mst.status', 'Deactive');
	$this->db->where_in('order_mst.trnno', $order_ids);
	$this->db->where(
		'transfer_mst.trnno = (SELECT MAX(t2.trnno) FROM transfer_mst t2 WHERE t2.order_id = transfer_mst.order_id AND t2.sr = transfer_mst.sr AND t2.dept = \'STIT\' AND t2.status = \'Deactive\')',
		NULL,
		FALSE
	);
	$this->db->order_by('order_mst.RDATE', 'ASC');
	$this->db->order_by('order_mst.trnno', 'ASC');
	$this->db->order_by('transfer_mst.sr', 'ASC');
	$q = $this->db->get();

	if ($q === FALSE || $q->num_rows() < 1)
	{
		return array();
	}
	$lines = array();
	foreach ($q->result() as $row)
	{
		$lines[] = $row;
	}
	return $this->_aggregate_ready_deliver_by_order($lines, $order_limit);
  }

  /**
   * Send a ready-for-delivery line back to stitching (STIT Active).
   */
  function return_ready_line_to_stitching($transfer_id)
  {
	$transfer_id = (int) $transfer_id;
	if ($transfer_id <= 0)
	{
		return FALSE;
	}

	$this->db->select('trnno, order_id, sr, dept, status');
	$this->db->from('transfer_mst');
	$this->db->where('trnno', $transfer_id);
	$this->db->where('dept', 'STIT');
	$this->db->where('status', 'Deactive');
	$q = $this->db->get();
	if ($q->num_rows() < 1)
	{
		return FALSE;
	}
	$row = $q->row();

	$this->db->where('trnno', (int) $row->trnno);
	$this->db->where('order_id', (int) $row->order_id);
	$this->db->where('sr', (int) $row->sr);
	$this->db->where('dept', 'STIT');
	$this->db->where('status', 'Deactive');
	$this->db->limit(1);
	$this->db->update('transfer_mst', array('status' => 'Active'));

	return $this->db->affected_rows() === 1;
  }

  /**
   * Send all ready-for-delivery lines of an order back to stitching.
   */
  function return_ready_order_to_stitching($order_id)
  {
	$order_id = (int) $order_id;
	if ($order_id < 1)
	{
		return 0;
	}

	$this->db->select('transfer_mst.trnno');
	$this->db->from('transfer_mst');
	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
	$this->db->where('transfer_mst.order_id', $order_id);
	$this->db->where('transfer_mst.dept', 'STIT');
	$this->db->where('transfer_mst.status', 'Deactive');
	$this->db->where('(order_mst.ostatus IS NULL OR order_mst.ostatus != 1)', NULL, FALSE);
	$this->db->where(
		'transfer_mst.trnno = (SELECT MAX(t2.trnno) FROM transfer_mst t2 WHERE t2.order_id = transfer_mst.order_id AND t2.sr = transfer_mst.sr AND t2.dept = \'STIT\' AND t2.status = \'Deactive\')',
		NULL,
		FALSE
	);
	$q = $this->db->get();
	if ($q->num_rows() < 1)
	{
		return 0;
	}

	$updated = 0;
	foreach ($q->result() as $row)
	{
		if ($this->return_ready_line_to_stitching((int) $row->trnno))
		{
			$updated++;
		}
	}
	return $updated;
  }

  function get_order_det_single($where)
  {
	 $this->db->distinct();
    $this->db->select('*,order_dtl.order_remarks AS ORDREMARKS,order_dtl.trnno ORDERSR,customer.code AS CUSTOMERCODE ');
	$this->db->from('transfer_mst');
 	$this->db->join('v_order_mst as order_mst', 'transfer_mst.order_id = order_mst.trnno');
 	$this->db->join('order_dtl', 'order_dtl.trnno = transfer_mst.sr');
 	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
 	$this->db->join('customer', 'customer.code = measurement.customer_code');	
	 $this->db->where('transfer_mst.dept = "STIT" and status = "Deactive" and order_mst.trnno = "'.$where.'"');
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }
  }
  function get_recevied($where)
  {
	//$this->db->distinct();
    $this->db->select('SUM(PAID) AS TOTRECEVIED');
	$this->db->from('dmst');
	 $this->db->where('dmst.order_id = "'.$where.'"');
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row->TOTRECEVIED;
      }
	
      return $row->TOTRECEVIED;
    }
  }
  
   function get_recevied_cash($where)
  {
	//$this->db->distinct();
    $this->db->select('SUM(amount) AS TOTCASHRECEVIED');
	$this->db->from('cash');
	 $this->db->where('cash.order_no = "'.$where.'"');
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row->TOTCASHRECEVIED;
      }
	
      return $row->TOTCASHRECEVIED;
    }
  }
  
    function total_wages($where)
  {
	//$this->db->distinct();
    $this->db->select('sum( price ) AS TOTPRICE');
	$this->db->from('employee');
 	$this->db->join('transfer_mst', 'employee.empno = transfer_mst.empno');
 /*	$this->db->join('order_dtl', 'order_dtl.trnno = transfer_mst.sr');
 	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
 	$this->db->join('customer', 'customer.code = measurement.customer_code');	
	 $this->db->where('transfer_mst.dept = "STIT" and order_mst.trnno = "'.$where.'"'); */
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }
  }
  
  function measurement_snap_field_names()
  {
    return array(
      'KL', 'FPL', 'BL', 'Teera', 'BP', 'CL', 'Chaati', 'Gala', 'Poncha_Choraic', 'Poncha_Chorai',
      'Shalwar_Lenght', 'Asan', 'Side_Pocket_Size', 'SC', 'BG', 'Lambai', 'Poncha', 'shalwar_Asan',
      'Peeir', 'PLambai', 'PPoncha', 'PAsan', 'PPeeir', 'PentLambai', 'pentWaiste', 'HIP', 'PentPoncha',
      'PentThai', 'SLambai', 'SBazoo', 'STeera', 'SGeera', 'geera', 'SChaati', 'SKamar', 'SGaala',
      'CoatLambai', 'CoatBazoo', 'CoatTeera', 'CoatChaati', 'CoatHIP', 'CoatKamar', 'crossback',
      'wastLambai', 'wastTeera', 'wastChaati', 'wastHIP', 'wastKamar', 'pait', 'WASIT',
      'FRONT_Pocket_Size', 'CAKLAMBAI', 'KAAFCURAI', 'MODA_CURAI', 'CoatGaala', 'wastGaala',
    );
  }

  function _measurement_snap_select_sql()
  {
    $parts = array();
    foreach ($this->measurement_snap_field_names() as $col)
    {
      $parts[] = 'COALESCE(NULLIF(TRIM(oms.`'.$col.'`), \'\'), measurement.`'.$col.'`) AS `'.$col.'`';
    }
    return implode(', ', $parts);
  }

  function _order_customer_name_sql($alias = 'name')
  {
    if ($this->_order_mst_has_column('snap_customer_name'))
    {
      return 'CASE
      WHEN order_mst.snap_customer_name IS NOT NULL
        AND TRIM(order_mst.snap_customer_name) != \'\'
        AND order_mst.snap_customer_name NOT REGEXP \'^[?[:space:]/]+$\'
      THEN order_mst.snap_customer_name
      ELSE customer.name
    END AS '.$alias;
    }
    return 'customer.name AS '.$alias;
  }

  function _order_customer_snap_select_sql()
  {
    return $this->_order_man_napno_sql_expr().' AS man_napno,
      '.$this->_order_customer_name_sql('name').',
      '.$this->_order_customer_code_sql_expr().' AS Customerno';
  }

  protected function _order_mst_has_column($column)
  {
    static $cache = array();
    $column = trim((string) $column);
    if ($column === '')
    {
      return FALSE;
    }
    if ( ! isset($cache[$column]))
    {
      $cache[$column] = $this->db->field_exists($column, 'order_mst');
    }
    return (bool) $cache[$column];
  }

  protected function _order_man_napno_sql_expr()
  {
    if ($this->_order_mst_has_column('snap_man_napno'))
    {
      return 'COALESCE(order_mst.snap_man_napno, customer.man_napno)';
    }
    return 'customer.man_napno';
  }

  protected function _order_customer_code_sql_expr()
  {
    if ($this->_order_mst_has_column('snap_customer_code'))
    {
      return 'COALESCE(order_mst.snap_customer_code, customer.code)';
    }
    return 'customer.code';
  }

  protected function _collect_query_int_column($q, $column)
  {
    $ids = array();
    if ($q === FALSE)
    {
      $err = $this->db->error();
      log_message('error', 'Order_query_model query failed: '.(isset($err['message']) ? $err['message'] : 'unknown'));
      return $ids;
    }
    foreach ($q->result() as $row)
    {
      if (isset($row->$column))
      {
        $ids[] = (int) $row->$column;
      }
    }
    return $ids;
  }

  protected function _query_result_rows($q)
  {
    if ($q === FALSE)
    {
      $err = $this->db->error();
      log_message('error', 'Order_query_model query failed: '.(isset($err['message']) ? $err['message'] : 'unknown'));
      return array();
    }
    if ($q->num_rows() <= 0)
    {
      return array();
    }
    return $q->result();
  }

  /**
   * Customer display name from measurement + customer join row.
   */
  function measurement_customer_name_from_row($row)
  {
    if (isset($row->customer_name) && trim((string) $row->customer_name) !== '')
    {
      return trim((string) $row->customer_name);
    }
    if (isset($row->name) && trim((string) $row->name) !== '' && ! preg_match('/^[?[:space:]\/]+$/u', (string) $row->name))
    {
      return trim((string) $row->name);
    }
    return '';
  }

  function get_suit_name_by_sno($sno)
  {
    $sno = (int) $sno;
    if ($sno <= 0)
    {
      return '';
    }
    $this->db->select('name');
    $this->db->from('suit');
    $this->db->where('SNO', $sno);
    $this->db->limit(1);
    $q = $this->db->get();
    return $q->num_rows() > 0 ? (string) $q->row()->name : '';
  }

  /**
   * Persist measurement row at booking (new orders only).
   */
  function insert_order_measurement_snap($order_trnno, $measurement_row)
  {
    $order_trnno = (int) $order_trnno;
    if ($order_trnno <= 0 || empty($measurement_row))
    {
      return FALSE;
    }

    $code = NULL;
    if (isset($measurement_row->code))
    {
      $code = (int) $measurement_row->code;
    }
    elseif (isset($measurement_row->MCODE))
    {
      $code = (int) $measurement_row->MCODE;
    }

    $cust = NULL;
    if (isset($measurement_row->customer_code))
    {
      $cust = (int) $measurement_row->customer_code;
    }
    elseif (isset($measurement_row->Customerno))
    {
      $cust = (int) $measurement_row->Customerno;
    }

    $data = array(
      'order_trnno' => $order_trnno,
      'snap_at' => date('Y-m-d H:i:s'),
      'measurement_code' => $code,
      'customer_code' => $cust,
    );

    foreach ($this->measurement_snap_field_names() as $field)
    {
      if (isset($measurement_row->$field))
      {
        $data[$field] = $measurement_row->$field;
      }
    }
    if ( ! isset($data['remarks']))
    {
      if (isset($measurement_row->remarks))
      {
        $data['remarks'] = $measurement_row->remarks;
      }
      elseif (isset($measurement_row->MREMARKS))
      {
        $data['remarks'] = $measurement_row->MREMARKS;
      }
    }

    return $this->db->insert('order_measurement_snap', $data);
  }

    function measurement($where)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
	$this->db->select('measurement.*, measurement.remarks AS MREMARKS, measurement.code AS MCODE, customer.code AS Customerno, customer.man_napno AS MANNAAPNO, customer.name AS name, customer.name AS customer_name', FALSE);
    $this->db->from('measurement');
	$this->db->join('customer', 'measurement.customer_code = customer.code');
	$this->db->where($where);
	//$this->db->where('measurement.code = 1');
	$q = $this->db->get();

	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }
    return array();
  }
  
 
      function get_order($where)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
	$meas_snap = $this->_measurement_snap_select_sql();
	$this->db->select('order_dtl.*, order_mst.*, customer.Phone, '.$meas_snap.', COALESCE(oms.remarks, measurement.remarks) AS MREMARKS, '.$this->_order_customer_snap_select_sql().', order_mst.EDATE AS ORDERDATE, order_mst.trnno AS TRNNO', FALSE);
    $this->db->from('order_mst');
	  $this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	//  $this->db->join('style', 'style.DNO = order_dtl.design');
	   $this->db->join('order_measurement_snap oms', 'oms.order_trnno = order_mst.trnno', 'left');
	   $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where($where);
	$this->db->order_by('order_dtl.trnno', 'ASC');
	 
	$q = $this->db->get();


    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }
  }

  function get_order_distinct_types($trnno)
  {
	$trnno = (int) $trnno;
	if ($trnno <= 0)
	{
		return array();
	}

	$this->db->distinct();
	$this->db->select('order_dtl.type');
	$this->db->from('order_dtl');
	$this->db->where('order_dtl.order_trnno', $trnno);
	$this->db->where('order_dtl.type !=', '');
	$this->db->order_by('order_dtl.type', 'ASC');
	$q = $this->db->get();
	if ($q->num_rows() < 1)
	{
		return array();
	}

	$types = array();
	foreach ($q->result() as $row)
	{
		$type = trim((string) $row->type);
		if ($type !== '')
		{
			$types[] = $type;
		}
	}

	return $types;
  }

  function get_order_header($trnno)
  {
	$trnno = (int) $trnno;
	if ($trnno <= 0)
	{
		return NULL;
	}

	$this->db->select('order_mst.trnno, order_mst.EDATE, order_mst.RDATE, order_mst.qty, order_mst.total, order_mst.advance, order_mst.Discount, order_mst.addprice, '.$this->_order_customer_snap_select_sql().', customer.Phone');
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where('order_mst.trnno', $trnno);
	$q = $this->db->get();
	if ($q->num_rows() < 1)
	{
		return NULL;
	}

	return $q->row();
  }
  
  function get_order_order_Detai3($where)
  {
	//$this->db->distinct();
    $this->db->select('*,order_dtl.order_remarks AS ORDREMARKS,order_dtl.trnno ORDERSR ,order_mst.trnno as ORDER,measurement.remarks AS MREMARKS');
	$this->db->from('transfer_mst');
 	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
 	$this->db->join('order_dtl', 'order_dtl.order_trnno = transfer_mst.order_id and order_dtl.trnno = transfer_mst.sr');
 	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
 	$this->db->join('customer', 'customer.code = measurement.customer_code');	
    $this->db->where($where);
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }
  }
  
  
     function get_booking_status_report($date, $date1)
  {
	$date = trim((string) $date);
	$date1 = trim((string) $date1);
	if ($date === '' || $date1 === '')
	{
		return array();
	}

	$this->db->select(
		'order_mst.ostatus,
		order_mst.trnno AS ORDERNO,
		order_mst.RDATE AS RORDERDATE,
		customer.Phone,
		customer.name,
		customer.man_napno,
		transfer_mst.dept,
		transfer_mst.status',
		FALSE
	);
	$this->db->from('order_mst');
	$this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->join('transfer_mst', 'transfer_mst.order_id = order_dtl.order_trnno AND transfer_mst.sr = order_dtl.trnno');
	$this->db->where('order_mst.RDATE >=', $date);
	$this->db->where('order_mst.RDATE <=', $date1);
	$this->db->group_start();
	$this->db->where('transfer_mst.status', 'Active');
	$this->db->or_group_start();
	$this->db->where('transfer_mst.dept', 'STIT');
	$this->db->where('transfer_mst.status', 'Deactive');
	$this->db->group_end();
	$this->db->group_end();
	$this->db->order_by('order_mst.RDATE', 'ASC');
	$this->db->order_by('order_mst.trnno', 'ASC');
	$q = $this->db->get();
	return $q->num_rows() > 0 ? $q->result() : array();
  }


     function get_order_order_Detai31($where)
  {
	// Legacy wrapper — parse RDATE range from raw WHERE when possible.
	if (preg_match('/order_mst\.RDATE\s+between\s+"([^"]+)"\s+and\s+"([^"]+)"/i', (string) $where, $m))
	{
		return $this->get_booking_status_report($m[1], $m[2]);
	}

	$this->db->select(
		'order_mst.ostatus,
		order_mst.trnno AS ORDERNO,
		order_mst.EDATE AS ORDERDATE,
		order_mst.RDATE AS RORDERDATE,
		order_dtl.trnno AS SUITNO,
		order_dtl.trnno AS ORDERSR,
		order_dtl.design AS styleno,
		transfer_mst.trnno AS TRANSFERID,
		transfer_mst.RDATE AS RETURNDATE,
		transfer_mst.EDATE AS TRANSFEREDATE,
		transfer_mst.empno AS employee,
		transfer_mst.dept,
		transfer_mst.status,
		measurement.remarks AS MREMARKS,
		customer.Phone,
		customer.name,
		customer.man_napno',
		FALSE
	);
    $this->db->from('order_mst');
	  $this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	   $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->join('transfer_mst', 'transfer_mst.order_id = order_dtl.order_trnno AND transfer_mst.sr = order_dtl.trnno');
	 if ($where !== '')
	 {
	 $this->db->where($where, NULL, FALSE);
	 }
	 $this->db->order_by('order_mst.RDATE', 'ASC')->order_by('order_mst.trnno', 'ASC');
	$q = $this->db->get();
	return $q->num_rows() > 0 ? $q->result() : array();
  }
  
  
  
     function get_order_order_Detai2($where, $transfer_dept = '')
  {
    /* all the queries relating to the data we want to retrieve will go in here. */

    //$this->db->where($where_arr);
	$this->db->select(
		'order_mst.trnno AS ORDERNO,
		order_mst.EDATE AS ORDERDATE,
		order_mst.RDATE AS RORDERDATE,
		order_dtl.trnno AS SUITNO,
		order_dtl.trnno AS ORDERSR,
		order_dtl.design AS styleno,
		order_dtl.type,
		order_dtl.SNO,
		order_dtl.fpocket,
		order_dtl.spocket,
		order_dtl.Shawlarpocket,
		order_dtl.kolor_style,
		order_dtl.order_remarks,
		COALESCE(order_dtl.snap_suit_name, suit.name) AS suit_name,
		transfer_mst.trnno AS TRANSFERID,
		transfer_mst.RDATE AS RETURNDATE,
		transfer_mst.EDATE AS TRANSFEREDATE,
		transfer_mst.empno,
		transfer_mst.dept,
		transfer_mst.status,
		measurement.remarks AS MREMARKS,
		customer.Phone,
		customer.name,
		customer.man_napno',
		FALSE
	);
    $this->db->from('order_mst');
	  $this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	$this->db->join('suit', 'suit.SNO = order_dtl.SNO', 'left');
	   $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$transfer_join = 'transfer_mst.order_id = order_dtl.order_trnno AND transfer_mst.sr = order_dtl.trnno';
	$transfer_join_type = 'left';
	if ($transfer_dept !== '')
	{
		$transfer_join .= ' AND transfer_mst.dept = '.$this->db->escape($transfer_dept).' AND transfer_mst.status = "Active"';
		$transfer_join_type = 'inner';
	}
	$this->db->join('transfer_mst', $transfer_join, $transfer_join_type, FALSE);
	if ($transfer_dept !== '')
	{
		$this->db->where('(order_mst.ostatus IS NULL OR order_mst.ostatus != 1)', NULL, FALSE);
	}
	 if ($where !== '')
	 {
	 $this->db->where($where, NULL, FALSE);
	 }
	 $this->db->order_by('order_mst.trnno', 'DESC');
	 // Hard cap when not using paged helper (legacy callers / single-row lookups).
	 if ($where === '')
	 {
		 $this->db->limit(500);
	 }
	 else
	 {
		 $this->db->limit(50);
	 }
	 
	$q = $this->db->get();


    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
		
      return $data;
    }
  }

  /**
   * Build pending-transfer query base (Active lines for a workflow dept).
   *
   * @param string $transfer_dept HO|CUT|HO2|STIT
   * @param string $search
   */
  protected function _pending_transfer_base_query($transfer_dept, $search = '')
  {
	$transfer_dept = strtoupper(trim((string) $transfer_dept));
	$this->db->from('order_mst');
	$this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	$this->db->join('suit', 'suit.SNO = order_dtl.SNO', 'left');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->join(
		'transfer_mst',
		'transfer_mst.order_id = order_dtl.order_trnno AND transfer_mst.sr = order_dtl.trnno'
			.' AND transfer_mst.dept = '.$this->db->escape($transfer_dept)
			.' AND transfer_mst.status = "Active"',
		'inner',
		FALSE
	);
	$this->db->where('(order_mst.ostatus IS NULL OR order_mst.ostatus != 1)', NULL, FALSE);

	$search = trim((string) $search);
	if ($search !== '')
	{
		$this->db->group_start();
		if (ctype_digit($search))
		{
			$this->db->or_where('order_mst.trnno', (int) $search);
			$this->db->or_where('transfer_mst.trnno', (int) $search);
		}
		$this->db->or_like('customer.name', $search);
		$this->db->or_like('customer.Phone', $search);
		$this->db->or_like('customer.man_napno', $search);
		$this->db->or_like('order_dtl.order_remarks', $search);
		$this->db->group_end();
	}
  }

  /**
   * @return int
   */
  function count_pending_transfer_rows($transfer_dept, $search = '')
  {
	$transfer_dept = strtoupper(trim((string) $transfer_dept));
	if (!in_array($transfer_dept, array('HO', 'CUT', 'HO2', 'STIT'), TRUE))
	{
		return 0;
	}
	$this->db->select('COUNT(*) AS total', FALSE);
	$this->_pending_transfer_base_query($transfer_dept, $search);
	$row = $this->db->get()->row();
	return $row ? (int) $row->total : 0;
  }

  /**
   * Server-side page of pending transfer lines.
   *
   * @return array
   */
  function get_pending_transfer_rows($transfer_dept, $start = 0, $length = 25, $search = '')
  {
	$transfer_dept = strtoupper(trim((string) $transfer_dept));
	if (!in_array($transfer_dept, array('HO', 'CUT', 'HO2', 'STIT'), TRUE))
	{
		return array();
	}
	$start = max(0, (int) $start);
	$length = (int) $length;
	if ($length < 1)
	{
		$length = 25;
	}
	if ($length > 100)
	{
		$length = 100;
	}

	$this->db->select(
		'order_mst.trnno AS ORDERNO,
		order_mst.EDATE AS ORDERDATE,
		order_mst.RDATE AS RORDERDATE,
		order_dtl.trnno AS SUITNO,
		order_dtl.trnno AS ORDERSR,
		order_dtl.order_remarks,
		transfer_mst.trnno AS TRANSFERID,
		transfer_mst.RDATE AS RETURNDATE,
		transfer_mst.EDATE AS TRANSFEREDATE,
		transfer_mst.empno,
		customer.Phone,
		customer.name,
		customer.man_napno',
		FALSE
	);
	$this->_pending_transfer_base_query($transfer_dept, $search);
	$this->db->order_by('order_mst.trnno', 'DESC');
	$this->db->order_by('order_dtl.trnno', 'ASC');
	$this->db->limit($length, $start);
	$q = $this->db->get();
	return ($q !== FALSE && $q->num_rows() > 0) ? $q->result() : array();
  }
  
  
  protected function _order_status_search_order_ids($cust_search, $limit = 1000, $fromdate = '', $todate = '')
  {
    $cust_search = trim((string) $cust_search);
    if ($cust_search === '')
    {
      return array();
    }

    $ids = array();
    $this->db->distinct();
    $this->db->select('order_mst.trnno AS ORDERNO');
    $this->db->from('customer');
    $this->db->join('measurement', 'measurement.customer_code = customer.code');
    $this->db->join('order_mst', 'order_mst.naap_no = measurement.code');
    $this->_apply_customer_search($cust_search);
    if (trim((string) $fromdate) !== '')
    {
      $this->db->where('order_mst.RDATE >=', $fromdate);
    }
    if (trim((string) $todate) !== '')
    {
      $this->db->where('order_mst.RDATE <=', $todate);
    }
    $this->db->order_by('order_mst.trnno', 'DESC');
    $this->db->limit((int) $limit);
    $ids = array_merge($ids, $this->_collect_query_int_column($this->db->get(), 'ORDERNO'));

    $digits = preg_replace('/\D+/', '', $cust_search);
    if ($digits !== '' && ctype_digit($digits))
    {
      $this->db->select('trnno');
      $this->db->from('order_mst');
      $this->db->where('trnno', (int) $digits);
      $row_q = $this->db->get();
      if ($row_q !== FALSE)
      {
        $row = $row_q->row();
        if ($row)
        {
          $ids[] = (int) $row->trnno;
        }
      }
    }

    return array_values(array_unique(array_filter($ids)));
  }

  /**
   * Resolve order IDs for order-status naap search using indexed columns (avoids COALESCE in heavy joins).
   */
  protected function _order_status_naap_order_ids($naap_no, $limit = 1000)
  {
    $naap_no = trim((string) $naap_no);
    if ($naap_no === '')
    {
      return array();
    }

    $ids = array();
    $esc = $this->db->escape_like_str($naap_no);
    $limit = max(1, (int) $limit);

    if ($this->_order_mst_has_column('snap_man_napno'))
    {
      $this->db->select('trnno');
      $this->db->from('order_mst');
      $this->db->group_start();
      $this->db->where('snap_man_napno', $naap_no);
      $this->db->or_like('snap_man_napno', $esc, 'after');
      $this->db->group_end();
      $this->db->limit($limit);
      $ids = array_merge($ids, $this->_collect_query_int_column($this->db->get(), 'trnno'));
    }

    $this->db->distinct();
    $this->db->select('order_mst.trnno');
    $this->db->from('customer');
    $this->db->join('measurement', 'measurement.customer_code = customer.code');
    $this->db->join('order_mst', 'order_mst.naap_no = measurement.code');
    $this->db->group_start();
    $this->db->where('customer.man_napno', $naap_no);
    $this->db->or_like('customer.man_napno', $esc, 'after');
    $this->db->group_end();
    $this->db->limit($limit);
    $ids = array_merge($ids, $this->_collect_query_int_column($this->db->get(), 'trnno'));

    return array_values(array_unique(array_filter($ids)));
  }

  protected function _select_order_status_fields()
  {
    $this->db->select('
      order_mst.ostatus,
      order_mst.trnno AS ORDERNO,
      order_mst.RDATE AS ORDERRDATE,
      order_mst.EDATE AS ORDERDATE,
      order_mst.trnno AS TRNNO,
      order_dtl.trnno AS SUITNO,
      order_dtl.trnno AS ORDERSR,
      order_dtl.design AS styleno,
      order_dtl.order_remarks,
      transfer_mst.trnno AS TRANSFERID,
      transfer_mst.RDATE AS RETURNDATE,
      transfer_mst.EDATE AS TRANSFEREDATE,
      transfer_mst.empno,
      transfer_mst.dept,
      transfer_mst.status,
      measurement.remarks AS MREMARKS,
      customer.Phone,
      '.$this->_order_customer_name_sql('name').',
      '.$this->_order_man_napno_sql_expr().' AS man_napno
    ', FALSE);
  }

  protected function _where_expr_equals_or_like_prefix($expr, $value)
  {
    $value = trim((string) $value);
    if ($value === '')
    {
      return;
    }
    $exact = $this->db->escape($value);
    $like = $this->db->escape($this->db->escape_like_str($value).'%');
    $this->db->where('('.$expr.' = '.$exact.' OR '.$expr.' LIKE '.$like.')', NULL, FALSE);
  }

  /**
   * Naap filter for queries that already join customer (must not run separate get() on $this->db).
   */
  protected function _where_order_naap_filter($naap_no)
  {
    $naap_no = trim((string) $naap_no);
    if ($naap_no === '')
    {
      return;
    }
    $exact = $this->db->escape($naap_no);
    $like = $this->db->escape($this->db->escape_like_str($naap_no).'%');
    $this->db->group_start();
    if ($this->_order_mst_has_column('snap_man_napno'))
    {
      $this->db->where('(order_mst.snap_man_napno = '.$exact.' OR order_mst.snap_man_napno LIKE '.$like.')', NULL, FALSE);
      $this->db->or_where('(customer.man_napno = '.$exact.' OR customer.man_napno LIKE '.$like.')', NULL, FALSE);
    }
    else
    {
      $this->db->where('(customer.man_napno = '.$exact.' OR customer.man_napno LIKE '.$like.')', NULL, FALSE);
    }
    $this->db->group_end();
  }

  protected function _normalize_order_status_filters($filters, $date_from = '', $date_to = '', $cust_search = '')
  {
    $defaults = array(
      'customer_name' => '',
      'order_no' => '',
      'naap_no' => '',
      'mobile_no' => '',
      'return_from' => '',
      'return_to' => '',
      'order_from' => '',
      'order_to' => '',
      'stage' => '',
    );

    if (is_array($filters))
    {
      $filters = array_merge($defaults, $filters);
    }
    else
    {
      $legacy_date_from = (string) $filters;
      $filters = $defaults;
      $filters['customer_name'] = (string) $cust_search;
      $filters['order_from'] = $legacy_date_from !== '' ? $legacy_date_from : (string) $date_from;
      $filters['order_to'] = (string) $date_to;
      $filters['return_from'] = $legacy_date_from !== '' ? $legacy_date_from : (string) $date_from;
      $filters['return_to'] = (string) $date_to;
    }

    foreach ($filters as $key => $value)
    {
      $filters[$key] = trim((string) $value);
    }

    return $filters;
  }

  protected function _has_order_status_detail_filters($filters)
  {
    foreach (array('customer_name', 'order_no', 'naap_no', 'mobile_no', 'return_from', 'return_to', 'order_from', 'order_to') as $key)
    {
      if (isset($filters[$key]) && trim((string) $filters[$key]) !== '')
      {
        return TRUE;
      }
    }
    return FALSE;
  }

  public function has_order_status_detail_filters($filters)
  {
    return $this->_has_order_status_detail_filters($filters);
  }

  public function apply_default_order_date_filters($filters, $days = 30)
  {
    if ( ! is_array($filters))
    {
      $filters = array();
    }
    $days = (int) $days;
    if ($days < 1)
    {
      $days = 30;
    }
    if ($days > 120)
    {
      $days = 120;
    }

    // Specific lookups (order # / naap / mobile) do not need a forced date window.
    $has_lookup = FALSE;
    foreach (array('order_no', 'naap_no', 'mobile_no') as $key)
    {
      if (isset($filters[$key]) && trim((string) $filters[$key]) !== '')
      {
        $has_lookup = TRUE;
        break;
      }
    }
    if ($has_lookup)
    {
      return $filters;
    }

    $has_any_date = FALSE;
    foreach (array('return_from', 'return_to', 'order_from', 'order_to') as $key)
    {
      if (isset($filters[$key]) && trim((string) $filters[$key]) !== '')
      {
        $has_any_date = TRUE;
        break;
      }
    }
    if ($has_any_date)
    {
      // Cap wide ranges to protect Order Status / Ready screens.
      $filters = $this->_cap_date_range_pair($filters, 'return_from', 'return_to', $days);
      $filters = $this->_cap_date_range_pair($filters, 'order_from', 'order_to', $days);
      return $filters;
    }

    $to = date('Y-m-d');
    $from = date('Y-m-d', strtotime('-'.$days.' days'));
    // Current tab filters by return date; delivered tab uses order dates.
    if ( ! isset($filters['return_from']) || trim((string) $filters['return_from']) === '')
    {
      $filters['return_from'] = $from;
    }
    if ( ! isset($filters['return_to']) || trim((string) $filters['return_to']) === '')
    {
      $filters['return_to'] = $to;
    }
    if ( ! isset($filters['order_from']) || trim((string) $filters['order_from']) === '')
    {
      $filters['order_from'] = $from;
    }
    if ( ! isset($filters['order_to']) || trim((string) $filters['order_to']) === '')
    {
      $filters['order_to'] = $to;
    }
    return $filters;
  }

  protected function _cap_date_range_pair(array $filters, $from_key, $to_key, $max_days)
  {
    $from = isset($filters[$from_key]) ? trim((string) $filters[$from_key]) : '';
    $to = isset($filters[$to_key]) ? trim((string) $filters[$to_key]) : '';
    if ($from === '' || $to === '')
    {
      return $filters;
    }
    $from_ts = strtotime($from);
    $to_ts = strtotime($to);
    if ($from_ts === FALSE || $to_ts === FALSE || $to_ts < $from_ts)
    {
      return $filters;
    }
    $span = (int) floor(($to_ts - $from_ts) / 86400);
    if ($span > (int) $max_days)
    {
      $filters[$from_key] = date('Y-m-d', $to_ts - ((int) $max_days * 86400));
    }
    return $filters;
  }

  protected function _apply_order_status_detail_filters($filters)
  {
    if ($filters['order_no'] !== '')
    {
      $digits = preg_replace('/\D+/', '', $filters['order_no']);
      if ($digits !== '' && ctype_digit($digits))
      {
        $this->db->where('order_mst.trnno', (int) $digits);
      }
      else
      {
        $this->db->where('order_mst.trnno', -1);
      }
    }

    if ($filters['naap_no'] !== '')
    {
      $this->_where_order_naap_filter($filters['naap_no']);
    }

    if ($filters['mobile_no'] !== '')
    {
      $digits = preg_replace('/\D+/', '', $filters['mobile_no']);
      $esc = $this->db->escape_like_str($filters['mobile_no']);
      $this->db->group_start();
      $this->db->where('customer.Phone', $filters['mobile_no']);
      if ($digits !== '')
      {
        $this->db->or_where('customer.Phone', $digits);
      }
      $this->db->or_like('customer.Phone', $esc, 'after');
      $this->db->group_end();
    }

    if ($filters['customer_name'] !== '')
    {
      $esc = $this->db->escape_like_str($filters['customer_name']);
      $this->db->group_start();
      $this->db->where('customer.name', $filters['customer_name']);
      $this->db->or_like('customer.name', $esc, 'after');
      $this->db->group_end();
    }

    if ($filters['return_from'] !== '')
    {
      $this->db->where('order_mst.RDATE >=', $filters['return_from']);
    }
    if ($filters['return_to'] !== '')
    {
      $this->db->where('order_mst.RDATE <=', $filters['return_to']);
    }
    if ($filters['order_from'] !== '')
    {
      $this->db->where('order_mst.EDATE >=', $filters['order_from']);
    }
    if ($filters['order_to'] !== '')
    {
      $this->db->where('order_mst.EDATE <=', $filters['order_to']);
    }
  }

    function order_Status_D($filters = array(), $edate = '', $cust_search = '')
  {
	 $filters = $this->_normalize_order_status_filters($filters, '', $edate, $cust_search);
	 if ( ! $this->_has_order_status_detail_filters($filters))
	 {
		 return array();
	 }

	$this->_select_order_status_fields();
    $this->db->from('order_mst');
	$this->db->join('transfer_mst', 'transfer_mst.order_id = order_mst.trnno');
	$this->db->join('order_dtl', 'order_dtl.order_trnno = transfer_mst.order_id and order_dtl.trnno = transfer_mst.sr');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where('transfer_mst.status', 'DELIVER');
	$this->db->where('transfer_mst.dept', 'STIT');
	$this->db->where('order_mst.ostatus', 0);
	$this->_apply_order_status_detail_filters($filters);
	$this->db->order_by('order_mst.RDATE', 'ASC');
	$this->db->order_by('order_mst.trnno', 'ASC');
	$this->db->limit(200);
	 
	$q = $this->db->get();
	$data = array();
	foreach ($this->_query_result_rows($q) as $row)
	{
		$data[] = $row;
	}
	return $data;
  }
  
  
   function order_Status($filters = array(), $fromdate = '', $todate = '', $stage = '')
  {
	$filters = $this->_normalize_order_status_filters($filters, $fromdate, $todate, '');
	if ($stage !== '')
	{
		$filters['stage'] = trim((string) $stage);
	}
	// Stage (incl. All) loads without requiring dates/search — optional filters only.

	$this->_select_order_status_fields();
    $this->db->from('order_mst');
	$this->db->join('transfer_mst', 'transfer_mst.order_id = order_mst.trnno');
	$this->db->join('order_dtl', 'order_dtl.order_trnno = transfer_mst.order_id and order_dtl.trnno = transfer_mst.sr');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	// Current status only: latest transfer row per suit line.
	$this->db->where(
		'transfer_mst.trnno = (SELECT MAX(t2.trnno) FROM transfer_mst t2 WHERE t2.order_id = transfer_mst.order_id AND t2.sr = transfer_mst.sr)',
		NULL,
		FALSE
	);
	$this->_apply_order_status_detail_filters($filters);
	$this->db->where('order_mst.ostatus', 0);
	if ($filters['stage'] === 'HO' || $filters['stage'] === 'CUT' || $filters['stage'] === 'HO2' || $filters['stage'] === 'STIT')
	{
		$this->db->where('transfer_mst.dept', $filters['stage']);
		$this->db->where('transfer_mst.status', 'Active');
	}
	elseif ($filters['stage'] === 'READY')
	{
		$this->db->where('transfer_mst.dept', 'STIT');
		$this->db->where('transfer_mst.status', 'Deactive');
	}
	elseif ($filters['stage'] === 'DELIVERED')
	{
		$this->db->where('transfer_mst.dept', 'STIT');
		$this->db->where('transfer_mst.status', 'DELIVER');
	}
	else
	{
		// All: current Active stages + Ready + Delivered.
		$this->db->group_start();
		$this->db->where('transfer_mst.status', 'Active');
		$this->db->or_group_start();
		$this->db->where('transfer_mst.dept', 'STIT');
		$this->db->where_in('transfer_mst.status', array('Deactive', 'DELIVER'));
		$this->db->group_end();
		$this->db->group_end();
	}
	$this->db->order_by('order_mst.RDATE', 'ASC');
	$this->db->order_by('order_mst.trnno', 'ASC');
	$this->db->limit(200);
	 
	$q = $this->db->get();
	$data = array();
	foreach ($this->_query_result_rows($q) as $row)
	{
		$data[] = $row;
	}
	return $data;
  }
  
  protected function _normalize_order_detail_filters($filters, $date_from = '', $date_to = '', $cust_search = '')
  {
    $defaults = array(
      'customer_name' => '',
      'order_no' => '',
      'naap_no' => '',
      'mobile_no' => '',
      'return_from' => '',
      'return_to' => '',
      'order_from' => '',
      'order_to' => '',
      'row_limit' => '500',
    );

    if (is_array($filters))
    {
      $filters = array_merge($defaults, $filters);
    }
    else
    {
      $legacy_date_from = (string) $filters;
      $filters = $defaults;
      $filters['customer_name'] = (string) $cust_search;
      $filters['order_from'] = $legacy_date_from !== '' ? $legacy_date_from : (string) $date_from;
      $filters['order_to'] = (string) $date_to;
    }

    foreach ($filters as $key => $value)
    {
      $filters[$key] = trim((string) $value);
    }

    return $filters;
  }

  protected function _has_order_detail_filters($filters)
  {
    foreach (array('customer_name', 'order_no', 'naap_no', 'mobile_no', 'return_from', 'return_to', 'order_from', 'order_to') as $key)
    {
      if (isset($filters[$key]) && trim((string) $filters[$key]) !== '')
      {
        return TRUE;
      }
    }
    return FALSE;
  }

  protected function _order_detail_row_limit($filters)
  {
    $limit = 500;
    if (isset($filters['row_limit']) && $filters['row_limit'] !== '')
    {
      $limit = (int) $filters['row_limit'];
    }
    if ($limit < 1)
    {
      $limit = 1;
    }
    if ($limit > 10000)
    {
      $limit = 10000;
    }
    return $limit;
  }

  protected function _apply_order_detail_filters($filters)
  {
    if ($filters['order_no'] !== '')
    {
      $digits = preg_replace('/\D+/', '', $filters['order_no']);
      $this->db->where('order_mst.trnno', ($digits !== '' && ctype_digit($digits)) ? (int) $digits : -1);
    }

    if ($filters['naap_no'] !== '')
    {
      $this->_where_order_naap_filter($filters['naap_no']);
    }

    if ($filters['mobile_no'] !== '')
    {
      $digits = preg_replace('/\D+/', '', $filters['mobile_no']);
      $esc = $this->db->escape_like_str($filters['mobile_no']);
      $this->db->group_start();
      $this->db->where('customer.Phone', $filters['mobile_no']);
      if ($digits !== '')
      {
        $this->db->or_where('customer.Phone', $digits);
      }
      $this->db->or_like('customer.Phone', $esc, 'after');
      $this->db->group_end();
    }

    if ($filters['customer_name'] !== '')
    {
      $name_expr = $this->_order_mst_has_column('snap_customer_name')
        ? 'COALESCE(order_mst.snap_customer_name, customer.name)'
        : 'customer.name';
      $this->_where_expr_equals_or_like_prefix($name_expr, $filters['customer_name']);
    }

    if ($filters['return_from'] !== '')
    {
      $this->db->where('order_mst.RDATE >=', $filters['return_from']);
    }
    if ($filters['return_to'] !== '')
    {
      $this->db->where('order_mst.RDATE <=', $filters['return_to']);
    }
    if ($filters['order_from'] !== '')
    {
      $this->db->where('order_mst.EDATE >=', $filters['order_from']);
    }
    if ($filters['order_to'] !== '')
    {
      $this->db->where('order_mst.EDATE <=', $filters['order_to']);
    }
  }

   function get_order_order_Detail($filters = array(), $edate = '', $cust_search = '')
  {
	 $filters = $this->_normalize_order_detail_filters($filters, '', $edate, $cust_search);
	 if ( ! $this->_has_order_detail_filters($filters))
	 {
		 return array();
	 }
 /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
/*	$this->db->select('*,order_mst.trnno AS ORDERNO,order_mst.EDATE AS ORDERDATE');
    $this->db->from('v_order_mst as order_mst');
	  $this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	// $this->db->join('style', 'style.DNO = order_dtl.kolor_style');
	   $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->group_by('order_mst.trnno');
	 $this->db->where('order_mst.ostatus != 1');
	 
	 */
	 //$this->db->select('*,  (select dept from transfer_mst where transfer_mst.order_id = ORDERNO and transfer_mst.dept != "HO" ORDER BY `status` ASC LIMIT 1 ) as status');
	 $this->db->select('order_mst.trnno AS ORDERNO, order_mst.EDATE AS ORDERDATE, order_mst.RDATE AS RDATE, '.$this->_order_customer_snap_select_sql().', customer.Phone');
    $this->db->from('order_mst');
    $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
    $this->db->join('customer', 'customer.code = measurement.customer_code');
    $this->db->where('order_mst.ostatus !=', 1);
	$this->_apply_order_detail_filters($filters);
	$this->db->order_by('order_mst.trnno', 'DESC');
	$this->db->limit($this->_order_detail_row_limit($filters));
	$q = $this->db->get();
	 


    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }
	return array();
  }

  /**
   * Per-line lifecycle timeline for an order (booking, cutting, HO2, stitching, delivery).
   */
  function get_order_lifecycle_history($order_id)
  {
	$order_id = (int) $order_id;
	if ($order_id <= 0)
	{
		return array('order' => NULL, 'lines' => array());
	}

	$this->db->select('order_mst.trnno, order_mst.EDATE AS order_date, order_mst.RDATE AS return_date, '.$this->_order_customer_name_sql('customer_name').', '.$this->_order_man_napno_sql_expr().' AS man_napno', FALSE);
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where('order_mst.trnno', $order_id);
	$order_q = $this->db->get();
	$order_header = $order_q->num_rows() > 0 ? $order_q->row() : NULL;

	$this->db->select('order_dtl.trnno AS line_sr, order_dtl.type, order_dtl.order_remarks, COALESCE(order_dtl.snap_suit_name, suit.name) AS suit_name');
	$this->db->from('order_dtl');
	$this->db->join('suit', 'suit.SNO = order_dtl.SNO', 'left');
	$this->db->where('order_dtl.order_trnno', $order_id);
	$this->db->order_by('order_dtl.trnno', 'ASC');
	$lines_q = $this->db->get();

	$lines_by_sr = array();
	$line_index = 0;
	if ($lines_q->num_rows() > 0)
	{
		foreach ($lines_q->result() as $line_row)
		{
			$line_index++;
			$sr = (int) $line_row->line_sr;
			$lines_by_sr[$sr] = array(
				'line_no' => $line_index,
				'line_sr' => $sr,
				'type' => (string) $line_row->type,
				'remarks' => (string) $line_row->order_remarks,
				'suit_name' => (string) $line_row->suit_name,
				'events' => array(),
			);
		}
	}

	$this->db->select('transfer_mst.trnno, transfer_mst.EDATE, transfer_mst.dept, transfer_mst.status, transfer_mst.sr, transfer_mst.empno, employee.name AS employee_name');
	$this->db->from('transfer_mst');
	$this->db->join('employee', 'employee.empno = transfer_mst.empno', 'left');
	$this->db->where('transfer_mst.order_id', $order_id);
	$this->db->order_by('transfer_mst.sr', 'ASC');
	$this->db->order_by('transfer_mst.trnno', 'ASC');
	$transfer_q = $this->db->get();

	if ($transfer_q->num_rows() > 0)
	{
		foreach ($transfer_q->result() as $transfer_row)
		{
			$sr = (int) $transfer_row->sr;
			if ( ! isset($lines_by_sr[$sr]))
			{
				$lines_by_sr[$sr] = array(
					'line_no' => count($lines_by_sr) + 1,
					'line_sr' => $sr,
					'type' => '',
					'remarks' => '',
					'suit_name' => '',
					'events' => array(),
				);
			}
			$event_meta = $this->_order_lifecycle_event_meta($transfer_row->dept, $transfer_row->status);
			if ($event_meta === NULL)
			{
				continue;
			}
			$lines_by_sr[$sr]['events'][] = array(
				'date' => (string) $transfer_row->EDATE,
				'event_key' => $event_meta['key'],
				'employee' => (string) $transfer_row->employee_name,
				'dept' => (string) $transfer_row->dept,
				'status' => (string) $transfer_row->status,
			);
		}
	}

	$this->db->select('ddtl.ORDERSR AS line_sr, ddtl.ord_dtl_date, dmst.EDATE AS delivery_date');
	$this->db->from('ddtl');
	$this->db->join('dmst', 'dmst.trnno = ddtl.DTRNNO', 'inner');
	$this->db->where('ddtl.ORDER_TRNNO', $order_id);
	$delivery_q = $this->db->get();

	$delivered_srs = array();
	if ($delivery_q->num_rows() > 0)
	{
		foreach ($delivery_q->result() as $delivery_row)
		{
			$sr = (int) $delivery_row->line_sr;
			$delivered_srs[$sr] = TRUE;
			if ( ! isset($lines_by_sr[$sr]))
			{
				$lines_by_sr[$sr] = array(
					'line_no' => count($lines_by_sr) + 1,
					'line_sr' => $sr,
					'type' => '',
					'remarks' => '',
					'suit_name' => '',
					'events' => array(),
				);
			}
			$delivery_date = ! empty($delivery_row->ord_dtl_date) ? $delivery_row->ord_dtl_date : $delivery_row->delivery_date;
			$has_delivered_event = FALSE;
			foreach ($lines_by_sr[$sr]['events'] as $ev)
			{
				if ($ev['event_key'] === 'delivered')
				{
					$has_delivered_event = TRUE;
					break;
				}
			}
			if ( ! $has_delivered_event)
			{
				$lines_by_sr[$sr]['events'][] = array(
					'date' => (string) $delivery_date,
					'event_key' => 'delivered',
					'employee' => '',
					'dept' => '',
					'status' => 'DELIVER',
				);
			}
		}
	}

	foreach ($lines_by_sr as $sr => &$line_data)
	{
		$has_deliver_status = FALSE;
		foreach ($line_data['events'] as $ev)
		{
			if ($ev['status'] === 'DELIVER' || $ev['event_key'] === 'delivered')
			{
				$has_deliver_status = TRUE;
				break;
			}
		}
		if ($has_deliver_status && ! isset($delivered_srs[$sr]))
		{
			$last_date = '';
			foreach ($line_data['events'] as $ev)
			{
				if ($ev['status'] === 'DELIVER' && ($last_date === '' || $ev['date'] > $last_date))
				{
					$last_date = $ev['date'];
				}
			}
			$has_label = FALSE;
			foreach ($line_data['events'] as $ev)
			{
				if ($ev['event_key'] === 'delivered')
				{
					$has_label = TRUE;
					break;
				}
			}
			if ( ! $has_label && $last_date !== '')
			{
				$line_data['events'][] = array(
					'date' => $last_date,
					'event_key' => 'delivered',
					'employee' => '',
					'dept' => '',
					'status' => 'DELIVER',
				);
			}
		}
		if ($order_header)
		{
			$has_booked = FALSE;
			foreach ($line_data['events'] as $ev)
			{
				if ($ev['event_key'] === 'booked')
				{
					$has_booked = TRUE;
					break;
				}
			}
			if ( ! $has_booked)
			{
				$line_data['events'][] = array(
					'date' => (string) $order_header->order_date,
					'event_key' => 'booked',
					'employee' => '',
					'dept' => 'HO',
					'status' => 'Active',
				);
			}
		}
		if ( ! empty($line_data['events']))
		{
			usort($line_data['events'], array($this, '_order_lifecycle_sort_events'));
		}
	}
	unset($line_data);

	return array(
		'order' => $order_header,
		'lines' => array_values($lines_by_sr),
	);
  }

  protected function _order_lifecycle_event_rank($event_key)
  {
	$ranks = array(
		'booked' => 10,
		'cutting' => 20,
		'cutting_done' => 30,
		'ho2_return' => 40,
		'stitching' => 50,
		'stitching_done' => 60,
		'ready' => 70,
		'delivered' => 80,
		'other' => 90,
	);
	$event_key = (string) $event_key;
	return isset($ranks[$event_key]) ? $ranks[$event_key] : 90;
  }

  protected function _order_lifecycle_sort_events($a, $b)
  {
	$rank_a = $this->_order_lifecycle_event_rank($a['event_key']);
	$rank_b = $this->_order_lifecycle_event_rank($b['event_key']);
	if ($rank_a !== $rank_b)
	{
		return $rank_a - $rank_b;
	}
	$date_a = isset($a['date']) ? strtotime($a['date']) : 0;
	$date_b = isset($b['date']) ? strtotime($b['date']) : 0;
	if ($date_a === $date_b)
	{
		return 0;
	}
	return ($date_a < $date_b) ? -1 : 1;
  }

  protected function _order_lifecycle_event_meta($dept, $status)
  {
	$dept = strtoupper(trim((string) $dept));
	$status = strtoupper(trim((string) $status));

	if ($status === 'DELIVER')
	{
		if ($dept === 'HO')
		{
			return array('key' => 'delivered');
		}
		if ($dept === 'CUT')
		{
			return array('key' => 'cutting_done');
		}
		if ($dept === 'HO2')
		{
			return array('key' => 'ho2_return');
		}
		if ($dept === 'STIT')
		{
			return array('key' => 'stitching_done');
		}
		return array('key' => 'delivered');
	}
	if ($dept === 'HO' && $status === 'ACTIVE')
	{
		return array('key' => 'booked');
	}
	if ($dept === 'HO' && $status === 'DEACTIVE')
	{
		return NULL;
	}
	if ($dept === 'CUT')
	{
		return array('key' => 'cutting');
	}
	if ($dept === 'HO2')
	{
		return array('key' => 'ho2_return');
	}
	if ($dept === 'STIT' && $status === 'ACTIVE')
	{
		return array('key' => 'stitching');
	}
	if ($dept === 'STIT' && $status === 'DEACTIVE')
	{
		return array('key' => 'ready');
	}
	return array('key' => 'other');
  }

  function get_customer_order_context($customer_code)
  {
	$customer_code = (int) $customer_code;
	$context = array(
		'phone' => '',
		'pending_balance' => 0,
		'pending_count' => 0,
		'order_count' => 0,
		'last_order_no' => 0,
		'last_order_date' => '',
		'last_order_total' => 0,
		'has_last_order' => FALSE,
	);

	if ($customer_code <= 0)
	{
		return $context;
	}

	$this->db->select('Phone');
	$this->db->from('customer');
	$this->db->where('code', $customer_code);
	$cq = $this->db->get();
	if ($cq->num_rows() < 1)
	{
		return $context;
	}
	$context['phone'] = trim((string) $cq->row()->Phone);

	$this->db->select('COALESCE(SUM(balance), 0) AS pending_balance, COUNT(*) AS pending_count', FALSE);
	$this->db->from('dmst');
	$this->db->where('customer_id', $customer_code);
	$this->db->where('balance !=', 0);
	$this->db->where('pending', 'YES');
	$this->db->group_start();
	$this->db->where('paidstatus IS NULL', NULL, FALSE);
	$this->db->or_where('paidstatus !=', 'YES');
	$this->db->group_end();
	$pq = $this->db->get();
	if ($pq->num_rows() > 0)
	{
		$prow = $pq->row();
		$context['pending_balance'] = round((float) $prow->pending_balance, 2);
		$context['pending_count'] = (int) $prow->pending_count;
	}

	$this->db->select('COUNT(DISTINCT order_mst.trnno) AS order_count', FALSE);
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->where('measurement.customer_code', $customer_code);
	$this->db->where('order_mst.ostatus !=', 1);
	$ocq = $this->db->get();
	if ($ocq->num_rows() > 0)
	{
		$context['order_count'] = (int) $ocq->row()->order_count;
	}

	$this->db->select('order_mst.trnno, order_mst.EDATE, order_mst.total');
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->where('measurement.customer_code', $customer_code);
	$this->db->where('order_mst.ostatus !=', 1);
	$this->db->order_by('order_mst.trnno', 'DESC');
	$this->db->limit(1);
	$loq = $this->db->get();
	if ($loq->num_rows() > 0)
	{
		$lrow = $loq->row();
		$context['has_last_order'] = TRUE;
		$context['last_order_no'] = (int) $lrow->trnno;
		$context['last_order_date'] = (string) $lrow->EDATE;
		$context['last_order_total'] = round((float) $lrow->total, 2);
	}

	return $context;
  }

  function get_last_order_lines_for_customer($customer_code)
  {
	$customer_code = (int) $customer_code;
	if ($customer_code <= 0)
	{
		return array('ok' => FALSE, 'lines' => array());
	}

	$this->db->select('order_mst.trnno, order_mst.qty');
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->where('measurement.customer_code', $customer_code);
	$this->db->where('order_mst.ostatus !=', 1);
	$this->db->order_by('order_mst.trnno', 'DESC');
	$this->db->limit(1);
	$oq = $this->db->get();
	if ($oq->num_rows() < 1)
	{
		return array('ok' => FALSE, 'lines' => array());
	}
	$order = $oq->row();
	$order_id = (int) $order->trnno;

	$this->db->select('type, kolor_style, SNO, suitrate, addprice, spocket, fpocket, Shawlarpocket, order_remarks');
	$this->db->from('order_dtl');
	$this->db->where('order_trnno', $order_id);
	$this->db->order_by('trnno', 'ASC');
	$dq = $this->db->get();
	if ($dq->num_rows() < 1)
	{
		return array('ok' => FALSE, 'lines' => array());
	}

	$lines = array();
	foreach ($dq->result() as $row)
	{
		$lines[] = array(
			'type' => (string) $row->type,
			'sno' => (int) $row->SNO,
			'suitrate' => (string) $row->suitrate,
			'addprice' => (string) $row->addprice,
			'kolor_style' => (string) $row->kolor_style,
			'fpocket' => (string) $row->fpocket,
			'spocket' => (string) $row->spocket,
			'shawpocket' => (string) $row->Shawlarpocket,
			'remarks' => (string) $row->order_remarks,
		);
	}

	return array(
		'ok' => TRUE,
		'order_no' => $order_id,
		'qty' => count($lines),
		'lines' => $lines,
	);
  }

  function get_customer_order_history($customer_code)
  {
	$customer_code = (int) $customer_code;
	if ($customer_code <= 0)
	{
		return array();
	}

	$this->db->select('order_mst.trnno AS ORDERNO, order_mst.EDATE AS ORDERDATE, order_mst.RDATE AS RDATE, order_mst.total, customer.name, customer.man_napno, customer.Phone');
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->where('customer.code', $customer_code);
	$this->db->where('order_mst.ostatus !=', 1);
	$this->db->order_by('order_mst.trnno', 'DESC');
	$this->db->limit(100);
	$q = $this->db->get();

	if ($q->num_rows() > 0)
	{
		$data = array();
		foreach ($q->result() as $row)
		{
			$data[] = $row;
		}
		return $data;
	}
	return array();
  }
      function transfer()
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
	$this->db->select('*');
    $this->db->from('v_order_mst as order_mst');
	$this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	$this->db->join('transfer_mst', 'transfer_mst.order_id = order_mst.trnno and transfer_mst.sr = order_dtl.trnno');
	$this->db->where('order_mst.ostatus != 1');
	 
	$q = $this->db->get();

	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }
  }
  
      function get_customer($val)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
	$this->db->select('customer.name');
    $this->db->from('measurement');
	$this->db->join('customer', 'measurement.customer_code = customer.code');
	$this->db->where('measurement.code', $val );
	$q = $this->db->get();

	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {

      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data = $row;
      }

      return $data->name;
    }
  }

  /**
   * Remaining payable on one order (total − booking advance − discount − dmst − cash).
   *
   * @param int $order_id
   * @return float
   */
  protected function _order_remaining_balance($order_id)
  {
	$order_id = (int) $order_id;
	if ($order_id <= 0)
	{
		return 0.0;
	}
	$this->db->select('total, advance, discount');
	$this->db->from('order_mst');
	$this->db->where('trnno', $order_id);
	$this->db->limit(1);
	$q = $this->db->get();
	if ($q === FALSE || $q->num_rows() < 1)
	{
		return 0.0;
	}
	$row = $q->row();
	$remaining = round(
		(float) $row->total
		- (float) $row->advance
		- (float) $row->discount
		- (float) $this->get_recevied($order_id)
		- (float) $this->get_recevied_cash($order_id),
		2
	);
	return ($remaining < 0) ? 0.0 : $remaining;
  }

  /**
   * Full delivery + payment summary for one order (Order Status dialog).
   *
   * @return array
   */
  function get_order_delivery_summary($order_id)
  {
	$order_id = (int) $order_id;
	$out = array(
		'ok' => FALSE,
		'message' => 'not_found',
		'order_id' => $order_id,
	);
	if ($order_id <= 0)
	{
		return $out;
	}

	$this->db->select('order_mst.trnno, order_mst.total, order_mst.advance, order_mst.discount, order_mst.qty, order_mst.EDATE AS ORDERDATE, order_mst.RDATE, order_mst.EDATE, order_mst.ostatus, order_mst.naap_no, customer.man_napno, customer.code AS customer_code, customer.name AS customer_name, customer.Phone');
	$this->db->from('order_mst');
	$this->db->join('measurement', 'measurement.code = order_mst.naap_no', 'left');
	$this->db->join('customer', 'customer.code = measurement.customer_code', 'left');
	$this->db->where('order_mst.trnno', $order_id);
	$this->db->limit(1);
	$hq = $this->db->get();
	if ($hq === FALSE || $hq->num_rows() < 1)
	{
		return $out;
	}
	$header = $hq->row();

	$total = round((float) $header->total, 2);
	$advance_booking = round((float) $header->advance, 2);
	$discount = round((float) $header->discount, 2);
	$received_dmst = round((float) $this->get_recevied($order_id), 2);
	$received_cash = round((float) $this->get_recevied_cash($order_id), 2);
	$advance_total = round($advance_booking + $received_dmst + $received_cash, 2);
	$order_balance = round($total - $advance_booking - $discount - $received_dmst - $received_cash, 2);
	if ($order_balance < 0)
	{
		$order_balance = 0.0;
	}

	$customer_code = (int) $header->customer_code;

	// Other orders' unpaid balances for this customer (ignore pending flag — balance>0 is what staff see in history).
	$previous_outstanding = 0.0;
	if ($customer_code > 0)
	{
		$this->db->select('COALESCE(SUM(balance), 0) AS prev_bal', FALSE);
		$this->db->from('dmst');
		$this->db->where('customer_id', $customer_code);
		$this->db->where('order_id !=', $order_id);
		$this->db->where('balance >', 0);
		$this->db->group_start();
		$this->db->where('paidstatus IS NULL', NULL, FALSE);
		$this->db->or_where('paidstatus !=', 'YES');
		$this->db->group_end();
		$pq = $this->db->get();
		if ($pq !== FALSE && $pq->num_rows() > 0)
		{
			$previous_outstanding = round((float) $pq->row()->prev_bal, 2);
		}
	}
	$amount_due_now = round($order_balance + $previous_outstanding, 2);

	// Latest transfer status per suit line (avoid duplicate HO/CUT/STIT rows).
	$latest_transfer = array();
	$this->db->select('sr, dept, status');
	$this->db->from('transfer_mst');
	$this->db->where('order_id', $order_id);
	$this->db->order_by('trnno', 'ASC');
	$tq = $this->db->get();
	if ($tq !== FALSE && $tq->num_rows() > 0)
	{
		foreach ($tq->result() as $trow)
		{
			$latest_transfer[(int) $trow->sr] = $trow;
		}
	}

	$this->db->select('order_dtl.trnno AS line_sr, order_dtl.type, order_dtl.SNO, order_dtl.suitrate, order_dtl.addprice, order_dtl.drate, order_dtl.erate, order_dtl.order_remarks, suit.name AS suit_name');
	$this->db->from('order_dtl');
	$this->db->join('suit', 'suit.SNO = order_dtl.SNO', 'left');
	$this->db->where('order_dtl.order_trnno', $order_id);
	$this->db->order_by('order_dtl.trnno', 'ASC');
	$lq = $this->db->get();
	$suits = array();
	$ready_count = 0;
	if ($lq !== FALSE && $lq->num_rows() > 0)
	{
		foreach ($lq->result() as $row)
		{
			$line_sr = (int) $row->line_sr;
			$trow = isset($latest_transfer[$line_sr]) ? $latest_transfer[$line_sr] : NULL;
			$dept = $trow ? strtoupper(trim((string) $trow->dept)) : '';
			$tstatus = $trow ? trim((string) $trow->status) : '';
			$line_total = round(
				(float) $row->suitrate + (float) $row->addprice + (float) $row->drate + (float) $row->erate,
				2
			);
			$is_ready = ($dept === 'STIT' && $tstatus === 'Deactive');
			$is_delivered = (strtoupper($tstatus) === 'DELIVER');
			if ($is_ready)
			{
				$ready_count++;
			}
			$status_label = 'Booking';
			if ($is_delivered)
			{
				$status_label = 'Delivered';
			}
			elseif ($is_ready)
			{
				$status_label = 'Ready 4 Deliver';
			}
			elseif ($dept === 'STIT')
			{
				$status_label = 'Stitching';
			}
			elseif ($dept === 'HO2')
			{
				$status_label = 'After Cutting';
			}
			elseif ($dept === 'CUT')
			{
				$status_label = 'Cutting';
			}
			elseif ($dept === 'HO')
			{
				$status_label = 'Booking';
			}

			$suits[] = array(
				'line_sr' => $line_sr,
				'type' => (string) $row->type,
				'suit_name' => (string) $row->suit_name,
				'remarks' => (string) $row->order_remarks,
				'line_total' => $line_total,
				'dept' => $dept,
				'transfer_status' => $tstatus,
				'is_ready' => $is_ready,
				'is_delivered' => $is_delivered,
				'status_label' => $status_label,
			);
		}
	}

	$history = array();
	$dmst_by_key = array(); // dedupe key => true
	$order_remaining_map = array(); // order_id => latest known remaining after receipt

	// All delivery receipts for this customer (paid + pending) so Balance column is filled.
	$this->db->select('dmst.trnno, dmst.EDATE, dmst.PAID, dmst.balance, dmst.pending, dmst.order_id, dmst.NPAID_dATE');
	$this->db->from('dmst');
	$this->db->group_start();
	$this->db->where('dmst.order_id', $order_id);
	if ($customer_code > 0)
	{
		$this->db->or_where('dmst.customer_id', $customer_code);
	}
	$this->db->group_end();
	$this->db->order_by('dmst.EDATE', 'DESC');
	$this->db->order_by('dmst.trnno', 'DESC');
	$this->db->limit(50);
	$hq2 = $this->db->get();
	if ($hq2 !== FALSE && $hq2->num_rows() > 0)
	{
		foreach ($hq2->result() as $h)
		{
			$oid = (int) $h->order_id;
			$paid = round((float) $h->PAID, 2);
			$bal = round((float) $h->balance, 2);
			$date = (string) $h->EDATE;
			if ( ! isset($order_remaining_map[$oid]))
			{
				$order_remaining_map[$oid] = $bal;
			}
			$key = $oid.'|'.$date.'|'.number_format($paid, 2, '.', '');
			$dmst_by_key[$key] = TRUE;
			$history[] = array(
				'kind' => 'delivery_receipt',
				'date' => $date,
				'order_id' => $oid,
				'paid' => $paid,
				'balance' => $bal,
				'pending' => (string) $h->pending,
				'due_date' => (string) $h->NPAID_dATE,
				'label' => ($oid === $order_id)
					? ('Receipt on order #'.$oid)
					: ('Receipt on order #'.$oid),
			);
		}
	}

	// Cash receipts: fill Balance from matching dmst / order remaining; skip duplicates of dmst rows.
	$this->db->select('cash.TRNNO, cash.EDATE, cash.amount, cash.narration, cash.order_no, cash.type');
	$this->db->from('cash');
	$this->db->group_start();
	$this->db->where('cash.order_no', $order_id);
	if ($customer_code > 0)
	{
		$this->db->or_where('cash.customer_id', $customer_code);
	}
	$this->db->group_end();
	$this->db->order_by('cash.EDATE', 'DESC');
	$this->db->order_by('cash.TRNNO', 'DESC');
	$this->db->limit(40);
	$cq = $this->db->get();
	if ($cq !== FALSE && $cq->num_rows() > 0)
	{
		foreach ($cq->result() as $c)
		{
			$oid = (int) $c->order_no;
			$narration = trim((string) $c->narration);
			if ($oid <= 0 && preg_match('/\b(?:Order|آرڈر)\s*#?\s*(\d+)/iu', $narration, $m))
			{
				$oid = (int) $m[1];
			}
			$paid = round((float) $c->amount, 2);
			$date = (string) $c->EDATE;
			$key = $oid.'|'.$date.'|'.number_format($paid, 2, '.', '');
			if ($oid > 0 && isset($dmst_by_key[$key]))
			{
				continue; // already shown via dmst with balance
			}

			$bal = NULL;
			if ($oid > 0)
			{
				if (isset($order_remaining_map[$oid]))
				{
					$bal = $order_remaining_map[$oid];
				}
				else
				{
					// Compute remaining for that order if dmst row was not loaded.
					$bal = $this->_order_remaining_balance($oid);
					$order_remaining_map[$oid] = $bal;
				}
			}

			$history[] = array(
				'kind' => 'cash',
				'date' => $date,
				'order_id' => $oid,
				'paid' => $paid,
				'balance' => $bal,
				'pending' => '',
				'due_date' => '',
				'label' => $narration !== ''
					? $narration
					: ('Cash'.($oid > 0 ? (' against order #'.$oid) : '')),
			);
		}
	}

	usort($history, function ($a, $b) {
		$cmp = strcmp((string) $b['date'], (string) $a['date']);
		if ($cmp !== 0)
		{
			return $cmp;
		}
		return ((int) $b['order_id']) - ((int) $a['order_id']);
	});

	return array(
		'ok' => TRUE,
		'message' => '',
		'order_id' => $order_id,
		'customer' => array(
			'code' => $customer_code,
			'name' => (string) $header->customer_name,
			'phone' => (string) $header->Phone,
			'naap_no' => (string) $header->man_napno,
		),
		'dates' => array(
			'order_date' => (string) $header->ORDERDATE,
			'return_date' => (string) $header->RDATE,
			'entry_date' => (string) $header->EDATE,
		),
		'qty' => (float) $header->qty,
		'suits' => $suits,
		'suit_count' => count($suits),
		'ready_count' => $ready_count,
		'can_deliver' => ($ready_count > 0 && (int) $header->ostatus === 0),
		'money' => array(
			'order_total' => $total,
			'booking_advance' => $advance_booking,
			'received_on_delivery' => $received_dmst,
			'received_cash' => $received_cash,
			'advance_total' => $advance_total,
			'discount' => $discount,
			'order_remaining' => $order_balance,
			'previous_outstanding' => $previous_outstanding,
			'amount_due_now' => $amount_due_now,
		),
		'history' => array_slice($history, 0, 40),
	);
  }
  
  
}

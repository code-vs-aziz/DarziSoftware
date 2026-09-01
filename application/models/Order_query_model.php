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
   */
  function get_order_ready_deliver($filters = array())
  {
	$filters = $this->_normalize_order_status_filters($filters);

	$this->db->select('order_mst.trnno AS ORDERNO, transfer_mst.trnno AS TRANSFERID, transfer_mst.sr AS ORDERSR, order_dtl.type AS line_type, '.$this->_order_customer_snap_select_sql().', order_mst.total, order_mst.advance, customer.Phone, order_mst.RDATE AS RETURNDATE');
	$this->db->from('transfer_mst');
	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
	$this->db->join('order_dtl', 'order_dtl.order_trnno = transfer_mst.order_id AND order_dtl.trnno = transfer_mst.sr', 'left');
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
	if ($this->_has_order_status_detail_filters($filters))
	{
		$this->_apply_order_status_detail_filters($filters);
	}
	$order_limit = 500;
	if (isset($filters['row_limit']) && (int) $filters['row_limit'] > 0)
	{
		$order_limit = min(10000, (int) $filters['row_limit']);
	}
	$this->db->order_by('order_mst.RDATE', 'ASC');
	$this->db->order_by('order_mst.trnno', 'ASC');
	$this->db->order_by('transfer_mst.sr', 'ASC');
	$q = $this->db->get();

	if ($q->num_rows() < 1)
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
      $parts[] = 'COALESCE(oms.`'.$col.'`, measurement.`'.$col.'`) AS `'.$col.'`';
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

	$this->db->select('order_mst.trnno, order_mst.EDATE, order_mst.RDATE, order_mst.qty, order_mst.total, order_mst.advance, order_mst.Discount, '.$this->_order_customer_snap_select_sql().', customer.Phone');
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
	else
	{
		$this->db->group_start();
		$this->db->where('transfer_mst.status', 'Active');
		$this->db->or_group_start();
		$this->db->where('transfer_mst.dept', 'STIT');
		$this->db->where('transfer_mst.status', 'Deactive');
		$this->db->group_end();
		$this->db->group_end();
	}
	$this->db->order_by('order_mst.RDATE', 'ASC');
	$this->db->order_by('order_mst.trnno', 'ASC');
	 
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
  
  
}

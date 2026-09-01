<?php
class employee_report_MD extends CI_Model

{
/* we will use the function getUsers */
  function get_dat($tab_name)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
    $this->db->select('*');
	$q = $this->db->get($tab_name);

	
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
  function get_data_customer_sale($date,$date1)
  {
	//$this->db->distinct();
	 
	$this->db->select('customer.man_napno,customer.name as CUSTOMER_NAME,sum(order_mst.qty) AS TOTAL_QTY,sum(order_mst.total) AS TOTAL_AMOUNT');
	$this->db->from('order_mst');
  	 $this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
	$this->db->join('customer', 'customer.code = measurement.customer_code');
    $this->db->where('order_mst.EDATE between "'.$date.'" and "'.$date1.'" and  order_mst.total > 0' );
	$this->db->group_by('customer.code');
	$this->db->order_by('TOTAL_QTY', 'DESC');
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
	  return $data ;
    } 
    }
 
  function get_order_mst($where)
  {
	$this->db->distinct();
    $this->db->select('order_mst.trnno,order_mst.EDATE,customer.name,customer.man_napno,order_mst.total,order_mst.advance,customer.Phone,order_mst.qty,order_mst.RDATE
,order_mst.total ,order_mst.advance ,order_mst.discount	');
	$this->db->from('transfer_mst');
 	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
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
	 
	$this->db->distinct();
    $this->db->select('order_mst.trnno,customer.name,customer.man_napno,order_mst.total,order_mst.advance,customer.Phone,order_mst.RDATE as RETURNDATE');
	$this->db->from('transfer_mst');
 	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
 	//$this->db->join('order_dtl', 'order_dtl.order_trnno = order_mst.trnno and order_dtl.trnno = transfer_mst.sr ');
 	$this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
 	$this->db->join('customer', 'customer.code = measurement.customer_code');	
	$this->db->where('transfer_mst.dept = "STIT" and  transfer_mst.status  = "Deactive"'  );
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
  
 function get_dat_style()
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
    $this->db->select('vol_name,CAST(vol_no AS UNSIGNED) as vol_no,CAST(design AS UNSIGNED) as design,DNO ');
	$this->db->from('style');
    $this->db->order_by("vol_name,vol_no,design");
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
	//print_r( $data );
	//die;
      return $data;
    }
  }
 
   function get_dat_embroidery()
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
    $this->db->select('LTRIM(RTRIM(embroidery_name)) AS embroidery_name,CAST(vol_no AS UNSIGNED) as vol_no,CAST(design AS UNSIGNED) as design,ENO');
	$this->db->from('embroidery');
    $this->db->order_by("LTRIM(RTRIM(embroidery_name)),vol_no,design");
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
 
 
 
  function get_dat_where($tab_name,$where)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
    $this->db->select('*');
	$this->db->from($tab_name);
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

 
    function measurement($where)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
	$this->db->select('* , measurement.remarks AS MREMARKS,measurement.code AS MCODE,customer.code AS Customerno,customer.man_napno AS MANNAAPNO');
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
  }
  
 
      function get_order($where)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
	$this->db->select('*,order_mst.EDATE as ORDERDATE ,order_mst.trnno as TRNNO,measurement.remarks AS MREMARKS');
    $this->db->from('order_mst');
	  $this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	//  $this->db->join('style', 'style.DNO = order_dtl.design');
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
  
   function get_opeing_cash($date)
  {
	//$this->db->distinct();
	 
 
	$this->db->select('SUM(order_mst.advance) AS RECEIPT');
	$this->db->from('order_mst');
  	 $this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
	$this->db->join('customer', 'customer.code = measurement.customer_code');
    //$this->db->where('measurement.customer_code = order_mst.naap_no );
    $this->db->where('advance != 0 and order_mst.EDATE < "'.$date.'"' );
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
           $data[] = $row->RECEIPT;
      }
    } 
	$this->db->select('SUM(amount) AS RECEIPT');
	$this->db->from('cash');
	   $this->db->join('customer', 'customer.code = cash.customer_id');
	//  $this->db->join('employee', 'employee.empno = cash.emp_id');
    // $this->db->where('cash.emp_id  = '.$emplyee.'');
	 $this->db->where('cash.EDATE < "'.$date.'" AND type = 2 AND type != 7' );
	$q1 = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q1->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q1->result() as $row1)
      {
       $data[] = $row1;
      }
    }
	
	$this->db->select('SUM(amount) AS RECEIPT');
	$this->db->from('cash');
	//   $this->db->join('customer', 'customer.code = cash.customer_id');
	//  $this->db->join('employee', 'employee.empno = cash.emp_id');
    // $this->db->where('cash.emp_id  = '.$emplyee.'');
	 $this->db->where('cash.EDATE < "'.$date.'" AND cash.type <> 2' );
	$q2 = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q2->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q2->result() as $row2)
      {
       $data[] = $row2;
      }
    }
	
	return $row->RECEIPT + $row1->RECEIPT - $row2->RECEIPT;
  }
  
   function get_opeing_emp($date)
  {
	//$this->db->distinct();
	 
       $this->db->select('SUM( price ) AS OPEING');
	$this->db->from('transfer_mst');
  	$this->db->join('order_dtl', 'order_dtl.trnno = transfer_mst.sr and order_dtl.order_trnno = transfer_mst.order_id');
  	 $this->db->join('order_mst', 'order_mst.trnno	 = order_dtl.order_trnno');
  	 $this->db->join('measurement', 'measurement.code	 = order_mst.naap_no');
   $this->db->join('customer', 'customer.code = measurement.customer_code');
   // $this->db->where('measurement.customer_code = '.$emplyee.'' );
    $this->db->where('transfer_mst.EDATE < "'.$date.'"  and 
	( ( dept = "CUT" and status = "Deactive" or status = "DELIVER" ) or ( dept = "STIT" and ( status = "Deactive"  or status = "DELIVER" ) ) )'  );
 
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row->OPEING;
      }
    }
	  $this->db->select('SUM(amount) AS PAID');
	$this->db->from('cash');
   // $this->db->where('cash.emp_id  = '.$emplyee.'');
	 $this->db->where('cash.EDATE < "'.$date.'" and cash.type != 7 and cash.type != 2');
	$q1 = $this->db->get();
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q1->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q1->result() as $row1)
      {
         $data[] = $row1->PAID;
      }
    }

	$this->db->select('SUM(abs( amount )) AS PAID');
	$this->db->from('cash');
   // $this->db->where('cash.emp_id  = '.$emplyee.'');
	 $this->db->where('cash.EDATE < "'.$date.'" and ( cash.type = 7 or cash.type = 2)');
	$q2 = $this->db->get();
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q2->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q2->result() as $row2)
      {
         $data[] = $row2->PAID;
      }
    }
	
	
	if ($row2->PAID == '')
	{
		return $row->OPEING -  $row1->PAID  ;
	}
	else{
		return $row->OPEING - (    $row1->PAID - $row2->PAID ) ;
	}
  }
  
  
  
  
  
    function get_opeing($date,$emplyee)
  {
	//$this->db->distinct();
	 
	 

	 
	 
	 
	 
	 
	 
	 
	 
    $this->db->select('sum(total)-sum(advance)-sum(discount) AS OPEING');
	$this->db->from('order_mst');
 	$this->db->join('measurement', 'order_mst.naap_no = measurement.code');
    $this->db->where('measurement.customer_code = '.$emplyee.'' );
    $this->db->where('order_mst.EDATE < "'.$date.'"' );
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row->OPEING;
      }
    }
	    $this->db->select('sum(amount) AS PAID');
	$this->db->from('cash');
    $this->db->where('cash.customer_id  = '.$emplyee.'');
	$this->db->where('cash.EDATE < "'.$date.'" and type = 7' );
	$q1 = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q1->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q1->result() as $row1)
      {
         $data[] = $row1->PAID;
      }
    }
	
	 
	return $row->OPEING - $row1->PAID ;
  }
  
    function get_data_customer($date,$emplyee)
  {
	//$this->db->distinct();
	



	
    $this->db->select('*');
	$this->db->from('order_mst');
 	$this->db->join('measurement', 'order_mst.naap_no = measurement.code');
    $this->db->where('measurement.customer_code = '.$emplyee.'' );
    $this->db->where('order_mst.EDATE > "'.$date.'"' );
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
    }

	$this->db->select('*');
	$this->db->from('cash');
	
    $this->db->where('cash.customer_id  = '.$emplyee.'');
	$this->db->where('cash.EDATE > "'.$date.'"' );
	$q1 = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q1->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q1->result() as $row1)
      {
       $data[] = $row1;
      }
    }
	
	return $data ;
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
	//$this->db->distinct();
	 
 
	 
	 
     $this->db->select('empno,name,(SELECT  sum(case type when "2" then amount * -1 else amount end) from cash where  edate BETWEEN "'.$date.'" and "'.$date1.'" and emp_id = employee.empno and ( type = 1 || type = 2 )) as Paid,
case empno WHEN "10" then (SELECT sum(amount) from cash where  edate BETWEEN "'.$date.'" and "'.$date1.'" and emp_id = employee.empno and type = 7) ELSE (select sum(price) from transfer_mst inner join order_dtl on order_dtl.trnno = transfer_mst.sr and order_dtl.order_trnno = transfer_mst.order_id inner join order_mst on order_mst.trnno = order_dtl.order_trnno inner join measurement on measurement.code = order_mst.naap_no inner join customer on customer.code = measurement.customer_code where  empno = employee.empno and transfer_mst.EDATE BETWEEN "'.$date.'" and "'.$date1.'" and ( ( dept = "CUT" and ( status = "Deactive" or status = "DELIVER" ) ) or ( dept = "STIT" and ( status = "Deactive" or status = "DELIVER" ) ) )) END as Salary, (select count(order_dtl.trnno) from transfer_mst inner join order_dtl on order_dtl.trnno = transfer_mst.sr and order_dtl.order_trnno = transfer_mst.order_id inner join order_mst on order_mst.trnno = order_dtl.order_trnno inner join measurement on measurement.code = order_mst.naap_no inner join customer on customer.code = measurement.customer_code where  empno = employee.empno and transfer_mst.EDATE BETWEEN "'.$date.'" and "'.$date1.'" and ( ( dept = "CUT" and ( status = "Deactive" or status = "DELIVER" ) ) or ( dept = "STIT" and ( status = "Deactive" or status = "DELIVER" ) ) )) as Total_SUIT');
	$this->db->from('employee');
  	
  
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
	 
    }
	 
	
	 
	// usort($data, 'sortByWeight');
	
	return $data ;
  }
  
  
  function get_data_report_del_order($date,$date1)
  {
	//$this->db->distinct();
	 
 
	 
	 
     $this->db->select(' *, dmst.EDATE as  ENDATE,order_mst.trnno as ORDERNO
	 ,order_mst.EDATE as ORDEREDATE, customer.name as CUSTOMERNAME , PAID, ( SELECT COUNT( * ) from ddtl where ORDER_TRNNO = dmst.order_id and ord_dtl_date = dmst.EDATE ) as COunt_suit
	 ');
	 //, (SELECT EDATE FROM transfer_mst as A where order_id = transfer_mst.order_id and sr = transfer_mst.sr
 	//and dept = "HO2") AS ERDATE'
	 //$this->db->select('*,order_mst.trnno as ORDERNO');
	$this->db->from('dmst');
  //	 $this->db->join('order_dtl', 'order_dtl.order_trnno = dmst.order_id');
	// $this->db->join('ddtl', 'ddtl.ORDER_TRNNO = dmst.order_id and ddtl.ORDERSR = order_dtl.trnno');
  	 $this->db->join('order_mst', 'order_mst.trnno	 = dmst.order_id');
  	 $this->db->join('measurement', 'measurement.code	 = order_mst.naap_no');
   $this->db->join('customer', 'customer.code = measurement.customer_code');
  // $this->db->join('employee', 'employee.empno = transfer_mst.empno');
   // $this->db->where('measurement.customer_code = '.$emplyee.'' );
    $this->db->where('dmst.EDATE between "'.$date.'" and "'.$date1.'" and PAID != "0.00"' );
	//( dept = "STIT" and ( status = "Deactive"  or status = "DELIVER" ) )  AND employee.type = "Stitching"'  );
//	$this->db->order_by("order_mst.EDATE");
		$this->db->order_by("dmst.EDATE");
	$this->db->order_by("order_mst.naap_no");
 //	$this->db->order_by("order_mst.trnno");
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
    }
	 
		 // print_r($data);
	 //  die;
	 
	// usort($data, 'sortByWeight');
	return $data ;
  }
  
  
   function get_data_report_all_month($date,$date1,$emplyee)
  {
	//$this->db->distinct();
	 
 
	 
	 
     $this->db->select('*,transfer_mst.EDATE as  ENDATE,transfer_mst.RDATE as  RDATE,order_mst.trnno as ORDERNO
	 ,order_mst.EDATE as ORDEREDATE, customer.name as CUSTOMERNAME , order_mst.total
	 ');
	 //, (SELECT EDATE FROM transfer_mst as A where order_id = transfer_mst.order_id and sr = transfer_mst.sr
 	//and dept = "HO2") AS ERDATE'
	 //$this->db->select('*,order_mst.trnno as ORDERNO');
	$this->db->from('transfer_mst');
  	$this->db->join('order_dtl', 'order_dtl.trnno = transfer_mst.sr and order_dtl.order_trnno = transfer_mst.order_id');
  	 $this->db->join('order_mst', 'order_mst.trnno	 = order_dtl.order_trnno');
  	 $this->db->join('measurement', 'measurement.code	 = order_mst.naap_no');
   $this->db->join('customer', 'customer.code = measurement.customer_code');
   $this->db->join('employee', 'employee.empno = transfer_mst.empno');
   // $this->db->where('measurement.customer_code = '.$emplyee.'' );
    $this->db->where('transfer_mst.EDATE between "'.$date.'" and "'.$date1.'" and 
	( dept = "STIT" and ( status = "Deactive"  or status = "DELIVER" ) )  AND employee.type = "Stitching"'  );
//	$this->db->order_by("order_mst.EDATE");
//	$this->db->order_by("transfer_mst.EDATE");
	$this->db->order_by("transfer_mst.trnno");
 //	$this->db->order_by("order_mst.trnno");
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
    }
	 
		 // print_r($data);
	 //  die;
	 
	// usort($data, 'sortByWeight');
	return $data ;
  }
  
     function get_data_exp($date,$date1,$type)
  {
	//$this->db->distinct();
	 
	$this->db->select('*');
	$this->db->from('cash');
  //	 $this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
//	$this->db->join('customer', 'customer.code = measurement.customer_code');
    //$this->db->where('measurement.customer_code = order_mst.naap_no );
	if($type == '')
	{
    $this->db->where('( type != 2 and type != 1 ) and EDATE between "'.$date.'" and "'.$date1.'"' );
	}
	else
	{
		$this->db->where('( type != 2 and type != 1 ) and EDATE between "'.$date.'" and "'.$date1.'" and type = "'.$type.'"' );
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
	  return $data ;
    } 
    } 
     function get_pending_cash($date,$date1)
  {
	//$this->db->distinct();
	 
	$this->db->select('*, dmst.EDATE AS DEDATE,customer.code as CUSTOMERNO');
	$this->db->from('dmst');
  	// $this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
	 $this->db->join('customer', 'customer.code = dmst.customer_id');
    //$this->db->where('measurement.customer_code = order_mst.naap_no );
    $this->db->where('balance != 0 and pending = "YES" and dmst.NPAID_dATE between "'.$date.'" and "'.$date1.'" and paidstatus != "YES"'  );
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
   function get_data_cash($date,$date1)
  {
	//$this->db->distinct();
	 
	$this->db->select('order_mst.advance AS RECEIPT,order_mst.EDATE AS ENTRYDATE,CONCAT("Advance Recevied Cash Agaisnt Order ",trnno , " Naap No : " , man_napno , "  " , name
	) as NARRATION
	
	 
	');
	$this->db->from('order_mst');
  	 $this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
	$this->db->join('customer', 'customer.code = measurement.customer_code');
    //$this->db->where('measurement.customer_code = order_mst.naap_no );
    $this->db->where('advance != 0 and order_mst.EDATE between "'.$date.'" and "'.$date1.'"' );
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
    } 
/*	  $this->db->select('PAID AS RECEIPT,dmst.EDATE AS ENTRYDATE, CONCAT("Recevied Cash  Agaisnt Order deliverd ",order_id, " Naap No : " , man_napno , "  " , name) as NARRATION');
	$this->db->from('dmst');
	$this->db->join('order_mst', 'dmst.order_id = order_mst.trnno');
	 $this->db->join('measurement', 'measurement.code = order_mst.naap_no');	
	$this->db->join('customer', 'customer.code = measurement.customer_code');
  	//$this->db->join('order_dtl', 'order_dtl.trnno = transfer_mst.sr and order_dtl.order_trnno = transfer_mst.order_id');
   // $this->db->where('measurement.customer_code = '.$emplyee.'' );
    $this->db->where('PAID != 0 and dmst.EDATE between "'.$date.'" and "'.$date1.'"' );
	$q = $this->db->get();
	
     after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
  /*  if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
           $data[] = $row;
      }
    } 
*/	
 
	 
	$this->db->select('amount AS RECEIPT,cash.EDATE AS ENTRYDATE,type, CONCAT(narration, " Naap No : " , man_napno , "  " , customer.name) as NARRATION');
	$this->db->from('cash');
	   $this->db->join('customer', 'customer.code = cash.customer_id');
	//  $this->db->join('employee', 'employee.empno = cash.emp_id');
    // $this->db->where('cash.emp_id  = '.$emplyee.'');
	 $this->db->where('cash.EDATE between "'.$date.'" and "'.$date1.'" and type != 7');
	$q1 = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q1->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q1->result() as $row1)
      {
       $data[] = $row1;
      }
    }
	
	$this->db->select('amount AS RECEIPT,cash.EDATE AS ENTRYDATE, cash.type,CONCAT(narration,  "  " , employee.name) as NARRATION');
	$this->db->from('cash');
	 //  $this->db->join('customer', 'customer.code = cash.customer_id');
	  $this->db->join('employee', 'employee.empno = cash.emp_id');
    // $this->db->where('cash.emp_id  = '.$emplyee.'');
	 $this->db->where('cash.EDATE between "'.$date.'" and "'.$date1.'" and cash.type != 7');
	$q1 = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q1->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q1->result() as $row1)
      {
       $data[] = $row1;
      }
    }
	
	$this->db->select('amount AS RECEIPT,cash.EDATE AS ENTRYDATE, cash.type,narration as NARRATION');
	$this->db->from('cash');
	 //  $this->db->join('customer', 'customer.code = cash.customer_id');
	//  $this->db->join('employee', 'employee.empno = cash.emp_id');
    // $this->db->where('cash.emp_id  = '.$emplyee.'');
	 $this->db->where('cash.EDATE between "'.$date.'" and "'.$date1.'" and type != 1 and type != 2 and type != 7');
	$q1 = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q1->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q1->result() as $row1)
      {
       $data[] = $row1;
      }
    }
	//  print_r($data);
	//    die;
	krsort($data[]);
	return $data ;
  }
  
  
  
  
  
  
  
  
  function get_order_order_Detai3($where)
  {
	//$this->db->distinct();
    $this->db->select('*,order_dtl.order_remarks AS ORDREMARKS,order_dtl.trnno ORDERSR ,order_mst.trnno as ORDER,measurement.remarks AS MREMARKS');
	$this->db->from('transfer_mst');
 	$this->db->join('order_mst', 'transfer_mst.order_id = order_mst.trnno');
 	$this->db->join('order_dtl', 'order_dtl.trnno = transfer_mst.sr');
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
     function get_order_order_Detai2($where)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */

    //$this->db->where($where_arr);
	$this->db->select('*,order_dtl.trnno AS SUITNO,order_mst.trnno AS ORDERNO,order_mst.EDATE AS ORDERDATE,order_mst.RDATE AS RORDERDATE,order_dtl.design AS styleno,
	transfer_mst.trnno AS TRANSFERID,transfer_mst.RDATE AS RETURNDATE,transfer_mst.EDATE AS TRANSFEREDATE,measurement.remarks AS MREMARKS,order_mst.trnno as TRNNO,order_dtl.trnno ORDERSR,
	transfer_mst.empno AS employee');
    $this->db->from('v_order_mst as order_mst');
	  $this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	// $this->db->join('style', 'style.DNO = order_dtl.design');
	   $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->join('transfer_mst', 'transfer_mst.order_id = order_dtl.order_trnno and transfer_mst.sr =  order_dtl.trnno'  );
	 $this->db->where($where);
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
  
  
    function order_Status_D()
  {
    /* all the queries relating to the data we want to retrieve will go in here. */

    //$this->db->where($where_arr);
	$this->db->select('*,order_dtl.trnno AS SUITNO,order_mst.trnno AS ORDERNO,order_mst.RDATE AS ORDERRDATE,order_mst.EDATE AS ORDERDATE,order_dtl.design AS styleno,
	transfer_mst.trnno AS TRANSFERID,transfer_mst.RDATE AS RETURNDATE,transfer_mst.EDATE AS TRANSFEREDATE,measurement.remarks AS MREMARKS,order_mst.trnno as TRNNO,order_dtl.trnno ORDERSR');
    $this->db->from('V_order_mst as order_mst');
	  $this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	// $this->db->join('style', 'style.DNO = order_dtl.design');
	   $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->join('transfer_mst', 'transfer_mst.order_id = order_dtl.order_trnno and transfer_mst.sr =  order_dtl.trnno  '  );
	 $this->db->where(' status = "DELIVER" and   dept = "STIT"   ');
	 
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
  
  
   function order_Status()
  {
    /* all the queries relating to the data we want to retrieve will go in here. */

    //$this->db->where($where_arr);
	$this->db->select('*,order_dtl.trnno AS SUITNO,order_mst.trnno AS ORDERNO,order_mst.RDATE AS ORDERRDATE,order_mst.EDATE AS ORDERDATE,order_dtl.design AS styleno,
	transfer_mst.trnno AS TRANSFERID,transfer_mst.RDATE AS RETURNDATE,transfer_mst.EDATE AS TRANSFEREDATE,measurement.remarks AS MREMARKS,order_mst.trnno as TRNNO,order_dtl.trnno ORDERSR');
    $this->db->from('V_order_mst as order_mst');
	  $this->db->join('order_dtl', 'order_mst.trnno = order_dtl.order_trnno');
	// $this->db->join('style', 'style.DNO = order_dtl.design');
	   $this->db->join('measurement', 'measurement.code = order_mst.naap_no');
	$this->db->join('customer', 'customer.code = measurement.customer_code');
	$this->db->join('transfer_mst', 'transfer_mst.order_id = order_dtl.order_trnno and transfer_mst.sr =  order_dtl.trnno  '  );
	 $this->db->where(' status != "DELIVER" and  ( status ="Active" or ( dept = "STIT"  and status ="Deactive" ))');
	 
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
  
   function get_order_order_Detail()
  {
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
	 $this->db->select('*');
    	$this->db->from('oderdtl');
	 	 //	  $this->db->where('ORDERNO = 267');	
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
  
  
       function get_max_id($field,$tab)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
	$this->db->select_max($field);
	$q = $this->db->get($tab);

	
    /* after weve made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {

      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data = $row;
      }

      return $data->$field;
    }
  }

  
}
?>
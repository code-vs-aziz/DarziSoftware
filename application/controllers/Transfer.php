<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transfer extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
 
	
	
		public function index()
	{
 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 $data["employee"]= $this->master_data->get_dat(array('employee'));
	 
 	$this->load->view('transfer',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
 	public function transfer_order()
	{
	 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	// $data["measurement"]= $this->order_query->measurement();
			$transfer_id = (int) $this->uri->segment(3);
			$line_sr = (int) $this->uri->segment(5);
			$where = 'transfer_mst.trnno = '.$transfer_id;
			if ($line_sr > 0)
			{
				$where .= ' AND order_dtl.trnno = '.$line_sr;
			}

			$measurement_rows = $this->order_query->get_order_order_Detai2($where);
			$data['Measurement_data'] = (!empty($measurement_rows) && isset($measurement_rows[0]))
				? $measurement_rows[0]
				: NULL;
				 	 $from_dept = strtoupper(trim((string) $this->uri->segment(4)));
				 	 $data['from_dept'] = $from_dept;
				 	 $data['transfer_employees'] = in_array($from_dept, array('HO', 'HO2'), TRUE)
				 	 	? $this->employee_query->get_transfer_employees($from_dept)
				 	 	: array();
					
 	$this->load->view('transfer_order',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
 
 
 public function Order_Status()
	{
	 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}
		$os_scope = strtolower(trim((string) $this->input->get('scope', TRUE)));
		if ($os_scope !== 'delivered')
		{
			$os_scope = 'current';
		}
		$status_filters = array(
			'customer_name' => trim((string) $this->input->get('customer_name', TRUE)),
			'order_no' => trim((string) $this->input->get('order_no', TRUE)),
			'naap_no' => trim((string) $this->input->get('naap_no', TRUE)),
			'mobile_no' => trim((string) $this->input->get('mobile_no', TRUE)),
			'dt_q' => trim((string) $this->input->get('dt_q', TRUE)),
			'return_from' => trim((string) $this->input->get('return_from', TRUE)),
			'return_to' => trim((string) $this->input->get('return_to', TRUE)),
			// Order From/To removed from Order Status UI — never apply entry-date range here.
			'order_from' => '',
			'order_to' => '',
			'stage' => trim((string) $this->input->get('stage', TRUE)),
		);
		$has_lookup = ($status_filters['order_no'] !== ''
			|| $status_filters['naap_no'] !== ''
			|| $status_filters['mobile_no'] !== ''
			|| $status_filters['dt_q'] !== ''
			|| $status_filters['customer_name'] !== '');
		// First open / Clear: Stage = All (user can narrow if needed).
		if ($this->input->get('stage') === NULL)
		{
			$status_filters['stage'] = '';
		}
		// Order # / phone / naap / table Search must find the suit at any stage (not only Ready).
		if ($has_lookup && ($status_filters['order_no'] !== '' || $status_filters['naap_no'] !== '' || $status_filters['mobile_no'] !== '' || $status_filters['dt_q'] !== ''))
		{
			$status_filters['stage'] = '';
			$status_filters['return_from'] = '';
			$status_filters['return_to'] = '';
			$status_filters['order_from'] = '';
			$status_filters['order_to'] = '';
		}
		if ($status_filters['return_from'] === '' && $this->input->get('fromdate', TRUE) !== NULL && ! $has_lookup)
		{
			$status_filters['return_from'] = trim((string) $this->input->get('fromdate', TRUE));
		}
		if ($status_filters['return_to'] === '' && $this->input->get('todate', TRUE) !== NULL && ! $has_lookup)
		{
			$status_filters['return_to'] = trim((string) $this->input->get('todate', TRUE));
		}
		// Delivered tab: Stage does not apply; default last 30 days if no search/dates.
		if ($os_scope === 'delivered')
		{
			$status_filters['stage'] = '';
			if ( ! $has_lookup
				&& $status_filters['return_from'] === ''
				&& $status_filters['return_to'] === '')
			{
				$status_filters = $this->order_query->apply_default_order_date_filters($status_filters, 30, 120, FALSE);
			}
		}
		$data['status_filters'] = $status_filters;
		$data['os_scope'] = $os_scope;
		$data['cust_search'] = trim((string) $this->input->get('cust_search', TRUE));
		$data['fromdate'] = $status_filters['return_from'];
		$data['todate'] = $status_filters['return_to'];
		$data['stage'] = $status_filters['stage'];
		if ($os_scope === 'delivered')
		{
			$data['orderdata'] = $this->order_query->order_Status_D($status_filters);
		}
		else
		{
			$data['orderdata'] = $this->order_query->order_Status($status_filters);
		}
		if (empty($data['orderdata']))
		{
			$data['orderdata'] = array();
		}
				 	 $data['employee']= $this->master_data->get_dat(array('employee'));
 	$this->load->view('order_status',$data);
	}
	
	public function order_StatusD()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}

		// Legacy URL → unified Order Status with Delivered scope.
		$q = array('scope' => 'delivered');
		foreach (array('customer_name', 'order_no', 'naap_no', 'mobile_no', 'return_from', 'return_to', 'fromdate', 'todate') as $key)
		{
			$val = trim((string) $this->input->post_get($key, TRUE));
			if ($val !== '')
			{
				if ($key === 'fromdate' && empty($q['return_from']))
				{
					$q['return_from'] = $val;
				}
				elseif ($key === 'todate' && empty($q['return_to']))
				{
					$q['return_to'] = $val;
				}
				elseif ($key !== 'fromdate' && $key !== 'todate')
				{
					$q[$key] = $val;
				}
			}
		}
		redirect('transfer/order_Status?'.http_build_query($q));
	}
 
	public function transfer_table()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
			return;
		}

		 $get_id = $this->input->post('fromdept');
			 $data['DEPT'] = $this->input->post('fromdept');
		if($get_id == '')
		{
		 
			$get_id = $this->uri->segment(4);
			 $data['DEPT'] = $this->uri->segment(4);
		}
		$get_id = strtoupper(trim((string) $get_id));
		$data['DEPT'] = $get_id;
		if (!in_array($get_id, array('HO', 'CUT', 'HO2', 'STIT'), TRUE))
		{
			$get_id = 'HO';
			$data['DEPT'] = 'HO';
		}

		 // Lightweight page shell — rows load via server-side DataTables JSON.
		 $data["employee"]= $this->master_data->get_dat(array('employee'));
		 $data['orderdata'] = array();
		 $data['transfer_ssp_url'] = site_url('Transfer/transfer_table_data/'.$this->uri->segment(3).'/'.$get_id);
		 $data['transfer_list_from'] = (string) $this->uri->segment(3);
 	$this->load->view('transfer_table',$data);
	}

	/**
	 * DataTables server-side JSON for pending transfer lists.
	 */
	public function transfer_table_data()
	{
		$this->load->library('session');
		$this->load->helper(array('url', 'esc'));
		header('Content-Type: application/json; charset=UTF-8');
		if ( ! $this->session->userdata('user'))
		{
			echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array(), 'error' => 'session'));
			return;
		}

		$dept = strtoupper(trim((string) $this->uri->segment(4)));
		if ($dept === '')
		{
			$dept = strtoupper(trim((string) $this->input->get_post('dept', TRUE)));
		}
		if (!in_array($dept, array('HO', 'CUT', 'HO2', 'STIT'), TRUE))
		{
			echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array()));
			return;
		}

		$draw = (int) $this->input->post('draw');
		$start = (int) $this->input->post('start');
		$length = (int) $this->input->post('length');
		$search_arr = $this->input->post('search');
		$search = is_array($search_arr) && isset($search_arr['value']) ? trim((string) $search_arr['value']) : '';

		$show_bulk = ($dept === 'CUT' || $dept === 'STIT');
		// Map DataTables column index → SQL (checkbox shifts indices when bulk is on).
		$col_map_bulk = array(
			1 => 'order_mst.trnno',
			2 => 'employee.name',
			3 => 'order_mst.EDATE',
			4 => 'order_mst.RDATE',
			5 => 'customer.Phone',
			6 => 'customer.name',
			7 => 'customer.man_napno',
			8 => 'order_dtl.order_remarks',
		);
		$col_map_plain = array(
			0 => 'order_mst.trnno',
			1 => 'employee.name',
			2 => 'order_mst.EDATE',
			3 => 'order_mst.RDATE',
			4 => 'customer.Phone',
			5 => 'customer.name',
			6 => 'customer.man_napno',
			7 => 'order_dtl.order_remarks',
		);
		$col_map = $show_bulk ? $col_map_bulk : $col_map_plain;

		$order_by = '';
		$order_dir = 'ASC';
		$order_post = $this->input->post('order');
		if (is_array($order_post) && ! empty($order_post[0]) && is_array($order_post[0]))
		{
			$col_idx = isset($order_post[0]['column']) ? (int) $order_post[0]['column'] : -1;
			$dir_raw = isset($order_post[0]['dir']) ? strtolower((string) $order_post[0]['dir']) : 'asc';
			if (isset($col_map[$col_idx]))
			{
				$order_by = $col_map[$col_idx];
				$order_dir = ($dir_raw === 'desc') ? 'DESC' : 'ASC';
			}
		}

		$total = $this->order_query->count_pending_transfer_rows($dept, '');
		$filtered = ($search === '')
			? $total
			: $this->order_query->count_pending_transfer_rows($dept, $search);
		$rows = $this->order_query->get_pending_transfer_rows($dept, $start, $length, $search, $order_by, $order_dir);

		$list_from = (string) $this->uri->segment(3);
		$data_out = array();
		$sr_by_order = array();
		foreach ($rows as $row)
		{
			$order_no = (int) $row->ORDERNO;
			if (!isset($sr_by_order[$order_no]))
			{
				$sr_by_order[$order_no] = 1;
			}
			else
			{
				$sr_by_order[$order_no]++;
			}
			$line_sr = isset($row->ORDERSR) ? (int) $row->ORDERSR : (int) $row->SUITNO;
			$display_sr = $sr_by_order[$order_no];
			$transfer_id = (int) $row->TRANSFERID;
			$emp_name = isset($row->emp_name) ? trim((string) $row->emp_name) : '';

			$entry = $row->ORDERDATE;
			if ($entry === '' || $entry === NULL)
			{
				$entry = $row->TRANSFEREDATE;
			}
			$ret = $row->RORDERDATE;
			if ($ret === '' || $ret === NULL)
			{
				$ret = $row->RETURNDATE;
			}

			$cells = array();
			if ($show_bulk)
			{
				$cells[] = '<input type="checkbox" class="bulk-transfer-cb" data-trnno="'.$transfer_id.'" data-order="'.$order_no.'" data-sr="'.$line_sr.'">';
			}
			$cells[] = esc_html($order_no.'-'.$display_sr);
			$cells[] = esc_html($emp_name);
			$cells[] = ($entry !== '' && $entry !== NULL) ? date('d-m-Y', strtotime($entry)) : '';
			$cells[] = ($ret !== '' && $ret !== NULL) ? date('d-m-Y', strtotime($ret)) : '';
			$cells[] = esc_html((string) $row->Phone);
			$cells[] = esc_html((string) $row->name);
			$cells[] = esc_html((string) $row->man_napno);
			$cells[] = esc_html((string) $row->order_remarks);

			$view = '';
			if ($dept === 'CUT' || $dept === 'STIT')
			{
				$view = '<a class="btn btn-primary dz-btn--filled btn-sm" href="'.site_url('Transfer/transfer_Print/'.$transfer_id.'/'.$dept.'/'.$line_sr).'" target="_blank">View</a>';
			}
			$cells[] = $view;
			$cells[] = '<a class="btn btn-primary dz-btn--filled btn-sm" href="'.site_url('Transfer/transfer_order/'.$transfer_id.'/'.$dept.'/'.$line_sr).'">Transfer</a>';
			$ret_btn = '';
			if ($dept !== 'HO')
			{
				$ret_btn = '<a class="btn btn-primary dz-btn--filled btn-sm" href="'.site_url('Transfer/transfer_return/'.$transfer_id.'/'.$dept.'/'.$order_no.'/'.$line_sr.'/'.$list_from).'">Return</a>';
			}
			$cells[] = $ret_btn;
			$data_out[] = $cells;
		}

		echo json_encode(array(
			'draw' => $draw,
			'recordsTotal' => $total,
			'recordsFiltered' => $filtered,
			'data' => $data_out,
		));
	}
 
 
 	public function Transfer_Print()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		
	

	/*		$data3["embroidery"]= $this->master_data->get_dat(array('embroidery'));
			$data3["style"]= $this->master_data->get_dat(array('style'));
			$where5 = "order_mst.trnno = ".$this->master_data->get_max_id('trnno','order_mst')." and order_dtl.type = 'Kurta'";
			$data3['order'] = $this->order_query->get_order($where5);
			*/

			$data3["embroidery"]= $this->master_data->get_dat(array('embroidery'));
			$data3["style"]= $this->master_data->get_dat(array('style'));
			$data3["suit"]= $this->master_data->get_dat(array('suit'));
			$data3["employee"]= $this->master_data->get_dat(array('employee'));
			//$where5 = "order_mst.trnno = ".$this->uri->segment(3)." and order_dtl.type = 'Kurta'";
		//	 $where5 = '( transfer_mst.dept = "STIT" or transfer_mst.dept = "CUT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Kurta"
		//	 and transfer_mst.empno != "" ';
			 if($this->uri->segment(4) == 'HO2' )
			{
			 
			$where5 = '( transfer_mst.dept = "STIT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Kurta"
			 and transfer_mst.empno != "" ';
			}
			else
			{
			$where5 = '( transfer_mst.dept = "CUT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Kurta"
			 and transfer_mst.empno != "" ';
			}
			 
			 
		//	$where5 = 'transfer_mst.dept = "STIT" and transfer_mst.trnno = "'.$this->uri->segment(3).'"';
			$data3['order'] = $this->order_query->get_order_order_Detai3($where5);
		
						$data2["embroidery"]= $this->master_data->get_dat(array('embroidery'));
						
			$data2["style"]= $this->master_data->get_dat(array('style'));
			
		 
			
			 if($this->uri->segment(4) == 'HO2' )
			{
			 
			$where5 = '( transfer_mst.dept = "STIT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Pajama"
			 and transfer_mst.empno != "" ';
			}
			else
			{
			$where5 = '( transfer_mst.dept = "CUT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Pajama"
			 and transfer_mst.empno != "" ';
			}
			$data2['order'] = $this->order_query->get_order_order_Detai3($where5);
			$data2["employee"]= $this->master_data->get_dat(array('employee'));
			$data2["suit"]= $this->master_data->get_dat(array('suit'));
			$data1["embroidery"]= $this->master_data->get_dat(array('embroidery'));
			$data1["style"]= $this->master_data->get_dat(array('style'));
			$data1["employee"]= $this->master_data->get_dat(array('employee'));
				 if($this->uri->segment(4) == 'HO2' )
			{
			 
			$where5 = '( transfer_mst.dept = "STIT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Pent"
			 and transfer_mst.empno != "" ';
			}
			else
			{
			$where5 = '( transfer_mst.dept = "CUT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Pent"
			 and transfer_mst.empno != "" ';
			}
			
			$data1['order'] = $this->order_query->get_order_order_Detai3($where5);
			
			$data1["suit"]= $this->master_data->get_dat(array('suit'));
			$data0["embroidery"]= $this->master_data->get_dat(array('embroidery'));
			$data0["style"]= $this->master_data->get_dat(array('style'));
					 if($this->uri->segment(4) == 'HO2' )
			{
			 
			$where5 = '( transfer_mst.dept = "STIT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Shirt"
			 and transfer_mst.empno != "" ';
			}
			else
			{
			$where5 = '( transfer_mst.dept = "CUT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Shirt"
			 and transfer_mst.empno != "" ';
			}
			
			$data0["employee"]= $this->master_data->get_dat(array('employee'));
			$data0['order'] = $this->order_query->get_order_order_Detai3($where5);	
			 
			$data0["suit"]= $this->master_data->get_dat(array('suit'));
			$data01["embroidery"]= $this->master_data->get_dat(array('embroidery'));
			$data01["style"]= $this->master_data->get_dat(array('style'));
			$data01["employee"]= $this->master_data->get_dat(array('employee'));
					 if($this->uri->segment(4) == 'HO2' )
			{
			 
			$where5 = '( transfer_mst.dept = "STIT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Coat"
			 and transfer_mst.empno != "" ';
			}
			else
			{
			$where5 = '( transfer_mst.dept = "CUT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Coat"
			 and transfer_mst.empno != "" ';
			}
			
			
			$data01['order'] = $this->order_query->get_order_order_Detai3($where5);	
			 
			$data03["suit"]= $this->master_data->get_dat(array('suit'));
			$data03["embroidery"]= $this->master_data->get_dat(array('embroidery'));
			$data03["employee"]= $this->master_data->get_dat(array('employee'));
			$data03["style"]= $this->master_data->get_dat(array('style'));
						 if($this->uri->segment(4) == 'HO2' )
			{
			 
			$where5 = '( transfer_mst.dept = "STIT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Waistcoat"
			 and transfer_mst.empno != "" ';
			}
			else
			{
			$where5 = '( transfer_mst.dept = "CUT") and order_dtl.trnno = "'.$this->uri->segment(5).'" and order_dtl.type = "Waistcoat"
			 and transfer_mst.empno != "" ';
			}
			
			
			$data03['order'] = $this->order_query->get_order_order_Detai3($where5);	
			$data03["suit"]= $this->master_data->get_dat(array('suit'));
			 

		if (!empty($data3['order'])) {
			$data3['type'] = 'Kurta';

		$this->load->view('transfer_print',$data3);
		}
		if (!empty($data2['order'])) {
			$data2['type'] = 'Pajama';
		$this->load->view('transfer_print',$data2);
		}
		if (!empty($data1['order'])) {
			$data1['type'] = 'Pent';
		$this->load->view('transfer_print',$data1);
		}
		if (!empty($data0['order'])) {
			$data0['type'] = 'Shirt';
		$this->load->view('transfer_print',$data0);
		}
		if (!empty($data01['order'])) {
			$data01['type'] = 'Coat';
		$this->load->view('transfer_print',$data01);
		}
		if (!empty($data03['order'])) {
		 $data03['type'] = 'Waistcoat';
		 $this->load->view('transfer_print',$data03);
		}
	if(!$this->session->userdata('user')){
		redirect('Login'); 
		$this->load->view('transfer_print',$data03);
	}	

		
	}
	private function _transfer_deactivate_row($transfer_trnno, $from_dept, $order_id, $order_sr, $data1)
	{
		$deactivated = FALSE;

		if ($transfer_trnno > 0)
		{
			$this->db->reset_query();
			$this->db->where('trnno', $transfer_trnno);
			$this->db->where('dept', $from_dept);
			$this->db->where('status', 'Active');
			$this->db->update('transfer_mst', $data1);
			$deactivated = ($this->db->affected_rows() > 0);
		}

		if (!$deactivated)
		{
			$this->db->reset_query();
			$this->db->where('order_id', $order_id);
			$this->db->where('sr', $order_sr);
			$this->db->where('dept', $from_dept);
			$this->db->where('status', 'Active');
			$this->db->update('transfer_mst', $data1);
			$deactivated = ($this->db->affected_rows() > 0);
		}

		$this->db->reset_query();
		return $deactivated;
	}

	private function _clear_dashboard_summary_cache()
	{
		$this->dashboard_data->clear_dashboard_summary_cache();
	}

	private function _transfer_next_dept($from_dept)
	{
		if ($from_dept === 'HO')
		{
			return 'CUT';
		}
		if ($from_dept === 'CUT')
		{
			return 'HO2';
		}
		if ($from_dept === 'HO2')
		{
			return 'STIT';
		}
		return '';
	}

	/**
	 * @return array{ok:bool, error?:string, dept1?:string}
	 */
	private function _transfer_process_one($transfer_trnno, $from_dept, $order_sr, $order_id, $employee_account = '', $rate = '', $rdate = '')
	{
		$this->load->model('insert');

		$transfer_trnno = (int) $transfer_trnno;
		$order_sr = (int) $order_sr;
		$order_id = (int) $order_id;
		$dept1 = $this->_transfer_next_dept($from_dept);
		$stit_complete = ($from_dept === 'STIT');
		$allowed_from = array('HO', 'CUT', 'HO2', 'STIT');
		if ($transfer_trnno <= 0 || $order_sr <= 0 || $order_id <= 0 || !in_array($from_dept, $allowed_from, TRUE))
		{
			return array('ok' => FALSE, 'error' => 'invalid');
		}
		if (!$stit_complete && $dept1 === '')
		{
			return array('ok' => FALSE, 'error' => 'invalid');
		}

		$employee_account = trim((string) $employee_account);
		$rate = trim((string) $rate);
		if (!$stit_complete && ($dept1 === 'CUT' || $dept1 === 'STIT'))
		{
			if ($employee_account === '')
			{
				return array('ok' => FALSE, 'error' => 'employee_required');
			}
			if ( ! $this->employee_query->is_transfer_employee($employee_account, $from_dept))
			{
				return array('ok' => FALSE, 'error' => 'employee_invalid');
			}
		}

		if ($rdate === '')
		{
			$rdate = date('Y-m-d');
		}

		$data = array(
			'EDATE' => date('Y-m-d'),
			'order_id' => $order_id,
			'sr' => $order_sr,
			'empno' => $employee_account,
			'qty' => 1,
			'price' => $rate,
			'RDATE' => $rdate,
			'dept' => $dept1,
			'status' => 'Active',
		);

		if ($from_dept === 'STIT' || $from_dept === 'CUT')
		{
			$data1 = array(
				'EDATE' => date('Y-m-d'),
				'RDATE' => date('Y-m-d'),
				'status' => 'Deactive',
			);
		}
		else
		{
			$data1 = array(
				'status' => 'Deactive',
				'RDATE' => date('Y-m-d'),
			);
		}

		$deactivated = $this->_transfer_deactivate_row($transfer_trnno, $from_dept, $order_id, $order_sr, $data1);
		if (!$deactivated)
		{
			return array('ok' => FALSE, 'error' => 'not_found');
		}

		if ($dept1 === 'CUT' || $dept1 === 'STIT')
		{
			$jmst = array(
				'TRNDATE' => date('Y-m-d'),
				'ACCOUNT' => $employee_account,
				'ACCOUNT_TYPE' => 'EMPLOYEE',
			);
			$this->insert->insert_dat1($jmst, 'jmst');
			$jdtl = array();
			$jdtl['SR'] = $this->master_data->get_max_id('TRNNO', 'jmst');
			$jdtl['NARRATION'] = 'Employee Debit to assign task REF#'.$this->master_data->get_max_id('trnno', 'transfer_mst');
			$jdtl['DR'] = $rate;
			$jdtl['CR'] = 0;
			$this->insert->insert_dat1($jdtl, 'jdtl');
			$jdtl['SR'] = $this->master_data->get_max_id('TRNNO', 'jmst');
			$jdtl['NARRATION'] = 'New ALi Baba tailor credited by task asign REF#'.$this->master_data->get_max_id('trnno', 'transfer_mst');
			$jdtl['DR'] = 0;
			$jdtl['CR'] = $rate;
			$this->insert->insert_dat1($jdtl, 'jdtl');
		}

		if ($stit_complete)
		{
			$this->db->reset_query();
			return array('ok' => TRUE, 'dept1' => '', 'stit_only' => TRUE);
		}

		$this->db->reset_query();
		$this->db->where('sr', $order_sr);
		$this->db->where('dept', $dept1);
		$this->db->where('order_id', $order_id);
		$query = $this->db->get('transfer_mst');
		if ($query->num_rows() < 1)
		{
			$this->insert->insert_dat1($data, 'transfer_mst');
		}
		$this->insert->insert_dat1($data, 'transfer_mst_new');
		$this->db->reset_query();

		return array('ok' => TRUE, 'dept1' => $dept1);
	}

	public function process()
	{
		$this->load->helper('url');
		$this->load->library('session');
		if (!$this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}

		$dept = $this->uri->segment(4);
		$result = $this->_transfer_process_one(
			$this->uri->segment(3),
			$dept,
			$this->uri->segment(5),
			$this->input->post('order_id'),
			$this->input->post('EMPLOYEE'),
			$this->input->post('Rate'),
			$this->input->post('RDATE')
		);

		if (!$result['ok'])
		{
			if (isset($result['error']) && $result['error'] === 'employee_required')
			{
				$this->session->set_flashdata('transfer_error', t('transfer_select_employee'));
			}
			elseif (isset($result['error']) && $result['error'] === 'employee_invalid')
			{
				$this->session->set_flashdata('transfer_error', t('transfer_invalid_employee'));
			}
			redirect('Transfer/transfer_order/'.$this->uri->segment(3).'/'.$this->uri->segment(4).'/'.$this->uri->segment(5));
			return;
		}

		$this->_clear_dashboard_summary_cache();

		if (!empty($result['stit_only']))
		{
			$this->_notify_order_ready((int) $this->input->post('order_id'), 'transfer_process');
			redirect('Transfer/transfer_table/'.$this->uri->segment(3).'/'.$dept);
			return;
		}

		$dept1 = $result['dept1'];
		// Print slip only after Booking → Cutting. HO2 → Stitching should stay on the list.
		if ($dept1 === 'CUT')
		{
			redirect('Transfer/transfer_Print/'.$this->uri->segment(3).'/'.$dept.'/'.$this->uri->segment(5));
			return;
		}

		redirect('Transfer/transfer_table/'.$this->uri->segment(3).'/'.$dept.'/'.$this->uri->segment(5));
	}

	public function transfer_process_bulk()
	{
		$this->load->helper('url');
		$this->load->library('session');
		if (!$this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}

		$dept = trim((string) $this->input->post('dept', TRUE));
		$list_from = trim((string) $this->input->post('list_from', TRUE));
		if (!in_array($list_from, array('CUT', 'HO', 'HO2', 'STIT'), TRUE))
		{
			$list_from = $dept;
		}

		$rdate = date('Y-m-d');
		$employee = trim((string) $this->input->post('bulk_employee', TRUE));
		$rate = trim((string) $this->input->post('bulk_rate', TRUE));
		$raw = $this->input->post('transfer_items', FALSE);
		$items = json_decode((string) $raw, TRUE);
		$done = 0;
		$failed = 0;
		$skipped_employee = 0;

		if (is_array($items))
		{
			foreach ($items as $item)
			{
				if (!is_array($item))
				{
					continue;
				}
				$trnno = isset($item['trnno']) ? (int) $item['trnno'] : 0;
				$order = isset($item['order']) ? (int) $item['order'] : 0;
				$sr = isset($item['sr']) ? (int) $item['sr'] : 0;
				$result = $this->_transfer_process_one($trnno, $dept, $sr, $order, $employee, $rate, $rdate);
				if (!empty($result['ok']))
				{
					$done++;
					if ($dept === 'STIT' && !empty($result['stit_only']))
					{
						$this->_notify_order_ready($order, 'transfer_bulk');
					}
				}
				elseif (isset($result['error']) && $result['error'] === 'employee_required')
				{
					$skipped_employee++;
				}
				else
				{
					$failed++;
				}
			}
		}

		if ($done > 0)
		{
			$this->session->set_flashdata('bulk_transfer_count', $done);
			$this->_clear_dashboard_summary_cache();
		}
		if ($skipped_employee > 0)
		{
			$this->session->set_flashdata('transfer_error', t('transfer_select_employee'));
		}
		elseif ($done === 0)
		{
			$this->session->set_flashdata('transfer_error', t('transfer_bulk_transfer_failed'));
		}
		redirect('Transfer/transfer_table/'.$list_from.'/'.$dept);
	}
				
				
		
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
	private function _notify_order_ready($order_id, $trigger_source)
	{
		$order_id = (int) $order_id;
		if ($order_id <= 0)
		{
			return;
		}

		$this->load->library('notification_service');
		$res = $this->notification_service->trigger('order_ready', $order_id, array(
			'trigger_source' => (string) $trigger_source,
			'defer_dispatch' => TRUE,
		));
		$this->notification_service->stash_whatsapp_flash($res);
	}

	private function _transfer_return_previous_dept($dept)
	{
		if ($dept === 'CUT')
		{
			return 'HO';
		}
		if ($dept === 'HO2')
		{
			return 'CUT';
		}
		if ($dept === 'STIT')
		{
			return 'HO2';
		}
		return '';
	}

	private function _transfer_return_one($trnno, $dept, $order_id, $ordersr)
	{
		$trnno = (int) $trnno;
		$order_id = (int) $order_id;
		$ordersr = (int) $ordersr;
		$allowed = array('CUT', 'HO2', 'STIT');
		if ($trnno <= 0 || $order_id <= 0 || $ordersr <= 0 || !in_array($dept, $allowed, TRUE))
		{
			return FALSE;
		}
		$prev_dept = $this->_transfer_return_previous_dept($dept);
		if ($prev_dept === '')
		{
			return FALSE;
		}
		$data = array('status' => 'Active');
		$where = array(
			'order_id' => $order_id,
			'dept' => $prev_dept,
			'sr' => $ordersr,
		);
		$this->db->where($where);
		$this->db->update('transfer_mst', $data);
		$this->db->where('trnno', $trnno);
		$this->db->delete('transfer_mst');
		return TRUE;
	}

	public function transfer_return()
	{
		$this->load->helper('url');
		$this->load->library('session');
		if (!$this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}
		$this->load->model('insert');
		$trnno = $this->uri->segment(3);
		$dept = $this->uri->segment(4);
		$order = $this->uri->segment(5);
		$ordersr = $this->uri->segment(6);
		$list_from = in_array($this->uri->segment(7), array('CUT', 'HO', 'HO2', 'STIT'), TRUE) ? $this->uri->segment(7) : $dept;
		$this->_transfer_return_one($trnno, $dept, $order, $ordersr);
		$this->_clear_dashboard_summary_cache();
		redirect('Transfer/transfer_table/'.$list_from.'/'.$dept);
	}

	public function transfer_return_bulk()
	{
		$this->load->helper('url');
		$this->load->library('session');
		if (!$this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}
		$dept = trim((string) $this->input->post('dept', TRUE));
		$list_from = trim((string) $this->input->post('list_from', TRUE));
		if (!in_array($list_from, array('CUT', 'HO', 'HO2', 'STIT'), TRUE))
		{
			$list_from = $dept;
		}
		$raw = $this->input->post('return_items', FALSE);
		$items = json_decode((string) $raw, TRUE);
		$done = 0;
		if (is_array($items))
		{
			foreach ($items as $item)
			{
				if (!is_array($item))
				{
					continue;
				}
				$trnno = isset($item['trnno']) ? $item['trnno'] : 0;
				$order = isset($item['order']) ? $item['order'] : 0;
				$sr = isset($item['sr']) ? $item['sr'] : 0;
				if ($this->_transfer_return_one($trnno, $dept, $order, $sr))
				{
					$done++;
				}
			}
		}
		if ($done > 0)
		{
			$this->_clear_dashboard_summary_cache();
		}
		$this->session->set_flashdata('bulk_return_count', $done);
		redirect('Transfer/transfer_table/'.$list_from.'/'.$dept);
	}
				
 
}

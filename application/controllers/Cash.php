<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cash extends MY_Controller {

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
	public function Total_wages()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  $data["total_wages"]= $this->order_query->total_wages('where employee.empno = 1');
		
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('cash_employee_wage',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
		public function customer_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$emp = $this->uri->segment(4);
	  $export = $this->input->get('export', TRUE);
	  $export = $export !== NULL ? strtolower(trim((string) $export)) : '';
	  $data["export_mode"] = ($export === 'excel' || $export === 'pdf') ? $export : '';
	  $data["asdate"] = $date;
	  $data["customer_id"] = $emp;
	  $data["opening"] = 0;
	  $data["payment"] = array();
	  $data["customer"] = array();
	  $data["customer_info"] = NULL;
	  if ($date !== '' && $emp !== '')
	  {
		  $data["customer_info"] = $this->cash_ledger->get_customer_info($emp);
		  $data["opening"] = $this->cash_ledger->get_opeing($date,$emp);
		  $data["payment"] = $this->cash_ledger->get_data_customer($date,$emp);
	  }
	  else
	  {
		  $data["customer"] = $this->master_data->get_dat(array('customer'));
	  }
	  if ($data["export_mode"] === 'excel')
	  {
		  $filename = 'customer-ledger-'.$date.'-'.$emp.'.xls';
		  header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
		  header('Content-Disposition: attachment; filename="'.$filename.'"');
		  header('Pragma: no-cache');
		  header('Expires: 0');
	  }
 	$this->load->view('customer_ledger',$data);

		
	}
 
	
			 
	public function customer_ledger_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('Customer');
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);
	redirect('Cash/customer_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('Customer').'');

		
	}
	public function reprtcustomer_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["customer"]= $this->master_data->get_dat(array('customer'));
		 
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('customer_ledger',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
	
	
	
	
	
	// employee ledger
		public function employee_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		$emp = $this->uri->segment(5);
	  $export = $this->input->get('export', TRUE);
	  $export = $export !== NULL ? strtolower(trim((string) $export)) : '';
	  $data["export_mode"] = ($export === 'excel' || $export === 'pdf') ? $export : '';
	  $data["fromdate"] = $date;
	  $data["todate"] = $date1;
	  $data["employee_id"] = $emp;
	  $data["employee_name"] = '';
	  $data["empopening"] = 0;
	  $data["payment"] = array();
	  $data["emp_status_filter"] = $this->employee_query->normalize_employee_status_filter($this->input->get('emp_status', TRUE));
	  $data["employee"] = $this->employee_query->get_employees_for_ledger($data["emp_status_filter"]);
	  foreach ($data["employee"] as $employees)
	  {
		  if ((string) $employees->empno === (string) $emp)
		  {
			  $data["employee_name"] = $employees->name;
			  break;
		  }
	  }
	  $data["employee_record_status"] = '';
	  if ($emp !== '')
	  {
		  $emp_row = $this->employee_query->get_employee_by_empno($emp);
		  if ($emp_row !== NULL)
		  {
			  if ($data["employee_name"] === '')
			  {
				  $data["employee_name"] = $emp_row->name;
			  }
			  $st = isset($emp_row->status) ? (string) $emp_row->status : '';
			  $data["employee_record_status"] = ($st === 'Inactive') ? 'Inactive' : 'Active';
		  }
	  }
	  if ($date !== '' && $date1 !== '' && $emp !== '')
	  {
		  $data["empopening"] = $this->cash_ledger->get_opeing_emp($date,$emp);
		  $data["payment"] = $this->cash_ledger->get_data_employee($date,$date1,$emp);
	  }
	  if ($data["export_mode"] === 'excel')
	  {
		  $filename = 'employee-ledger-'.$date.'-'.$date1.'.xls';
		  header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
		  header('Content-Disposition: attachment; filename="'.$filename.'"');
		  header('Pragma: no-cache');
		  header('Expires: 0');
	  }
 	$this->load->view('employee_ledger',$data);

		
	}
	//all)delivery_order.php
 
 
 public function all_delivery_order()
	{
	
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		 
		//$type = $this->input->post('fromdate');
//		$emp = $this->uri->segment(5);
//	  $data["empopening"]= $this->cash_ledger->get_opeing_emp($date,$emp);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		//$data["employee"]= $this->master_data->get_dat(array('employee'));
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	 // $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= ($date !== '' && $date1 !== '') ? $this->cash_ledger->get_data_report_del_order($date,$date1) : array();
		
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('all_delivery_order',$data);

		
	}
 
 
		public function all_Sale_employee_wise()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		$rtype = (string) $this->uri->segment(5);
	  $data["opening"]= 0;
	  $data["payment"]= ($date !== '' && $date1 !== '') ? $this->cash_ledger->get_data_report_all_month($date,$date1,'',$rtype) : array();
		
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('all_Sale_employee_wise',$data);

		
	}
			 
	public function employee_ledger_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	  $emp_status = $this->employee_query->normalize_employee_status_filter($this->input->post('emp_status', TRUE));
	  $url = 'Cash/employee_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('employee');
	  if ($emp_status !== 'All')
	  {
		  $url .= '?emp_status='.rawurlencode($emp_status);
	  }
	  redirect($url);
	}
public function all_del_ord_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
		 $fromdate = $this->input->post('fromdate');
		
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/all_delivery_order/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('rtype').'');

		
	}

public function all_sale_employee_sie_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
		 $fromdate = $this->input->post('fromdate');
		
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/all_Sale_employee_wise/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('rtype').'');

		
	}

	public function reprtemployee_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	  $data["emp_status_filter"] = $this->employee_query->normalize_employee_status_filter($this->input->get('emp_status', TRUE));
	  $data["employee"] = $this->employee_query->get_employees_for_ledger($data["emp_status_filter"]);
 	$this->load->view('employee_ledger',$data);
	}


	public function reprtall_delivery_order()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->master_data->get_dat(array('employee'));
		 
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('all_delivery_order',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	}

	
	public function reprtall_sale_employee_wise()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->master_data->get_dat(array('employee'));
		 
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('all_Sale_employee_wise',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	}
		
 
	
	
	public function customer_pending_ledger_pr()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
		 $fromdate = $this->input->post('fromdate');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/customer_pending_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'');

		
	}
	public function reprtcustomer_pending()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  $data["fromdate"] = '';
	  $data["todate"] = '';
	  $data["payment"] = array();
	  $data["export_mode"] = '';
		 
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('customer_pending_ledger_pro',$data);

		
	}
	
	
	public function customer_pending_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		//$emp = $this->uri->segment(5);
	  $export = $this->input->get('export', TRUE);
	  $export = $export !== NULL ? strtolower(trim((string) $export)) : '';
	  $data["export_mode"] = ($export === 'excel' || $export === 'pdf') ? $export : '';
	  $data["fromdate"] = $date;
	  $data["todate"] = $date1;
	//  $data["CASHopening"]= $this->cash_ledger->get_opeing_cash($date);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		//$data["employee"]= $this->master_data->get_dat(array('employee'));
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	//  $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= ($date !== '' && $date1 !== '') ? $this->cash_ledger->get_pending_cash($date,$date1) : array();
	 	// $data["payment"] = array_unique($data["payment"]);
	  if ($data["export_mode"] === 'excel')
	  {
		  $filename = 'pending-customer-ledger-'.$date.'-'.$date1.'.xls';
		  header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
		  header('Content-Disposition: attachment; filename="'.$filename.'"');
		  header('Pragma: no-cache');
		  header('Expires: 0');
	  }
 
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('customer_pending_ledger_pro',$data);

		
	}
 
	
	
	
	
	
	public function cash_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		//$emp = $this->uri->segment(5);
	  $export = $this->input->get('export', TRUE);
	  $export = $export !== NULL ? strtolower(trim((string) $export)) : '';
	  $data["export_mode"] = ($export === 'excel' || $export === 'pdf') ? $export : '';
	  $data["fromdate"] = $date;
	  $data["todate"] = $date1;
	  $load_detail = strtolower((string) $this->input->get('detail')) === 'all'
		  || $export === 'excel';
	  if ($date !== '' && $date1 !== '')
	  {
		  $data['summary'] = $this->cash_ledger->get_cash_ledger_summary($date, $date1);
		  $data['CASHopening'] = $data['summary']['opening'];
		  $data['payment'] = $load_detail ? $this->cash_ledger->get_cash_ledger_detail($date, $date1) : array();
		  $data['load_detail_inline'] = $load_detail;
	  }
	  else
	  {
		  $data['summary'] = NULL;
		  $data['CASHopening'] = 0;
		  $data['payment'] = array();
		  $data['load_detail_inline'] = FALSE;
	  }
	//	 $data["payment"] = array_unique($data["payment"]);
	  if ($data["export_mode"] === 'excel')
	  {
		  $filename = 'cash-ledger-'.$date.'-'.$date1.'.xls';
		  header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
		  header('Content-Disposition: attachment; filename="'.$filename.'"');
		  header('Pragma: no-cache');
		  header('Expires: 0');
	  }

	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('cash_ledger',$data);

		
	}
 
	
			 
	public function cash_ledger_data()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->library('session');
		if (!$this->session->userdata('user'))
		{
			$this->output->set_status_header(401)->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'message' => 'Session expired.')));
			return;
		}
		$from = trim((string) $this->input->get_post('from'));
		$to = trim((string) $this->input->get_post('to'));
		$scope_from = trim((string) $this->input->get_post('scope_from'));
		$scope_to = trim((string) $this->input->get_post('scope_to'));
		$mode = strtolower(trim((string) $this->input->get_post('mode')));
		$day = trim((string) $this->input->get_post('day'));
		$source = trim((string) $this->input->get_post('source'));

		if ($from === '' || $to === '')
		{
			$this->output->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'message' => 'From and to dates are required.')));
			return;
		}

		$query_from = ($scope_from !== '') ? $scope_from : $from;
		$query_to = ($scope_to !== '') ? $scope_to : $to;
		if ($query_from < $from) { $query_from = $from; }
		if ($query_to > $to) { $query_to = $to; }
		if ($query_from > $query_to)
		{
			$query_from = $from;
			$query_to = $to;
		}

		$summary_modes = array('daily', 'month', 'quarter', 'halfyear', 'year');
		$payload = array('ok' => TRUE, 'from' => $from, 'to' => $to, 'mode' => $mode);
		if (in_array($mode, $summary_modes, TRUE))
		{
			$rows = $this->cash_ledger->get_cash_ledger_grouped_summary($query_from, $query_to, $mode);
			$totals = $this->cash_ledger->get_cash_ledger_detail_totals($query_from, $query_to, '', '');
			$payload['rows'] = array();
			foreach ($rows as $row)
			{
				$receipt = (float) $row->receipt_total;
				$payment = (float) $row->payment_total;
				$payload['rows'][] = array(
					'period_key' => (string) $row->period_key,
					'period_label' => (string) $row->period_label,
					'period_from' => (string) $row->period_from,
					'period_to' => (string) $row->period_to,
					'date_label' => (string) $row->period_label,
					'receipt_total' => $receipt,
					'payment_total' => $payment,
					'period_net' => $receipt - $payment,
					'line_count' => (int) $row->line_count,
					'drill_next' => (string) $row->drill_next,
				);
			}
			$payload['receipt_total'] = $totals['receipt_total'];
			$payload['payment_total'] = $totals['payment_total'];
			$payload['period_net'] = $totals['period_net'];
			$payload['line_count'] = $totals['line_count'];
		}
		elseif ($mode === 'detail')
		{
			$rows = $this->cash_ledger->get_cash_ledger_detail($query_from, $query_to, $day, $source);
			$totals = $this->cash_ledger->get_cash_ledger_detail_totals($query_from, $query_to, $day, $source);
			$payload['rows'] = array();
			foreach ($rows as $row)
			{
				$amount = (float) $row->RECEIPT;
				$is_receipt = !empty($row->is_receipt);
				$payload['rows'][] = array(
					'date' => $row->ENTRYDATE,
					'date_label' => date('d-m-Y', strtotime($row->ENTRYDATE)),
					'narration' => (string) $row->NARRATION,
					'source' => isset($row->SOURCE) ? (string) $row->SOURCE : 'other',
					'receipt' => $is_receipt ? $amount : 0,
					'payment' => $is_receipt ? 0 : $amount,
				);
			}
			$payload['receipt_total'] = $totals['receipt_total'];
			$payload['payment_total'] = $totals['payment_total'];
			$payload['period_net'] = $totals['period_net'];
			$payload['line_count'] = $totals['line_count'];
			$payload['opening'] = $this->cash_ledger->get_opeing_cash($from);
		}
		elseif ($mode === 'summary' || $mode === '')
		{
			$payload['summary'] = $this->cash_ledger->get_cash_ledger_summary($from, $to);
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($payload));
	}

	public function cash_ledger_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
		 $fromdate = $this->input->post('fromdate');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/cash_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('todate'));

		
	}
	public function reprtcash_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login'); 
		}
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  $data["fromdate"] = '';
	  $data["todate"] = '';
	  $data["CASHopening"] = 0;
	  $data["payment"] = array();
	  $data["export_mode"] = '';
		 
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('cash_ledger',$data);

		
	}
	
	
	//
	
	
		public function index()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		$data['emp_status_filter'] = 'Active';
		$emp_status_raw = $this->input->get('emp_status', TRUE);
		if ($emp_status_raw !== NULL && $emp_status_raw !== '')
		{
			$data['emp_status_filter'] = $this->employee_query->normalize_employee_status_filter($emp_status_raw);
		}
		$data['employee'] = $this->employee_query->get_employees_for_ledger($data['emp_status_filter']);
		$data['customer']= $this->customer_query->get_cash_customer_options();
 	$this->load->view('cash',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
public function process()  
		{ 
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			if ($this->input->is_ajax_request()) {
				$this->output
					->set_content_type('application/json')
					->set_output(json_encode(array('success' => false, 'message' => 'Session expired. Please login again.')));
				return;
			}
			redirect('Login'); 
		}
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         $salary = 0;
		if($this->input->post('type') == '7')
		{
			$salary = $this->input->post('paid') * -1;
		}
		$salary = $this->input->post('paid');
		$data = array(
				'EDATE' => date('Y-m-d'),
				'type' => $this->input->post('type'),
				'emp_id' => $this->input->post('Employee'),
				'customer_id' => $this->input->post('Customer'),
				'amount' => $salary,
				'narration' => $this->input->post('Remarks'),
				'order_no' => $this->input->post('order_number'),
	 
  
);
				
				$inserted = $this->db->insert('cash', $data);
				if ($inserted)
				{
					$this->cash_ledger->invalidate_cash_ledger_opening_cache(isset($data['EDATE']) ? $data['EDATE'] : date('Y-m-d'));
				}
				if ($this->input->is_ajax_request()) {
					$this->output
						->set_content_type('application/json')
						->set_output(json_encode(array(
							'success' => (bool) $inserted,
							'message' => $inserted ? 'Cash Payment/Receipt Generated' : 'Unable to save entry'
						)));
					return;
				}
				if ($inserted) {
					$this->session->set_flashdata('suss', 'yes');
				}
				redirect('Cash');
		}
				
				
		
	public function process_status()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}

		$fromdate = trim((string) $this->input->post('fromdate', TRUE));
		$todate = trim((string) $this->input->post('todate', TRUE));
		if ($fromdate === '')
		{
			$fromdate = (string) $this->uri->segment(3);
		}
		if ($todate === '')
		{
			$todate = (string) $this->uri->segment(4);
		}

		$dmst_trnno = (int) $this->input->post('dmst_trnno', TRUE);
		$customer_id = trim((string) $this->input->post('customer_id', TRUE));
		$redirect_url = 'Cash/customer_pending_ledger/'.$fromdate.'/'.$todate;

		if ($dmst_trnno <= 0)
		{
			$dmst_trnno = (int) $this->uri->segment(5);
		}
		if ($customer_id === '')
		{
			$customer_id = (string) $this->uri->segment(6);
		}

		if ($dmst_trnno <= 0 || $fromdate === '' || $todate === '')
		{
			$this->session->set_flashdata('transfer_error', t('report_pending_payment_invalid'));
			redirect($redirect_url);
			return;
		}
		$dmst_row = $this->cash_ledger->get_dmst_pending_row($dmst_trnno);
		if ($dmst_row === NULL)
		{
			$this->session->set_flashdata('transfer_error', t('report_pending_payment_not_found'));
			redirect($redirect_url);
			return;
		}

		$balance = round((float) $dmst_row->balance, 2);
		$pay_amount = round((float) $this->input->post('pay_amount', TRUE), 2);
		$discount_amount = round((float) $this->input->post('discount_amount', TRUE), 2);

		// Legacy GET link: full balance as payment.
		if ($this->input->post('dmst_trnno', TRUE) === NULL && $this->uri->segment(7) !== NULL && $this->uri->segment(7) !== '')
		{
			$pay_amount = round((float) $this->uri->segment(7), 2);
			$discount_amount = 0;
		}

		if ($pay_amount < 0 || $discount_amount < 0)
		{
			$this->session->set_flashdata('transfer_error', t('report_pending_payment_invalid'));
			redirect($redirect_url);
			return;
		}
		if ($pay_amount + $discount_amount <= 0)
		{
			$this->session->set_flashdata('transfer_error', t('report_pending_payment_zero'));
			redirect($redirect_url);
			return;
		}
		if ($pay_amount - $balance > 0.009
			|| $discount_amount - $balance > 0.009
			|| $discount_amount - ($balance - $pay_amount) > 0.009
			|| $pay_amount + $discount_amount - $balance > 0.009)
		{
			$this->session->set_flashdata('transfer_error', t('report_pending_payment_over'));
			redirect($redirect_url);
			return;
		}

		if ($customer_id === '')
		{
			$customer_id = (string) $dmst_row->customer_id;
		}

		$remaining = round($balance - $pay_amount - $discount_amount, 2);
		$is_settled = ($remaining <= 0.009);
		$paid_today = date('Y-m-d');

		$this->db->trans_start();

		$dmst_update = array(
			'balance' => $is_settled ? 0 : $remaining,
		);
		if ($is_settled)
		{
			$dmst_update['paid_Date'] = $paid_today;
			$dmst_update['paidstatus'] = 'YES';
		}
		$this->db->where('trnno', $dmst_trnno);
		$this->db->update('dmst', $dmst_update);

		$cash_trnno = 0;
		if ($pay_amount > 0)
		{
			$narration = 'Received From Customer';
			if ($discount_amount > 0)
			{
				$narration .= ' (Discount: '.number_format($discount_amount, 2).')';
			}
			if ( ! $is_settled)
			{
				$narration .= ' (Partial, Balance: '.number_format($remaining, 2).')';
			}

			$cash_data = array(
				'EDATE' => $paid_today,
				'type' => '2',
				'customer_id' => $customer_id,
				'amount' => $pay_amount,
				'narration' => $narration,
				'order_no' => (string) $dmst_trnno,
			);
			$this->db->insert('cash', $cash_data);
			$cash_trnno = (int) $this->db->insert_id();
			if ($cash_trnno > 0)
			{
				$this->cash_ledger->invalidate_cash_ledger_opening_cache($paid_today);
			}
		}

		if ($cash_trnno > 0 && $this->cash_ledger->dmst_has_paid_cash_trnno_column())
		{
			$this->db->where('trnno', $dmst_trnno);
			$this->db->update('dmst', array('paid_cash_trnno' => $cash_trnno));
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() !== FALSE)
		{
			$this->dashboard_data->clear_dashboard_summary_cache();
			$this->session->set_flashdata('suss', 'yes');
			if ($is_settled)
			{
				$this->session->set_flashdata('pending_payment_msg', t('report_pending_paid_full'));
			}
			else
			{
				$this->session->set_flashdata('pending_payment_msg', sprintf(t('report_pending_paid_partial'), number_format($remaining, 2)));
			}
		}
		else
		{
			$this->session->set_flashdata('transfer_error', t('report_pending_payment_failed'));
		}

		redirect($redirect_url);
	}

		
		
		
		
		
		
		
		
		
		
		
		
		public function reprtexp_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  $data["payment"]= array();
		 
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('exp_sheet',$data);

		
	}
	



	
	
 public function exp_ledger_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
		 $fromdate = $this->input->post('fromdate');
		 $typ = $this->input->post('type');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/exp_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('type').'');

		
	}
		
		public function rep_customer_sale()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
	
		//$emp = $this->uri->segment(5);
	  //$data["CASHopening"]= $this->cash_ledger->get_opeing_cash($date);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
//		$data["employee"]= $this->master_data->get_dat(array('employee'));
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	//  $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= ($date !== '' && $date1 !== '') ? $this->order_query->get_data_customer_sale($date,$date1) : array();
	
	//	 $data["payment"] = array_unique($data["payment"]);

	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('customer_wise_sale_report',$data);

		
	}
	
	public function customer_sale_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
		 $fromdate = $this->input->post('fromdate');
		 //$typ = $this->input->post('type');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/rep_customer_sale/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'');

		
	}

	private function normalize_financial_group($group)
	{
		$group = strtolower(trim((string) $group));
		return $group === 'monthly' ? 'monthly' : 'daily';
	}

	public function reprtfinancial_dashboard()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		$data["fromdate"] = '';
		$data["todate"] = '';
		$data["group"] = 'daily';
		$data["summary"] = array();
		$data["rows"] = array();
		$this->load->view('financial_dashboard_report',$data);
	}

	public function financial_dashboard_pro()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		redirect('Cash/financial_dashboard/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->normalize_financial_group($this->input->post('group')).'');
	}

	public function financial_dashboard()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		$date = (string) $this->uri->segment(3);
		$date1 = (string) $this->uri->segment(4);
		$group = $this->normalize_financial_group($this->uri->segment(5));
		$data["fromdate"] = $date;
		$data["todate"] = $date1;
		$data["group"] = $group;
		$data["summary"] = ($date !== '' && $date1 !== '') ? $this->dashboard_data->get_financial_dashboard_summary($date,$date1) : array();
		$data["rows"] = ($date !== '' && $date1 !== '') ? $this->dashboard_data->get_financial_grouped_comparison($date,$date1,$group) : array();
		$this->load->view('financial_dashboard_report',$data);
	}

	public function reprtsales_expense_compare()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		$data["fromdate"] = '';
		$data["todate"] = '';
		$data["group"] = 'daily';
		$data["rows"] = array();
		$this->load->view('sales_expense_compare_report',$data);
	}

	public function sales_expense_compare_pro()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		redirect('Cash/sales_expense_compare/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->normalize_financial_group($this->input->post('group')).'');
	}

	public function sales_expense_compare()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		$date = (string) $this->uri->segment(3);
		$date1 = (string) $this->uri->segment(4);
		$group = $this->normalize_financial_group($this->uri->segment(5));
		$data["fromdate"] = $date;
		$data["todate"] = $date1;
		$data["group"] = $group;
		$data["rows"] = ($date !== '' && $date1 !== '') ? $this->dashboard_data->get_financial_grouped_comparison($date,$date1,$group) : array();
		$this->load->view('sales_expense_compare_report',$data);
	}

	public function reprtcash_flow_receivable()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		$data["fromdate"] = '';
		$data["todate"] = '';
		$data["group"] = 'daily';
		$data["opening"] = 0;
		$data["rows"] = array();
		$this->load->view('cash_flow_receivable_report',$data);
	}

	public function cash_flow_receivable_pro()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		redirect('Cash/cash_flow_receivable/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->normalize_financial_group($this->input->post('group')).'');
	}

	public function cash_flow_receivable()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		$date = (string) $this->uri->segment(3);
		$date1 = (string) $this->uri->segment(4);
		$group = $this->normalize_financial_group($this->uri->segment(5));
		$data["fromdate"] = $date;
		$data["todate"] = $date1;
		$data["group"] = $group;
		$data["opening"] = ($date !== '') ? $this->cash_ledger->get_opeing_cash($date) : 0;
		$data["rows"] = ($date !== '' && $date1 !== '') ? $this->dashboard_data->get_financial_cash_flow($date,$date1,$group) : array();
		$this->load->view('cash_flow_receivable_report',$data);
	}
	
	
	public function exp_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		$type = $this->uri->segment(5);
		//$emp = $this->uri->segment(5);
	  //$data["CASHopening"]= $this->cash_ledger->get_opeing_cash($date);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
//		$data["employee"]= $this->master_data->get_dat(array('employee'));
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	//  $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= ($date !== '' && $date1 !== '') ? $this->cash_ledger->get_data_exp($date,$date1,$type) : array();
	
	//	 $data["payment"] = array_unique($data["payment"]);

	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('exp_sheet',$data);

		
	}
 
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class employee_rep extends MY_Controller {

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
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$emp = $this->uri->segment(4);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= $this->cash_ledger->get_data_customer($date,$emp);
		  $data["customer"]= $this->master_data->get_dat(array('customer'));
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('customer_ledger',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
 
	
			 
	public function customer_ledger_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["customer"]= $this->master_data->get_dat(array('customer'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('Customer');
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);
	redirect('Cash/customer_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('Customer').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
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
	
	
	
	
		public function booking_ledreport()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		$data['payment'] = ($date !== '' && $date1 !== '') ? $this->order_query->get_booking_status_report($date, $date1) : array();
 	$this->load->view('rep_booking',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	}
	
	
	// employee ledger
		public function employee_ledreport()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		$data['payment'] = ($date !== '' && $date1 !== '') ? $this->cash_ledger->get_data_employee_summary($date, $date1) : array();
 	$this->load->view('employee_report',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	}
	//all)delivery_order.php
 
 
 public function all_delivery_order()
	{
	
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		 
		//$type = $this->input->post('fromdate');
//		$emp = $this->uri->segment(5);
//	  $data["empopening"]= $this->cash_ledger->get_opeing_emp($date);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		//$data["employee"]= $this->master_data->get_dat(array('employee'));
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	 // $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= $this->cash_ledger->get_data_report_del_order($date,$date1);
		
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('all_delivery_order',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
 
 
		public function all_Sale_employee_wise()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		 
		//$type = $this->input->post('fromdate');
//		$emp = $this->uri->segment(5);
//	  $data["empopening"]= $this->cash_ledger->get_opeing_emp($date,$emp);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		$data["employee"]= $this->master_data->get_dat(array('employee'));
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= $this->cash_ledger->get_data_report_all_month($date,$date1,$emp);
		
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('all_Sale_employee_wise',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
			 
	public function employee_report()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
	   
	  $this->load->model("employee_report_MD");	
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->employee_report_MD->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('employee_rep/employee_ledreport/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('employee').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	public function booking_view()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
	   
	  $this->load->model("employee_report_MD");	
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->employee_report_MD->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('employee_rep/rep_booking/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('employee').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	public function booking()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
	   
	  $this->load->model("employee_report_MD");	
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->employee_report_MD->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('employee_rep/booking_ledreport/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('employee').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
public function all_del_ord_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->master_data->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
		
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/all_delivery_order/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('rtype').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}

public function all_sale_employee_sie_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->master_data->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
		
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/all_Sale_employee_wise/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('rtype').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}

	public function reprtemployee_report()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
	   
	  $this->load->model("employee_report_MD");	
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->employee_report_MD->get_dat(array('employee'));
		 
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('employee_report',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
	}
	public function booking_rep()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
	   
	  $this->load->model("employee_report_MD");	
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->employee_report_MD->get_dat(array('employee'));
		 
	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('rep_booking',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	
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
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->master_data->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/customer_pending_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	public function reprtcustomer_pending()
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
 	$this->load->view('customer_pending_ledger_pro',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
	public function customer_pending_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		//$emp = $this->uri->segment(5);
	//  $data["CASHopening"]= $this->cash_ledger->get_opeing_cash($date);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		//$data["employee"]= $this->master_data->get_dat(array('employee'));
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	//  $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= $this->cash_ledger->get_pending_cash($date,$date1);
	 	// $data["payment"] = array_unique($data["payment"]);
 
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('customer_pending_ledger_pro',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
 
	
	
	
	
	
	public function cash_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
		$date = $this->uri->segment(3);
		$date1 = $this->uri->segment(4);
		//$emp = $this->uri->segment(5);
	  $data["CASHopening"]= $this->cash_ledger->get_opeing_cash($date);
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		$data["employee"]= $this->master_data->get_dat(array('employee'));
	 // $data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	//  $data["opening"]= $this->cash_ledger->get_opeing($date,$emp);
	 // $data["payment"]= $this->cash_ledger->get_data_customer('2019-03-01','4');
	  $data["payment"]= $this->cash_ledger->get_data_cash($date,$date1);
	//	 $data["payment"] = array_unique($data["payment"]);

	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('cash_ledger',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
 
	
			 
	public function cash_ledger_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->master_data->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/cash_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('employee').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	public function reprtcash_ledger()
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
 	$this->load->view('cash_ledger',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
	//
	
	
		public function index()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 $data["style"]= $this->master_data->get_dat(array('style'));
	 $data["employee"]= $this->master_data->get_dat(array('employee'));
	 $data["customer"]= $this->master_data->get_dat(array('customer'));
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
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         $salary = 0;
	 	$this->load->model("insert");
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
				
				
				$res=$this->insert->insert_dat($data,' cash','Cash');
				if($res==true)
				{	
					$data['suss'] = 'yes';
				 	
				}
				else
				{
				 //	$this->session->set_flashdata('suss', 'no');
				}
		}
				
				
		
	public function process_status()  
		{ 
		 
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         
	 	$this->load->model("insert");
		$data = array(
				'paid_Date' => date('Y-m-d'),
				'paidstatus' => 'YES',
				 
	 
  
);
	 
				$where = "trnno = ".$this->uri->segment(5)."";//.' and sr = '.$ordersr;
					//$where = 'trnno = 191';
					$this->db->where($where);
					$this->db->update('dmst', $data);
						$data = array(
				'EDATE' => date('Y-m-d'),
				'type' => '2',
				'customer_id' => $this->uri->segment(6),
				'amount' => $this->uri->segment(7),
				'narration' => 'Recevied From Customer',
	 
  
);
				
				
				$res=$this->insert->insert_dat($data,' cash');
					
					
					redirect('Cash/customer_pending_ledger/'.$this->uri->segment(3).'/'.$this->uri->segment(4).'');
				 
		}	
		
		
		
		
		
		
		
		
		
		
		
		
		public function reprtexp_ledger()
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
 	$this->load->view('exp_sheet',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	



	
	
 public function exp_ledger_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->master_data->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
		 $typ = $this->input->post('type');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/exp_ledger/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'/'.$this->input->post('type').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
		
		public function rep_customer_sale()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
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
	  $data["payment"]= $this->order_query->get_data_customer_sale($date,$date1);
	
	//	 $data["payment"] = array_unique($data["payment"]);

	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('customer_wise_sale_report',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	public function customer_sale_pro()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
	  //$data["opening"]= $this->cash_ledger->get_opeing('2019-03-01','4');
	  $data["employee"]= $this->master_data->get_dat(array('employee'));
		 $fromdate = $this->input->post('fromdate');
		 $emp = $this->input->post('employee');
		 //$typ = $this->input->post('type');
	 	  
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 //	$this->load->view('customer_ledger',$data);todate
	redirect('Cash/rep_customer_sale/'.$this->input->post('fromdate').'/'.$this->input->post('todate').'');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
	public function exp_ledger()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
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
	  $data["payment"]= $this->cash_ledger->get_data_exp($date,$date1,$type);
	
	//	 $data["payment"] = array_unique($data["payment"]);

	//	 echo $data["opening"];
		//die;
	// $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('exp_sheet',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
 
}

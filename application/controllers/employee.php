<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee extends MY_Controller {

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
	public function Employee_add()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
 	$this->load->view('employee');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
			public function employee_edit()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		$last = (int) $this->uri->segment(3);
		if ($last <= 0) {
			redirect('Employee');
		}
		$where = 'employee.empno = '.$last;
		$data["employee_data"]= $this->master_data->get_dat_where('employee', $where);
		$this->session->set_flashdata('id', $last);
		$this->load->view('employee_edit', $data);
	}
		public function index()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 $data["employee"]= $this->master_data->get_dat(array('employee'));
 	$this->load->view('employee_view',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
			public function Rate()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 $data["employee"]= $this->master_data->get_dat(array('employee'));
	 $data["style"]= $this->master_data->get_dat(array('style'), TRUE);
 	$this->load->view('design_asign_emp',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
public function process_rate()  
		{ 
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         
	 	$this->load->model("insert");
		$data = array(
				'EDATE' => date('Y-m-d'),
				'empno' => $this->input->post('empno'),
				'dno' => $this->input->post('styleno'),
				'rate' => $this->input->post('rate'),
				'remarks' => $this->input->post('Remarks'),
  
);
				
				
				$res=$this->insert->insert_dat($data,'emp_design_wise' ,'Employee/Rate');
				if($res==true)
				{	
					$data['suss'] = 'yes';
				 	
				}
				else
				{
				 //	$this->session->set_flashdata('suss', 'no');
				}
		}	
private function _sanitize_employee_type($type)
	{
		$allowed = array('Cutting', 'Stitching', 'Staff');
		return in_array($type, $allowed, TRUE) ? $type : '';
	}

	private function _sanitize_employee_status($status)
	{
		return ($status === 'Inactive') ? 'Inactive' : 'Active';
	}

	private function _employee_form_data()
	{
		return array(
				'EDATE' => date('Y-m-d'),
				'name' => $this->input->post('Name'),
				'phone' => $this->input->post('Phone'),
				'nic' => $this->input->post('CNIC'),
				'addres' => $this->input->post('Address'),
				'emergencyphone' => $this->input->post('Contact'),
				'advanceamount' => $this->input->post('Loan'),
				'remarks' => $this->input->post('Remarks'),
				'type' => $this->_sanitize_employee_type($this->input->post('type')),
				'status' => $this->_sanitize_employee_status($this->input->post('status')),
		);
	}

public function process()  
		{ 
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         
	 	$this->load->model("insert");
		$data = $this->_employee_form_data();
				
				
				$res=$this->insert->insert_dat($data,'employee' ,'Employee');
				if($res==true)
				{	
					$data['suss'] = 'yes';
				 	
				}
				else
				{
				 //	$this->session->set_flashdata('suss', 'no');
				}
		}
				
				
	public function process_edit()  
		{ 
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
	 	$this->load->model("insert");
		$data = $this->_employee_form_data();
		$last = (int) $this->uri->segment(3);
		if ($last <= 0) {
			redirect('Employee');
		}
		$this->insert->entry_update1('empno', $last, $data, 'employee');
		redirect('Employee');
		}	
 
}

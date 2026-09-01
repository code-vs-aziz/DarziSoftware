<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class embroidery extends MY_Controller {

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
	public function embroidery_add()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}
		$this->load->view('embroidery');
	}
	
	public function embroidery_edit()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
 	// $data["customer"]= $this->master_data->get_dat(array('customer'));
		$last = $this->uri->segment(3);
				$where = 'embroidery.ENO  = '.$last;
					$data["embroidery"]= $this->master_data->get_dat_where( 'embroidery',$where);
 
					//$data["last_id"] = $this->master_data->get_max_id('code','Measurement');
				// $data["measurement"]= $this->order_query->measurement($where);
  	
		 
				 $this->session->set_flashdata('id', $this->uri->segment(3));
 	$this->load->view('embroidery_edit',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
		public function index()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 $data["Embroidery"]= $this->master_data->get_dat(array('Embroidery'));
 	$this->load->view('embroidery_view',$data);
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
         
	 	$this->load->model("insert");
		$data = array(
				'EDATE' => date('Y-m-d'),
				'embroidery_name' => $this->input->post('Name'),
				'vol_no' => $this->input->post('vol_no'),
				'design' => $this->input->post('design'),
				'rate' => $this->input->post('Rate'),
				'remarks' => $this->input->post('Remarks'),
	 
  
);
				
				
				$res=$this->insert->insert_dat($data,'embroidery','embroidery');
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
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         
	 	$this->load->model("insert");
		$data = array(
				'EDATE' => date('Y-m-d'),
				'embroidery_name' => $this->input->post('Name'),
				'vol_no' => $this->input->post('vol_no'),
				'design' => $this->input->post('design'),
				'rate' => $this->input->post('Rate'),
				'remarks' => $this->input->post('Remarks'),
	 
  
);
				
				
				$last = $this->uri->segment(3);
			 
				$res=$this->insert->entry_update1('ENO',$last,$data,'embroidery');
				redirect('Embroidery');
				if($res==true)
				{	
					$data['suss'] = 'yes';
				 	
				}
				else
				{
				 //	$this->session->set_flashdata('suss', 'no');
				}
		}	
 
}

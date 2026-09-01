<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Button extends MY_Controller {

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
	public function Button_add()
	{
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 
 	$this->load->view('button');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
		public function index()
	{
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	 $data["Button"]= $this->master_data->get_dat(array('button'));
 	$this->load->view('button_view',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
public function process()  
		{ 
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         
	 	$this->load->model("insert");
		$data = array(
				'EDATE' => date('Y-m-d'),
				'bname' => $this->input->post('Name'),
				'remarks' => $this->input->post('Remarks'),
	 
  
);
				
				
				$res=$this->insert->insert_dat($data,'button','Button');
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

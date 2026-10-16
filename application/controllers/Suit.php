<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Suit extends MY_Controller {

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
	public function Suit_add()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if(!$this->session->userdata('user')){
			redirect('Login');
		}
		$this->load->view('suit_type');
	}
		public function Suit_edit()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
 	// $data["customer"]= $this->master_data->get_dat(array('customer'));
		$last = $this->uri->segment(3);
				$where = 'suit.SNO  = '.$last;
					$data["suit"]= $this->master_data->get_dat_where( 'suit',$where);
 
					//$data["last_id"] = $this->master_data->get_max_id('code','Measurement');
				// $data["measurement"]= $this->order_query->measurement($where);
  	
		 
				 $this->session->set_flashdata('id', $this->uri->segment(3));
 	$this->load->view('suit_type_edit',$data);
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
	 $data["suit"]= $this->master_data->get_dat(array('suit'));
 	$this->load->view('suit_type_view',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
public function process()  
		{ 
		//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         
	 	$this->load->model("insert");
		$rate = str_replace(',', '', trim((string) $this->input->post('Rate')));
		$data = array(
				'EDATE' => date('Y-m-d'),
				'name' => $this->input->post('Name'),
				'rate' => $rate !== '' ? (float) $rate : 0,
				'remarks' => $this->input->post('Remarks'),
				'is_active' => $this->input->post('is_active') ? 1 : 0,
);
				
			 
				$res=$this->insert->insert_dat($data,'suit','Suit');
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
	//	error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
         
	 	$this->load->model("insert");
		$rate = str_replace(',', '', trim((string) $this->input->post('Rate')));
		$data = array(
			'EDATE' => date('Y-m-d'),
				'name' => $this->input->post('Name'),
				'rate' => $rate !== '' ? (float) $rate : 0,
				'remarks' => $this->input->post('Remarks'),
				'is_active' => $this->input->post('is_active') ? 1 : 0,
);
				
				
								$last = (int) $this->uri->segment(3);
				if ($last <= 0)
				{
					redirect('Suit');
					return;
				}
			 
				$res=$this->insert->entry_update1('SNO',$last,$data,'suit');
				redirect('Suit');
				if($res==true)
				{	
					$data['suss'] = 'yes';
				 	
				}
				else
				{
				 //	$this->session->set_flashdata('suss', 'no');
				}
		}

	public function toggle_active($id = 0)
	{
		$id = (int) $id;
		if ($id > 0)
		{
			$row = $this->db->where('SNO', $id)->get('suit')->row_array();
			if ($row)
			{
				$next = empty($row['is_active']) ? 1 : 0;
				$this->db->where('SNO', $id)->update('suit', array('is_active' => $next));
				$this->master_data->clear_dat_cache('suit');
				$this->session->set_flashdata('suss', 'yes');
			}
		}
		redirect('Suit');
	}
		
 
}

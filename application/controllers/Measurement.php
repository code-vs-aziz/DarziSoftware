<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Measurement extends MY_Controller {

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
 	// $data["customer"]= $this->master_data->get_dat(array('customer'));
		$last = $this->master_data->get_max_id('code','customer');;
				$where = 'code  = '.$last;
					$data["customer_data"]= $this->master_data->get_dat_where( 'customer',$where);
					$data["last_id"] = $this->master_data->get_max_id('code','Measurement');
 	$this->load->view('measurement',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
			public function measurement_edit()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
 	// $data["customer"]= $this->master_data->get_dat(array('customer'));
		$last = $this->uri->segment(3);
				$where = 'customer.code  = '.$last;
					$data["customer_data"]= $this->master_data->get_dat_where( 'customer',$where);
					$data["last_id"] = $this->master_data->get_max_id('code','Measurement');
				 $data["measurement"]= $this->order_query->measurement($where);
				 $data['return_order'] = $this->input->get('return') === 'order';
  	
		 
				 $this->session->set_flashdata('id', $this->uri->segment(3));
 	$this->load->view('measurement_edit',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
		public function measurement_add()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
 	// $data["customer"]= $this->master_data->get_dat(array('customer'));
		$last = $this->uri->segment(3);
	 
				$where = 'code  = '.$last;
					$data["customer_data"]= $this->master_data->get_dat_where( 'customer',$where);
					$data["last_id"] = $this->master_data->get_max_id('code','Measurement');
					$data['return_order'] = $this->input->get('return') === 'order';
					 
 	$this->load->view('measurement',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
			public function measurement_view()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
 	 $data["measurement"]= $this->order_query->measurement();
 	$this->load->view('measurement_view',$data);
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
	'customer_code'=> $this->input->post('customerno1'),			 
	'KL'=> $this->input->post('KL'),			 
	'FPL'=> $this->input->post('FPL'),			 
	'BL'=> $this->input->post('BL'),			 
	//'BPL'=> $this->input->post('BPL'),			 
	'Teera'=> $this->input->post('Teera'),
	'BP'=> $this->input->post('BP'),
	//'CKC'=> $this->input->post('CKC'),
	//'KC'=> $this->input->post('KC'),
	'CL'=> $this->input->post('CL'),
	'Chaati'=> $this->input->post('Chaati'),
	//'KKL'=> $this->input->post('KKL'),
//	'Kamar'=> $this->input->post('Kamar'),
//	'Moda'=> $this->input->post('Moda'),
	'Gala'=> $this->input->post('Gala'),
	'Poncha_Choraic'=> $this->input->post('Poncha_Choraic'),
	'Poncha_Chorai'=> $this->input->post('Poncha_Chorai'),
	'Shalwar_Lenght'=> $this->input->post('Shalwar_Lenght'),
//	'Pajama_Waist'=> $this->input->post('Pajama_Waist'),
//	'PLM'=> $this->input->post('PLM'),
//	'FPS'=> $this->input->post('FPS'),
	'Asan'=> $this->input->post('Asan'),
	'Side_Pocket_Size'=> $this->input->post('Side_Pocket_Size'),
	'SC'=> $this->input->post('SC'),
	'BG'=> $this->input->post('BG'),
	'Lambai'=> $this->input->post('Lambai'),
	'Poncha'=> $this->input->post('Poncha'),
	'shalwar_Asan'=> $this->input->post('shalwar_Asan'),
	'Peeir'=> $this->input->post('Peeir'),
	'PLambai'=> $this->input->post('PLambai'),
	'PPoncha'=> $this->input->post('PPoncha'),
	'PAsan'=> $this->input->post('PAsan'),
	'PPeeir'=> $this->input->post('PPeeir'),
	'PentLambai'=> $this->input->post('PentLambai'),
	'pentWaiste'=> $this->input->post('pentWaiste'),
	'HIP'=> $this->input->post('HIP'),
	'PentPoncha'=> $this->input->post('PentPoncha'),
	'PentThai'=> $this->input->post('PentThai'),
	'SLambai'=> $this->input->post('SLambai'),
	'SBazoo'=> $this->input->post('SBazoo'),
	'STeera'=> $this->input->post('STeera'),
	'SGeera'=> $this->input->post('SGeera'),
	'geera'=> $this->input->post('geera'),
	'SChaati'=> $this->input->post('SChaati'),
	'SKamar'=> $this->input->post('SKamar'),
	'SGaala'=> $this->input->post('SGaala'),
	'CoatLambai'=> $this->input->post('CoatLambai'),
	'CoatBazoo'=> $this->input->post('CoatBazoo'),
	'CoatTeera'=> $this->input->post('CoatTeera'),
	'CoatChaati'=> $this->input->post('CoatChaati'),
	'CoatHIP'=> $this->input->post('CoatHIP'),
	'CoatKamar'=> $this->input->post('CoatKamar'),
	'crossback'=> $this->input->post('crossback'),
	'wastLambai'=> $this->input->post('wastLambai'),
	'wastTeera'=> $this->input->post('wastTeera'),
	'wastChaati'=> $this->input->post('wastChaati'),
	'wastHIP'=> $this->input->post('wastHIP'),
	'wastKamar'=> $this->input->post('wastKamar'),
	'remarks'=> $this->input->post('Remarks'),
	'pait'=> $this->input->post('pait'),
	'WASIT'=> $this->input->post('WASIT'),
	'FRONT_Pocket_Size'=> $this->input->post('FRONT_Pocket_Size'),
	'CAKLAMBAI'=> $this->input->post('CAKLAMBAI'),
	'KAAFCURAI'=> $this->input->post('KAAFCURAI'),
	'MODA_CURAI'=> $this->input->post('MODA_CURAI'),
	'Poncha_Choraic'=> $this->input->post('Poncha_Choraic'),
	'CoatGaala'=> $this->input->post('CoatGaala'),
	'wastGaala'=> $this->input->post('wastGaala'),
  
);
				
				
				$res=$this->insert->insert_dat1($data,'Measurement');
				$customer_code = (int) $this->input->post('customerno1', TRUE);
				if ($this->input->post('return_to', TRUE) === 'order' && $customer_code > 0)
				{
					redirect('Order/order_add1/'.$customer_code);
					return;
				}
				redirect('Order/order_view');
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
	'customer_code			 '=> $this->input->post('customerno1'),			 
	'KL'=> $this->input->post('KL'),			 
	'FPL'=> $this->input->post('FPL'),			 
	'BL'=> $this->input->post('BL'),			 
	//'BPL'=> $this->input->post('BPL'),			 
	'Teera'=> $this->input->post('Teera'),
	'BP'=> $this->input->post('BP'),
	//'CKC'=> $this->input->post('CKC'),
	//'KC'=> $this->input->post('KC'),
	'CL'=> $this->input->post('CL'),
	'Chaati'=> $this->input->post('Chaati'),
	//'KKL'=> $this->input->post('KKL'),
//	'Kamar'=> $this->input->post('Kamar'),
//	'Moda'=> $this->input->post('Moda'),
	'Gala'=> $this->input->post('Gala'),
	'Poncha_Chorai'=> $this->input->post('Poncha_Chorai'),
	'Shalwar_Lenght'=> $this->input->post('Shalwar_Lenght'),
//	'Pajama_Waist'=> $this->input->post('Pajama_Waist'),
//	'PLM'=> $this->input->post('PLM'),
//	'FPS'=> $this->input->post('FPS'),
	'Asan'=> $this->input->post('Asan'),
	'Side_Pocket_Size'=> $this->input->post('Side_Pocket_Size'),
	'SC'=> $this->input->post('SC'),
	'BG'=> $this->input->post('BG'),
	'Lambai'=> $this->input->post('Lambai'),
	'Poncha'=> $this->input->post('Poncha'),
	'shalwar_Asan'=> $this->input->post('shalwar_Asan'),
	'Peeir'=> $this->input->post('Peeir'),
	'PLambai'=> $this->input->post('PLambai'),
	'PPoncha'=> $this->input->post('PPoncha'),
	'PAsan'=> $this->input->post('PAsan'),
	'PPeeir'=> $this->input->post('PPeeir'),
	'PentLambai'=> $this->input->post('PentLambai'),
	'pentWaiste'=> $this->input->post('pentWaiste'),
	'HIP'=> $this->input->post('HIP'),
	'PentPoncha'=> $this->input->post('PentPoncha'),
	'PentThai'=> $this->input->post('PentThai'),
	'SLambai'=> $this->input->post('SLambai'),
	'SBazoo'=> $this->input->post('SBazoo'),
	'STeera'=> $this->input->post('STeera'),
	'SGeera'=> $this->input->post('SGeera'),
	'geera'=> $this->input->post('geera'),
	'SChaati'=> $this->input->post('SChaati'),
	'SKamar'=> $this->input->post('SKamar'),
	'SGaala'=> $this->input->post('SGaala'),
	'CoatLambai'=> $this->input->post('CoatLambai'),
	'CoatBazoo'=> $this->input->post('CoatBazoo'),
	'CoatTeera'=> $this->input->post('CoatTeera'),
	'CoatChaati'=> $this->input->post('CoatChaati'),
	'CoatHIP'=> $this->input->post('CoatHIP'),
	'CoatKamar'=> $this->input->post('CoatKamar'),
	'crossback'=> $this->input->post('crossback'),
	'wastLambai'=> $this->input->post('wastLambai'),
	'wastTeera'=> $this->input->post('wastTeera'),
	'wastChaati'=> $this->input->post('wastChaati'),
	'wastHIP'=> $this->input->post('wastHIP'),
	'wastKamar'=> $this->input->post('wastKamar'),
	'remarks'=> $this->input->post('Remarks'),
	'pait'=> $this->input->post('pait'),
	'WASIT'=> $this->input->post('WASIT'),
	'FRONT_Pocket_Size'=> $this->input->post('FRONT_Pocket_Size'),
	'CAKLAMBAI'=> $this->input->post('CAKLAMBAI'),
	'KAAFCURAI'=> $this->input->post('KAAFCURAI'),
	'MODA_CURAI'=> $this->input->post('MODA_CURAI'),
	'Poncha_Choraic'=> $this->input->post('Poncha_Choraic'),
	'CoatGaala'=> $this->input->post('CoatGaala'),
	'wastGaala'=> $this->input->post('wastGaala'),
  
);
				$last = $this->uri->segment(3);
				
				$res=$this->insert->entry_update1('code',$last,$data,'Measurement');
				$customer_code = (int) $this->input->post('customerno1', TRUE);
				if ($this->input->post('return_to', TRUE) === 'order' && $customer_code > 0)
				{
					$meas_code = (int) $last;
					redirect('Order/order_add1/'.$customer_code.($meas_code > 0 ? '/'.$meas_code : ''));
					return;
				}
				redirect('Order/order_view/'.$last.'');
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

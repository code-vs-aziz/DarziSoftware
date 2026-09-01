<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends MY_Controller {

	/** Default rows per page when not specified */
	protected $customer_per_page_default = 25;

	/** Allowed "per page" values (search results) */
	protected $customer_per_page_allowed = array(10, 25, 50, 100);

	/**
	 * Resolve per_page from query string; must be one of $customer_per_page_allowed.
	 *
	 * @return int
	 */
	protected function resolve_customer_per_page()
	{
		$raw = $this->input->get('per_page');
		$n = ($raw !== NULL && $raw !== '') ? (int) $raw : $this->customer_per_page_default;
		if ( ! in_array($n, $this->customer_per_page_allowed, TRUE))
		{
			$n = $this->customer_per_page_default;
		}
		return $n;
	}

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
	public function Customer_add()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	  $data["max_naap"]= $this->master_data->get_max_id('man_napno', 'customer');
	
 	$this->load->view('customer',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
		public function customer_edit()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}
		$last = (int) $this->uri->segment(3);
		if ($last < 1)
		{
			show_error('Invalid customer code.', 400);
			return;
		}
		$data = array();
		$data['customer_data'] = $this->master_data->get_dat_where('customer', 'customer.code = '.$last);
		if ( ! is_array($data['customer_data']))
		{
			$data['customer_data'] = array();
		}
		$this->session->set_flashdata('id', $last);
		$this->load->view('customer_edit', $data);
	}
	
	
		public function index()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}
		$data['customer'] = array();
		$data['customers_with_measurement'] = array();
		$data['customer_measurement_codes'] = array();
		$data['pagination'] = '';
		$data['naap'] = array();
		$data['is_search'] = FALSE;
		$data['search_term'] = '';
		$data['pagination_total_rows'] = 0;
		$data['pagination_offset'] = 0;
		$data['pagination_per_page'] = $this->customer_per_page_default;
		$data['customer_per_page_allowed'] = $this->customer_per_page_allowed;
 	$this->load->view('customer_view',$data);

		
	}

	/**
	 * POST from customer search form: redirects to search_customer with encoded term.
	 */
	public function search_post()
	{
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}
		$q = $this->input->post('customer_search', TRUE);
		$q = $q !== NULL ? trim($q) : '';
		if ($q === '')
		{
			$this->session->set_flashdata('customer_notice', t('customer_search_need_field'));
			redirect('Customer');
		}
		$per = (int) $this->input->post('per_page');
		if ( ! in_array($per, $this->customer_per_page_allowed, TRUE))
		{
			$per = $this->customer_per_page_default;
		}
		redirect('Customer/search_customer/'.rawurlencode($q).'?per_page='.$per);
	}
	
			public function search_customer()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}
		$this->load->library('pagination');
	//	$this->load->library('form_validation');
		
 
	   
	  
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		$term = $this->uri->segment(3);
		$term = $term !== NULL ? rawurldecode($term) : '';
		$term = trim($term);
		if ($term === '')
		{
			$this->session->set_flashdata('customer_notice', t('customer_search_need_term'));
			redirect('Customer');
		}
		$per_page = $this->resolve_customer_per_page();
		$offset = (int) $this->uri->segment(4);
		if ($offset < 0)
		{
			$offset = 0;
		}

		$enc_term = rawurlencode($term);
		$total_rows = $this->customer_query->count_search_customers($term);
		if ($total_rows > 0 && $per_page > 0)
		{
			$max_offset = (int) (ceil($total_rows / $per_page) - 1) * $per_page;
			if ($offset > $max_offset)
			{
				$offset = $max_offset;
			}
		}
		$qs_per = '?per_page='.(int) $per_page;
		$config['base_url'] = site_url('Customer/search_customer/'.$enc_term);
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $per_page;
		$config['uri_segment'] = 4;
		$config['suffix'] = $qs_per;
		$config['first_url'] = rtrim(site_url('Customer/search_customer/'.$enc_term), '/').$qs_per;
		$config['reuse_query_string'] = FALSE;
		$config['full_tag_open'] = '<nav aria-label="Customer results pages" class="mt-2"><ul class="pagination pagination-sm justify-content-center flex-wrap mb-0">';
		$config['full_tag_close'] = '</ul></nav>';
		$config['attributes'] = array('class' => 'page-link');
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active" aria-current="page"><span class="page-link">';
		$config['cur_tag_close'] = '</span></li>';
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';
		$this->pagination->initialize($config);

		$data['customer'] = $this->customer_query->search_customers_paged($term, $per_page, $offset);
		$codes = array();
		foreach ($data['customer'] as $row)
		{
			$codes[] = (int) $row->code;
		}
		$data['customers_with_measurement'] = $this->customer_query->get_measurement_flags_for_codes($codes);
		$data['customer_measurement_codes'] = $this->customer_query->get_primary_measurement_codes_for_codes($codes);
		$links = $this->pagination->create_links();
		$num_pages = $per_page > 0 ? (int) ceil($total_rows / $per_page) : 1;
		if ($links === '' && $total_rows > 0 && $num_pages <= 1)
		{
			$links = '<nav aria-label="Customer results pages" class="mt-2"><ul class="pagination pagination-sm justify-content-center mb-0"><li class="page-item active" aria-current="page"><span class="page-link">Page 1 of 1</span></li></ul></nav>';
		}
		$data['pagination'] = $links;
		$data['pagination_total_rows'] = (int) $total_rows;
		$data['pagination_offset'] = (int) $offset;
		$data['pagination_per_page'] = (int) $per_page;
		$data['customer_per_page_allowed'] = $this->customer_per_page_allowed;
		$data['naap'] = array();
		$data['is_search'] = TRUE;
		$data['search_term'] = $term;
 	$this->load->view('customer_view',$data);

		
	}
public function process()  
		{ 
		$this->load->helper('url'); 
		$this->load->library('session');
		$this->load->model('insert');

		$man_napno = trim((string) $this->input->post('man_napno'));
		if ($man_napno !== '')
		{
			$existing = $this->master_data->get_dat_where('customer', 'man_napno = '.$this->db->escape($man_napno));
			if ( ! empty($existing))
			{
				redirect('Customer/Customer_add/err');
				return;
			}
		}

		$data = array(
				'EDATE' => date('Y-m-d'),
				'name' => $this->input->post('Name'),
				'city' => $this->input->post('City'),
				'country' => $this->input->post('Country'),
				'Phone' => $this->input->post('Phone'),
				'Address' => $this->input->post('Address'),
				'cus_type' => $this->input->post('type'),
				'cnic' => $this->input->post('CNIC'),
				'man_napno' => $man_napno,
				'remarks' => $this->input->post('Remarks'),
		);

		$this->insert->insert_dat1($data, 'customer');
		redirect('Measurement');
		return;
		}
				
		public function process_edit()  
		{ 
		$this->load->helper('url'); 
		$this->load->library('session');
		$this->load->model('insert');

		$last = (int) $this->uri->segment(3);
		$man_napno = trim((string) $this->input->post('man_napno'));
		if ($last > 0 && $man_napno !== '')
		{
			$existing = $this->master_data->get_dat_where(
				'customer',
				'man_napno = '.$this->db->escape($man_napno).' AND code != '.$last
			);
			if ( ! empty($existing))
			{
				redirect('Customer/customer_edit/'.$last.'/err');
				return;
			}
		}

		$data = array(
				'EDATE' => date('Y-m-d'),
				'name' => $this->input->post('Name'),
				'city' => $this->input->post('City'),
				'country' => $this->input->post('Country'),
				'Phone' => $this->input->post('Phone'),
				'Address' => $this->input->post('Address'),
				'cnic' => $this->input->post('CNIC'),
				'cus_type' => $this->input->post('type'),
				'man_napno' => $man_napno,
				'remarks' => $this->input->post('Remarks'),
		);

		$this->insert->entry_update1('code', $last, $data, 'customer');
		redirect('Customer');
		return;
		}		
		
 
}

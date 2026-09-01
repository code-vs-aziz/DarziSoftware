<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Configuration extends MY_Controller
{
	public function index()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Business_config_model');
		$this->load->model('Order_predefined_model');

		$tab = $this->input->get('tab', TRUE);
		$tab = ($tab === 'order_predefined') ? 'order_predefined' : 'business';

		$data['active_tab'] = $tab;
		$data['config'] = $this->Business_config_model->get_config();
		$data['config_table_ready'] = $this->Business_config_model->has_table();
		$data['predefined_table_ready'] = $this->Order_predefined_model->has_table();
		$data['order_predefined_items'] = $this->Order_predefined_model->get_all_for_admin();
		$this->load->view('business_configuration', $data);
	}

	public function save()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Business_config_model');

		$ok = $this->Business_config_model->save_config(array(
			'location_name' => $this->input->post('location_name', TRUE),
			'phone' => $this->input->post('phone', TRUE),
			'address_urdu' => $this->input->post('address_urdu', FALSE),
		));

		$this->session->set_flashdata($ok ? 'suss' : 'error', $ok ? 'yes' : 'missing_table');
		redirect('Configuration');
	}

	public function predefined_add()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Order_predefined_model');

		$ok = $this->Order_predefined_model->add_item(
			$this->input->post('label_text', FALSE),
			$this->input->post('insert_text', FALSE),
			$this->input->post('row_group', TRUE)
		);

		$this->session->set_flashdata($ok ? 'predefined_suss' : 'predefined_error', $ok ? 'yes' : ($this->Order_predefined_model->has_table() ? 'invalid' : 'missing_table'));
		redirect('Configuration?tab=order_predefined');
	}

	public function predefined_update()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Order_predefined_model');

		$id = (int) $this->input->post('id');
		$ok = $this->Order_predefined_model->update_item(
			$id,
			$this->input->post('label_text', FALSE),
			$this->input->post('insert_text', FALSE),
			$this->input->post('row_group', TRUE)
		);

		$this->session->set_flashdata(
			$ok ? 'predefined_suss' : 'predefined_error',
			$ok ? 'updated' : ($this->Order_predefined_model->has_table() ? 'invalid' : 'missing_table')
		);
		redirect('Configuration?tab=order_predefined');
	}

	public function predefined_delete($id = 0)
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Order_predefined_model');

		$ok = $this->Order_predefined_model->delete_item($id);
		$this->session->set_flashdata($ok ? 'predefined_suss' : 'predefined_error', $ok ? 'deleted' : 'failed');
		redirect('Configuration?tab=order_predefined');
	}

	public function predefined_reseed()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Order_predefined_model');

		$ok = $this->Order_predefined_model->reseed_defaults();
		$this->session->set_flashdata($ok ? 'predefined_suss' : 'predefined_error', $ok ? 'reseeded' : 'missing_table');
		redirect('Configuration?tab=order_predefined');
	}
}

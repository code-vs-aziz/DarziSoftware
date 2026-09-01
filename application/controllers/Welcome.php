<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller {

	protected function load_dashboard_page()
	{
		$data = array(
			'dashboard_summary' => $this->dashboard_data->dashboard_summary(),
		);
		$this->load->view('dashboard_home', $data);
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
	public function index()
	{
		if (!$this->require_login())
		{
			return;
		}
		$this->load_dashboard_page();
	}

	public function overview()
	{
		if (!$this->require_login())
		{
			return;
		}
		$this->load_dashboard_page();
	}

	public function dashboard_summary_json()
	{
		if (!$this->require_login(TRUE))
		{
			return;
		}
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'summary' => $this->dashboard_data->dashboard_summary(),
			)));
	}

	public function dashboard_top_customers_json()
	{
		if (!$this->require_login(TRUE))
		{
			return;
		}
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'top_customers' => $this->dashboard_data->dashboard_top_customers(5),
			)));
	}

	public function dashboard_latest_orders_json()
	{
		if (!$this->require_login(TRUE))
		{
			return;
		}
		$limit = (int) $this->input->get('limit', TRUE);
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'latest_orders' => $this->dashboard_data->dashboard_latest_orders($limit),
			)));
	}

	public function dashboard_financial_trends_json()
	{
		if (!$this->require_login(TRUE))
		{
			return;
		}
		$days = (int) $this->input->get('days', TRUE);
		if (!in_array($days, array(7, 30, 90), TRUE))
		{
			$days = 30;
		}
		$to = date('Y-m-d');
		$from = date('Y-m-d', strtotime('-'.($days - 1).' days'));
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'from' => $from,
				'to' => $to,
				'trends' => $this->dashboard_data->get_financial_grouped_comparison($from, $to, 'daily'),
			)));
	}
		public function Report()
	{
		if (!$this->require_login())
		{
			return;
		}
		$this->load->view('Report');
	}
}

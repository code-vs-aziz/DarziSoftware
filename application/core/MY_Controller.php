<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller for authenticated application pages.
 *
 * Login enforcement is also handled by Rbac_hook; this provides
 * defense-in-depth and shared helpers for reports.
 */
class MY_Controller extends CI_Controller {

	/** @var bool */
	protected $requires_login = TRUE;

	public function __construct()
	{
		parent::__construct();
		$this->load_app_models();
		$this->purge_orphan_success_flash();
		// CLI jobs (php index.php …) have no browser session.
		if ($this->requires_login && ! is_cli())
		{
			$this->require_login();
		}
	}

	/**
	 * Remove leftover "suss" / success_msg session keys that are not valid
	 * one-shot flashdata. Prevents green success banners from sticking on
	 * every page open (common after older bugs left plain userdata).
	 */
	protected function purge_orphan_success_flash()
	{
		if (is_cli() || session_status() !== PHP_SESSION_ACTIVE)
		{
			return;
		}
		foreach (array('suss', 'success_msg', 'transfer_error', 'bulk_transfer_count') as $key)
		{
			$is_flash = isset($_SESSION['__ci_vars'][$key]) && ! is_int($_SESSION['__ci_vars'][$key]);
			if (isset($_SESSION[$key]) && ! $is_flash)
			{
				unset($_SESSION[$key]);
				if (isset($_SESSION['__ci_vars'][$key]))
				{
					unset($_SESSION['__ci_vars'][$key]);
				}
			}
		}
		if (isset($_SESSION['__ci_vars']) && empty($_SESSION['__ci_vars']))
		{
			unset($_SESSION['__ci_vars']);
		}
	}

	/**
	 * Domain models (Phase 3 — direct access, no gatedata facade).
	 */
	protected function load_app_models()
	{
		$this->load->model('Master_data_model', 'master_data');
		$this->load->model('Order_query_model', 'order_query');
		$this->load->model('Employee_query_model', 'employee_query');
		$this->load->model('Customer_query_model', 'customer_query');
		$this->load->model('Dashboard_data_model', 'dashboard_data');
		$this->load->model('Cash_ledger_model', 'cash_ledger');
	}

	/**
	 * Redirect to login or return 401 JSON when session expired.
	 *
	 * @param bool $json
	 * @return bool
	 */
	protected function require_login($json = FALSE)
	{
		$this->load->helper('url');
		$this->load->library('session');
		if ($this->session->userdata('user'))
		{
			return TRUE;
		}
		if ($json)
		{
			$this->output
				->set_status_header(401)
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'success' => FALSE,
					'message' => 'Session expired. Please login again.',
				)));
			return FALSE;
		}
		redirect('Login');
		return FALSE;
	}

	/**
	 * @param int $from_segment
	 * @param int $to_segment
	 * @return array
	 */
	protected function report_date_range_from_uri($from_segment = 3, $to_segment = 4)
	{
		$from = trim((string) $this->uri->segment($from_segment));
		$to = trim((string) $this->uri->segment($to_segment));
		return array($from, $to);
	}

	/**
	 * @param string $from_field
	 * @param string $to_field
	 * @return array
	 */
	protected function report_date_range_from_post($from_field = 'fromdate', $to_field = 'todate')
	{
		$from = trim((string) $this->input->post($from_field));
		$to = trim((string) $this->input->post($to_field));
		return array($from, $to);
	}
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broadcast extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Broadcast_model', 'broadcast_model');
		$this->load->library('broadcast_service');
		$this->load->library('notification_service');
	}

	public function index()
	{
		$data = array(
			'table_ready' => $this->broadcast_model->has_tables(),
			'campaigns' => $this->broadcast_model->has_tables() ? $this->broadcast_model->list_campaigns(30) : array(),
			'bridge_status' => $this->notification_service->bridge_status(),
			'phone_customer_count' => $this->broadcast_model->has_tables()
				? $this->broadcast_model->count_customers_with_phone()
				: 0,
			'safety' => array(
				'daily_cap' => Broadcast_service::DEFAULT_DAILY_CAP,
				'hour_cap' => Broadcast_service::DEFAULT_HOUR_CAP,
				'hard_daily' => Broadcast_service::HARD_DAILY_CAP,
				'delay_min' => Broadcast_service::DEFAULT_DELAY_MIN,
				'delay_max' => Broadcast_service::DEFAULT_DELAY_MAX,
				'warn_at' => Broadcast_service::WARN_RECIPIENTS,
				'max_recipients' => Broadcast_service::MAX_RECIPIENTS_PER_CAMPAIGN,
			),
		);
		$this->load->view('broadcast', $data);
	}

	public function search_customers()
	{
		header('Content-Type: application/json; charset=UTF-8');
		$q = trim((string) $this->input->get_post('q', TRUE));
		$limit = (int) $this->input->get_post('limit');
		if ($limit < 1)
		{
			$limit = 60;
		}
		$rows = $this->broadcast_model->search_customers($q, $limit, 0);
		$out = array();
		foreach ($rows as $row)
		{
			$norm = $this->broadcast_service->normalize_phone(isset($row['Phone']) ? $row['Phone'] : '');
			if ($norm === '')
			{
				continue;
			}
			$out[] = array(
				'code' => (int) $row['code'],
				'name' => (string) $row['name'],
				'phone' => (string) $row['Phone'],
				'phone_normalized' => $norm,
			);
		}
		echo json_encode(array(
			'ok' => TRUE,
			'count' => count($out),
			'total_matching' => $this->broadcast_model->count_customers_with_phone($q),
			'customers' => $out,
		));
	}

	public function create()
	{
		if ( ! $this->broadcast_model->has_tables())
		{
			$this->session->set_flashdata('broadcast_error', 'missing_table');
			redirect('Broadcast');
			return;
		}

		$codes = $this->input->post('customer_codes');
		if ( ! is_array($codes))
		{
			$codes = array();
		}
		$select_all = (string) $this->input->post('select_all_phones') === '1';
		if ($select_all)
		{
			// Cap select-all to safety max; user must confirm risk.
			$rows = $this->broadcast_model->list_phone_customers(Broadcast_service::MAX_RECIPIENTS_PER_CAMPAIGN);
			$codes = array();
			foreach ($rows as $row)
			{
				if ($this->broadcast_service->normalize_phone($row['Phone']) !== '')
				{
					$codes[] = (int) $row['code'];
				}
			}
		}

		$result = $this->broadcast_service->create_and_queue(
			$this->input->post('title', TRUE),
			$this->input->post('message', FALSE),
			$codes,
			array(
				'delay_min_sec' => $this->input->post('delay_min_sec'),
				'delay_max_sec' => $this->input->post('delay_max_sec'),
				'daily_cap' => $this->input->post('daily_cap'),
				'hour_cap' => $this->input->post('hour_cap'),
				'send_window_start' => $this->input->post('send_window_start'),
				'send_window_end' => $this->input->post('send_window_end'),
			),
			(string) $this->session->userdata('user'),
			TRUE
		);

		if (empty($result['ok']))
		{
			$this->session->set_flashdata('broadcast_error', isset($result['error']) ? $result['error'] : 'failed');
			redirect('Broadcast');
			return;
		}

		$this->session->set_flashdata('broadcast_ok', 'created:'.$result['campaign_id'].':'.$result['recipients']);
		if ( ! empty($result['warned']))
		{
			$this->session->set_flashdata('broadcast_warn', 'large_batch');
		}
		redirect('Broadcast/view/'.$result['campaign_id']);
	}

	public function view($id = 0)
	{
		$id = (int) $id;
		$campaign = $this->broadcast_model->get_campaign($id);
		if ( ! $campaign)
		{
			show_404();
			return;
		}
		$data = array(
			'table_ready' => TRUE,
			'campaign' => $campaign,
			'recipients' => $this->broadcast_model->list_recipients($id, 300, 0),
			'bridge_status' => $this->notification_service->bridge_status(),
		);
		$this->load->view('broadcast_view', $data);
	}

	public function pause($id = 0)
	{
		$this->broadcast_service->set_status((int) $id, 'paused');
		$this->session->set_flashdata('broadcast_ok', 'paused');
		redirect('Broadcast/view/'.(int) $id);
	}

	public function resume($id = 0)
	{
		$this->broadcast_service->set_status((int) $id, 'queued');
		$this->session->set_flashdata('broadcast_ok', 'resumed');
		redirect('Broadcast/view/'.(int) $id);
	}

	public function cancel($id = 0)
	{
		$this->broadcast_service->set_status((int) $id, 'cancelled');
		$this->session->set_flashdata('broadcast_ok', 'cancelled');
		redirect('Broadcast/view/'.(int) $id);
	}

	public function process_now($id = 0)
	{
		$result = $this->broadcast_service->process(1);
		$this->session->set_flashdata('broadcast_ok', 'processed:'.(int) $result['processed']);
		if ((int) $id > 0)
		{
			redirect('Broadcast/view/'.(int) $id);
			return;
		}
		redirect('Broadcast');
	}
}

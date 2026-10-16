<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends MY_Controller
{
	public function index()
	{
		$this->load->helper('url');
		$this->load->model('Notification_model');

		$data['table_ready'] = $this->Notification_model->has_tables();
		$data['settings'] = $this->Notification_model->get_settings();
		$data['queue'] = $this->Notification_model->get_recent_queue(80);
		$data['stats'] = $this->Notification_model->get_stats();

		$this->load->library('notification_service');
		$data['bridge_status'] = $this->notification_service->bridge_status();

		$this->load->view('notification_automation', $data);
	}

	public function save_settings()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Notification_model');

		if (!$this->Notification_model->has_tables())
		{
			$this->session->set_flashdata('notify_error', 'missing_table');
			redirect('Notifications');
			return;
		}

		$ok = $this->Notification_model->save_settings(array(
			'enabled_order_booked' => $this->input->post('enabled_order_booked'),
			'enabled_order_ready' => $this->input->post('enabled_order_ready'),
			'enabled_payment_reminder' => $this->input->post('enabled_payment_reminder'),
			'enabled_delivery_confirm' => $this->input->post('enabled_delivery_confirm'),
			'auto_open_whatsapp' => $this->input->post('auto_open_whatsapp'),
			'dispatch_mode' => $this->input->post('dispatch_mode', TRUE),
			'payment_reminder_days_before' => $this->input->post('payment_reminder_days_before', TRUE),
			'template_order_booked' => $this->input->post('template_order_booked', FALSE),
			'template_order_ready' => $this->input->post('template_order_ready', FALSE),
			'template_payment_reminder' => $this->input->post('template_payment_reminder', FALSE),
			'template_delivery_confirm' => $this->input->post('template_delivery_confirm', FALSE),
		));

		$this->session->set_flashdata($ok ? 'notify_saved' : 'notify_error', $ok ? 'yes' : 'save_failed');
		redirect('Notifications');
	}

	public function process_pending()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->library('notification_service');

		// Keep web-triggered batches small so PHP workers are not blocked on WhatsApp I/O.
		$result = $this->notification_service->process_pending(5);
		$this->session->set_flashdata('notify_processed', (int) $result['processed']);
		redirect('Notifications');
	}

	public function restore_templates()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Notification_model');

		$ok = $this->Notification_model->restore_professional_templates();
		$this->session->set_flashdata($ok ? 'notify_saved' : 'notify_error', $ok ? 'templates_restored' : 'save_failed');
		redirect('Notifications');
	}

	public function run_payment_reminders()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->library('notification_service');

		$count = $this->notification_service->scan_payment_reminders();
		$this->session->set_flashdata('notify_reminders_queued', (int) $count);
		redirect('Notifications');
	}

	public function open_whatsapp($id = 0)
	{
		$this->load->helper('url');
		$this->load->model('Notification_model');

		$row = $this->Notification_model->get_queue_row((int) $id);
		if ($row === NULL || trim((string) $row->whatsapp_url) === '')
		{
			redirect('Notifications');
			return;
		}

		redirect($row->whatsapp_url);
	}

	public function dispatch_for_order()
	{
		$this->load->library('session');
		header('Content-Type: application/json; charset=UTF-8');

		if (!$this->session->userdata('user'))
		{
			echo json_encode(array('success' => FALSE, 'message' => 'session_expired'));
			return;
		}

		$order_id = (int) $this->input->post('order_id', TRUE);
		$event_type = trim((string) $this->input->post('event_type', TRUE));
		if ($event_type === '')
		{
			$event_type = 'order_booked';
		}

		$this->load->library('notification_service');
		$result = $this->notification_service->dispatch_for_order($order_id, $event_type);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function bridge_status_json()
	{
		// Hard ceiling — never let a hung bridge freeze the whole ERP page.
		@set_time_limit(3);

		$this->load->library('session');
		header('Content-Type: application/json; charset=UTF-8');

		if (!$this->session->userdata('user'))
		{
			echo json_encode(array('success' => FALSE, 'message' => 'session_expired'));
			return;
		}

		$this->load->library('whatsapp_bridge');

		// Manual refresh / Start / Reset may pass force=1 to clear cool-down.
		// Automatic polls must NOT reset the circuit or PHP freezes on a dead bridge.
		$force = $this->input->get_post('force');
		if ($force === '1' || $force === 'true')
		{
			$this->whatsapp_bridge->reset_circuit();
			$this->whatsapp_bridge->clear_status_cache();
		}

		if ($this->whatsapp_bridge->is_circuit_open())
		{
			echo json_encode(array(
				'success' => TRUE,
				'status' => array(
					'ok' => FALSE,
					'ready' => FALSE,
					'state' => 'disconnected',
					'has_qr' => FALSE,
					'error' => 'circuit_open',
				),
				'qr' => '',
				'qr_at' => '',
			), JSON_UNESCAPED_UNICODE);
			return;
		}

		$status = $this->whatsapp_bridge->status();
		$qr = array();
		// Only fetch QR image when needed — status alone is enough while connected.
		if (empty($status['ready']) && !empty($status['ok']) && !empty($status['has_qr']))
		{
			$qr = $this->whatsapp_bridge->qr();
			if (is_array($qr) && !empty($qr['qr']))
			{
				$status['ok'] = TRUE;
				$status['has_qr'] = TRUE;
				$status['state'] = isset($qr['state']) && $qr['state'] !== ''
					? $qr['state']
					: 'qr';
			}
		}
		elseif (empty($status['ready']) && !empty($status['ok']) && empty($status['has_qr']))
		{
			// Waiting for QR — one light status is enough; skip heavy /qr until has_qr.
		}

		echo json_encode(array(
			'success' => TRUE,
			'status' => $status,
			'qr' => isset($qr['qr']) ? $qr['qr'] : '',
			'qr_at' => isset($qr['qr_at']) ? $qr['qr_at'] : '',
		), JSON_UNESCAPED_UNICODE);
	}

	public function bridge_start()
	{
		$this->load->library('session');
		header('Content-Type: application/json; charset=UTF-8');

		if (!$this->session->userdata('user'))
		{
			echo json_encode(array('success' => FALSE, 'message' => 'session_expired'));
			return;
		}

		$this->load->library('whatsapp_bridge');
		$result = $this->whatsapp_bridge->start_process();
		// Include fresh status+qr so the modal can show QR without waiting for another poll.
		$status = $this->whatsapp_bridge->status();
		$qr = array();
		if (empty($status['ready']))
		{
			$qr = $this->whatsapp_bridge->qr();
			if (is_array($qr) && !empty($qr['qr']))
			{
				$status['ok'] = TRUE;
				$status['has_qr'] = TRUE;
				$status['state'] = isset($qr['state']) && $qr['state'] !== '' ? $qr['state'] : 'qr';
			}
		}
		echo json_encode(array(
			'success' => !empty($result['ok']),
			'result' => $result,
			'status' => $status,
			'qr' => isset($qr['qr']) ? $qr['qr'] : '',
			'qr_at' => isset($qr['qr_at']) ? $qr['qr_at'] : '',
		), JSON_UNESCAPED_UNICODE);
	}

	public function bridge_restart()
	{
		$this->load->library('session');
		header('Content-Type: application/json; charset=UTF-8');

		if (!$this->session->userdata('user'))
		{
			echo json_encode(array('success' => FALSE, 'message' => 'session_expired'));
			return;
		}

		$clear = $this->input->post('clear_session');
		$clear_session = ($clear === NULL || $clear === '' || $clear === '1' || $clear === 'true' || $clear === TRUE);

		$this->load->library('whatsapp_bridge');
		$this->whatsapp_bridge->clear_status_cache();
		$result = $this->whatsapp_bridge->restart_or_start($clear_session);
		echo json_encode($this->bridge_action_payload($result), JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Hard clear auth/session and regenerate a fresh QR (never reuse stale image).
	 */
	public function bridge_reset_qr()
	{
		$this->load->library('session');
		header('Content-Type: application/json; charset=UTF-8');

		if (!$this->session->userdata('user'))
		{
			echo json_encode(array('success' => FALSE, 'message' => 'session_expired'));
			return;
		}

		$this->load->library('whatsapp_bridge');
		$this->whatsapp_bridge->clear_status_cache();
		$result = $this->whatsapp_bridge->reset_qr();
		echo json_encode($this->bridge_action_payload($result), JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @param array $result
	 * @return array
	 */
	protected function bridge_action_payload(array $result)
	{
		$status = isset($result['status']) && is_array($result['status'])
			? $result['status']
			: array();
		$qr = isset($result['qr']) ? (string) $result['qr'] : '';

		if (empty($status) || (empty($status['ready']) && $qr === '' && empty($status['has_qr'])))
		{
			$this->load->library('whatsapp_bridge');
			$status = $this->whatsapp_bridge->status();
			if (empty($status['ready']))
			{
				$qr_payload = $this->whatsapp_bridge->qr();
				if (is_array($qr_payload) && !empty($qr_payload['qr']))
				{
					$qr = (string) $qr_payload['qr'];
					$status['ok'] = TRUE;
					$status['has_qr'] = TRUE;
					$status['state'] = isset($qr_payload['state']) && $qr_payload['state'] !== ''
						? $qr_payload['state']
						: 'qr';
				}
			}
		}

		if ($qr !== '')
		{
			$status['ok'] = TRUE;
			$status['has_qr'] = TRUE;
			if (empty($status['state']))
			{
				$status['state'] = 'qr';
			}
		}

		return array(
			'success' => !empty($result['ok']),
			'result' => $result,
			'status' => $status,
			'qr' => $qr,
			'qr_at' => isset($result['qr_at']) ? (string) $result['qr_at'] : '',
		);
	}
}

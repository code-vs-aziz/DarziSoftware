<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Advanced Reports API — PDF export, WhatsApp share, quick open.
 * Does not replace legacy Cash/* URLs.
 */
class Reports extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->library('report_registry');
		$this->load->library('report_data_service');
		$this->load->library('report_pdf_service');
		$this->load->library('whatsapp_bridge');
	}

	protected function require_user_json()
	{
		header('Content-Type: application/json; charset=UTF-8');
		if (!$this->session->userdata('user'))
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'session'));
			return FALSE;
		}
		if (!$this->rbac->can('reports.menu'))
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'permission'));
			return FALSE;
		}
		return TRUE;
	}

	protected function parse_range()
	{
		$preset = trim((string) $this->input->post_get('preset', TRUE));
		$from = trim((string) $this->input->post_get('from', TRUE));
		$to = trim((string) $this->input->post_get('to', TRUE));
		if ($from === '')
		{
			$from = trim((string) $this->input->post_get('fromdate', TRUE));
		}
		if ($to === '')
		{
			$to = trim((string) $this->input->post_get('todate', TRUE));
		}
		if ($preset !== '' && ($from === '' || $to === ''))
		{
			$resolved = $this->report_registry->resolve_preset($preset);
			if ($resolved)
			{
				$from = $resolved['from'];
				$to = $resolved['to'];
			}
		}
		return $this->report_registry->validate_dates($from, $to);
	}

	/**
	 * POST/GET: key, from, to | preset → JSON { ok, url }
	 */
	public function open()
	{
		if (!$this->require_user_json())
		{
			return;
		}
		$key = trim((string) $this->input->post_get('key', TRUE));
		if (!$this->report_registry->get($key))
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'unknown_report'));
			return;
		}
		if (!$this->report_registry->can_access($key))
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'permission'));
			return;
		}
		$item = $this->report_registry->get($key);
		$needs = isset($item['needs']) ? $item['needs'] : array();
		if (in_array('form', $needs, TRUE))
		{
			echo json_encode(array(
				'ok' => TRUE,
				'url' => site_url($item['form_url']),
				'needs_form' => TRUE,
			));
			return;
		}
		$range = $this->parse_range();
		if (empty($range['ok']))
		{
			echo json_encode(array('ok' => FALSE, 'error' => $range['error']));
			return;
		}
		$url = $this->report_registry->build_url($key, $range['from'], $range['to']);
		echo json_encode(array(
			'ok' => TRUE,
			'url' => $url,
			'from' => $range['from'],
			'to' => $range['to'],
			'needs_form' => FALSE,
		));
	}

	/**
	 * Generate PDF and stream download or return JSON path.
	 */
	public function pdf()
	{
		$as_json = (string) $this->input->post_get('json', TRUE) === '1';
		if ($as_json)
		{
			if (!$this->require_user_json())
			{
				return;
			}
		}
		else
		{
			if (!$this->session->userdata('user') || !$this->rbac->can('reports.menu'))
			{
				show_error('Unauthorized', 403);
				return;
			}
		}

		$key = trim((string) $this->input->post_get('key', TRUE));
		if (!$this->report_registry->can_access($key))
		{
			if ($as_json)
			{
				echo json_encode(array('ok' => FALSE, 'error' => 'permission'));
				return;
			}
			show_error('Permission denied', 403);
			return;
		}
		$item = $this->report_registry->get($key);
		if (!$item || empty($item['builder']))
		{
			if ($as_json)
			{
				echo json_encode(array('ok' => FALSE, 'error' => 'pdf_unsupported'));
				return;
			}
			show_error('PDF not available for this report. Open the interactive report instead.', 400);
			return;
		}
		$range = $this->parse_range();
		if (empty($range['ok']))
		{
			if ($as_json)
			{
				echo json_encode(array('ok' => FALSE, 'error' => $range['error']));
				return;
			}
			show_error('Invalid date range', 400);
			return;
		}

		$payload = $this->report_data_service->build($item['builder'], $range['from'], $range['to']);
		$file = $this->report_pdf_service->generate_file($payload, $key);
		if (empty($file['ok']))
		{
			if ($as_json)
			{
				echo json_encode(array('ok' => FALSE, 'error' => isset($file['error']) ? $file['error'] : 'pdf_failed'));
				return;
			}
			show_error('PDF generation failed', 500);
			return;
		}

		if ($as_json)
		{
			echo json_encode(array(
				'ok' => TRUE,
				'filename' => $file['filename'],
				'url' => $file['url'],
				'empty' => !empty($payload['meta']['empty']),
			));
			return;
		}

		$this->load->helper('download');
		force_download($file['filename'], file_get_contents($file['path']));
	}

	/**
	 * Generate PDF and send to owner phone (or provided phone) via WhatsApp bridge.
	 */
	public function send_whatsapp()
	{
		if (!$this->require_user_json())
		{
			return;
		}
		$key = trim((string) $this->input->post('key', TRUE));
		if (!$this->report_registry->can_access($key))
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'permission'));
			return;
		}
		$item = $this->report_registry->get($key);
		if (!$item || empty($item['builder']))
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'pdf_unsupported'));
			return;
		}
		$range = $this->parse_range();
		if (empty($range['ok']))
		{
			echo json_encode(array('ok' => FALSE, 'error' => $range['error']));
			return;
		}

		$phone = trim((string) $this->input->post('phone', TRUE));
		if ($phone === '' && function_exists('dz_env'))
		{
			$phone = trim((string) dz_env('REPORT_OWNER_PHONE', ''));
		}
		if ($phone === '')
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'missing_phone'));
			return;
		}

		$payload = $this->report_data_service->build($item['builder'], $range['from'], $range['to']);
		if (empty($payload['ok']))
		{
			echo json_encode(array('ok' => FALSE, 'error' => isset($payload['error']) ? $payload['error'] : 'build_failed'));
			return;
		}

		// Empty reports are still generated and sent (explicit "No data" caption).
		$is_empty = !empty($payload['meta']['empty']);
		$file = $this->report_pdf_service->generate_file($payload, $key);
		if (empty($file['ok']))
		{
			echo json_encode(array('ok' => FALSE, 'error' => isset($file['error']) ? $file['error'] : 'pdf_failed'));
			return;
		}

		$caption = (isset($payload['title']) ? $payload['title'] : 'Report')
			.' ('.$range['from'].' → '.$range['to'].')';
		if ($is_empty)
		{
			$caption .= ' — '.t('report_no_data');
		}

		$this->load->library('notification_service');
		$queued = $this->notification_service->queue_document($phone, $caption, $file['path'], array(
			'event_type' => 'report_pdf_document',
			'order_id' => 0,
			'trigger_source' => 'reports_send_whatsapp:'.$key,
		));
		if (empty($queued['queued']))
		{
			echo json_encode(array(
				'ok' => FALSE,
				'error' => isset($queued['skipped']) ? (string) $queued['skipped'] : 'queue_failed',
				'url' => isset($file['url']) ? $file['url'] : '',
			));
			return;
		}

		// Best-effort kick if HTTP cron secret is configured.
		$secret = function_exists('dz_env') ? trim((string) dz_env('CRON_HTTP_SECRET', '')) : '';
		if ($secret !== '')
		{
			$url = site_url('cron/process_notifications/10').'?secret='.rawurlencode($secret);
			$ch = curl_init($url);
			if ($ch !== FALSE)
			{
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_TIMEOUT, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
				@curl_exec($ch);
				@curl_close($ch);
			}
		}

		echo json_encode(array(
			'ok' => TRUE,
			'mode' => 'queued',
			'queued' => TRUE,
			'queue_id' => isset($queued['queue_id']) ? (int) $queued['queue_id'] : 0,
			'filename' => $file['filename'],
			'url' => isset($file['url']) ? $file['url'] : '',
			'empty' => $is_empty,
			'message' => 'queued',
		));
	}
}

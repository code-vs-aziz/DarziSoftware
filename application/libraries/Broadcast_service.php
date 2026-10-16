<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Slow WhatsApp broadcast over the local unofficial bridge.
 *
 * IMPORTANT: whatsapp-web.js is not the official Cloud API. Bulk marketing
 * can get the shop number banned. Defaults below are intentionally conservative.
 */
class Broadcast_service
{
	/** Soft default caps for unofficial bridge mode. */
	const DEFAULT_DAILY_CAP = 40;
	const HARD_DAILY_CAP = 80;
	const DEFAULT_HOUR_CAP = 20;
	const HARD_HOUR_CAP = 30;
	const DEFAULT_DELAY_MIN = 55;
	const DEFAULT_DELAY_MAX = 95;
	const HARD_DELAY_MIN = 40;
	const MAX_RECIPIENTS_PER_CAMPAIGN = 500;
	const WARN_RECIPIENTS = 50;
	const MAX_CONSECUTIVE_FAILURES = 3;
	const CRON_BATCH_SIZE = 2;

	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('Broadcast_model', 'broadcast_model');
		$this->CI->load->library('whatsapp_bridge');
	}

	public function normalize_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);
		if ($digits === '')
		{
			return '';
		}
		if (strpos($digits, '92') === 0 && strlen($digits) >= 12)
		{
			$digits = substr($digits, 0, 12);
		}
		elseif ($digits[0] === '0' && strlen($digits) >= 11)
		{
			$digits = '92'.substr($digits, 1, 10);
		}
		elseif (strlen($digits) === 10 && $digits[0] === '3')
		{
			$digits = '92'.$digits;
		}
		// Valid PK mobile: 923XXXXXXXXX
		if ( ! preg_match('/^923\d{9}$/', $digits))
		{
			return '';
		}
		return $digits;
	}

	public function personalize($template, $name)
	{
		$name = trim((string) $name);
		if ($name === '')
		{
			$name = 'Customer';
		}
		$out = str_replace(
			array('{{name}}', '{{NAME}}', '{name}'),
			$name,
			(string) $template
		);
		return trim($out);
	}

	public function clamp_safety_settings(array $in)
	{
		$delay_min = (int) (isset($in['delay_min_sec']) ? $in['delay_min_sec'] : self::DEFAULT_DELAY_MIN);
		$delay_max = (int) (isset($in['delay_max_sec']) ? $in['delay_max_sec'] : self::DEFAULT_DELAY_MAX);
		$daily = (int) (isset($in['daily_cap']) ? $in['daily_cap'] : self::DEFAULT_DAILY_CAP);
		$hour = (int) (isset($in['hour_cap']) ? $in['hour_cap'] : self::DEFAULT_HOUR_CAP);
		$win_start = (int) (isset($in['send_window_start']) ? $in['send_window_start'] : 10);
		$win_end = (int) (isset($in['send_window_end']) ? $in['send_window_end'] : 20);

		$delay_min = max(self::HARD_DELAY_MIN, min(300, $delay_min));
		$delay_max = max($delay_min, min(600, $delay_max));
		$daily = max(1, min(self::HARD_DAILY_CAP, $daily));
		$hour = max(1, min(self::HARD_HOUR_CAP, $hour));
		$win_start = max(0, min(23, $win_start));
		$win_end = max(1, min(24, $win_end));
		if ($win_end <= $win_start)
		{
			$win_end = min(24, $win_start + 8);
		}

		return array(
			'delay_min_sec' => $delay_min,
			'delay_max_sec' => $delay_max,
			'daily_cap' => $daily,
			'hour_cap' => $hour,
			'send_window_start' => $win_start,
			'send_window_end' => $win_end,
		);
	}

	public function create_and_queue($title, $message, array $customer_codes, array $settings, $created_by, $risk_ack)
	{
		if ( ! $this->CI->broadcast_model->has_tables())
		{
			return array('ok' => FALSE, 'error' => 'missing_table');
		}
		if ( ! $risk_ack)
		{
			return array('ok' => FALSE, 'error' => 'risk_not_acknowledged');
		}
		$message = trim((string) $message);
		if ($message === '' || mb_strlen($message) < 5)
		{
			return array('ok' => FALSE, 'error' => 'empty_message');
		}
		if (mb_strlen($message) > 3500)
		{
			return array('ok' => FALSE, 'error' => 'message_too_long');
		}

		$safe = $this->clamp_safety_settings($settings);
		$customers = $this->CI->broadcast_model->customers_by_codes($customer_codes);
		$recipients = array();
		$seen = array();
		foreach ($customers as $c)
		{
			$norm = $this->normalize_phone(isset($c['Phone']) ? $c['Phone'] : '');
			if ($norm === '' || isset($seen[$norm]))
			{
				continue;
			}
			$seen[$norm] = TRUE;
			$name = isset($c['name']) ? (string) $c['name'] : '';
			$recipients[] = array(
				'customer_code' => (int) $c['code'],
				'customer_name' => $name,
				'phone' => (string) $c['Phone'],
				'phone_normalized' => $norm,
				'personalized_message' => $this->personalize($message, $name),
			);
			if (count($recipients) >= self::MAX_RECIPIENTS_PER_CAMPAIGN)
			{
				break;
			}
		}
		if ( ! $recipients)
		{
			return array('ok' => FALSE, 'error' => 'no_valid_phones');
		}

		$id = $this->CI->broadcast_model->create_campaign(array(
			'title' => trim((string) $title) !== '' ? trim((string) $title) : 'Broadcast '.date('Y-m-d H:i'),
			'message' => $message,
			'status' => 'queued',
			'delay_min_sec' => $safe['delay_min_sec'],
			'delay_max_sec' => $safe['delay_max_sec'],
			'daily_cap' => $safe['daily_cap'],
			'hour_cap' => $safe['hour_cap'],
			'send_window_start' => $safe['send_window_start'],
			'send_window_end' => $safe['send_window_end'],
			'risk_acknowledged' => 1,
			'created_by' => (string) $created_by,
		));
		if ($id < 1)
		{
			return array('ok' => FALSE, 'error' => 'create_failed');
		}

		$added = $this->CI->broadcast_model->add_recipients($id, $recipients);
		return array(
			'ok' => TRUE,
			'campaign_id' => $id,
			'recipients' => $added,
			'warned' => $added > self::WARN_RECIPIENTS,
		);
	}

	public function set_status($campaign_id, $status)
	{
		$allowed = array('queued', 'running', 'paused', 'cancelled', 'completed');
		if ( ! in_array($status, $allowed, TRUE))
		{
			return FALSE;
		}
		$data = array('status' => $status);
		if ($status === 'cancelled' || $status === 'completed')
		{
			$data['finished_at'] = date('Y-m-d H:i:s');
		}
		if ($status === 'queued' || $status === 'running')
		{
			$data['consecutive_failures'] = 0;
			$data['finished_at'] = NULL;
		}
		return $this->CI->broadcast_model->update_campaign((int) $campaign_id, $data);
	}

	/**
	 * Process a tiny batch for one or more campaigns. Designed for cron every minute.
	 */
	public function process($limit = self::CRON_BATCH_SIZE)
	{
		$limit = max(1, min(5, (int) $limit));
		if ( ! $this->CI->broadcast_model->has_tables())
		{
			return array('processed' => 0, 'reason' => 'missing_table');
		}
		if ( ! $this->CI->whatsapp_bridge->is_enabled())
		{
			return array('processed' => 0, 'reason' => 'bridge_disabled');
		}
		if ($this->CI->whatsapp_bridge->is_circuit_open())
		{
			return array('processed' => 0, 'reason' => 'circuit_open');
		}
		$st = $this->CI->whatsapp_bridge->status();
		if (empty($st['ready']))
		{
			return array('processed' => 0, 'reason' => 'bridge_not_ready');
		}

		$hour = (int) date('G');
		$day_sent = $this->CI->broadcast_model->count_sent_since(date('Y-m-d 00:00:00'));
		$hour_sent = $this->CI->broadcast_model->count_sent_since(date('Y-m-d H:00:00'));
		$processed = 0;
		$campaigns = $this->CI->broadcast_model->active_campaigns();

		foreach ($campaigns as $campaign)
		{
			if ($processed >= $limit)
			{
				break;
			}
			$cid = (int) $campaign['id'];
			$daily_cap = min(self::HARD_DAILY_CAP, max(1, (int) $campaign['daily_cap']));
			$hour_cap = min(self::HARD_HOUR_CAP, max(1, (int) $campaign['hour_cap']));
			$win_start = (int) $campaign['send_window_start'];
			$win_end = (int) $campaign['send_window_end'];

			if ($hour < $win_start || $hour >= $win_end)
			{
				continue;
			}
			if ($day_sent >= $daily_cap || $hour_sent >= $hour_cap)
			{
				continue;
			}

			// Respect inter-message delay (randomized between min/max, sticky per campaign state).
			if ( ! empty($campaign['last_sent_at']))
			{
				$elapsed = time() - strtotime($campaign['last_sent_at']);
				$delay_min = max(self::HARD_DELAY_MIN, (int) $campaign['delay_min_sec']);
				$delay_max = max($delay_min, (int) $campaign['delay_max_sec']);
				$need = $delay_min + ((int) $campaign['sent_count'] % max(1, ($delay_max - $delay_min + 1)));
				if ($elapsed < $need)
				{
					continue;
				}
			}

			if ($campaign['status'] === 'queued')
			{
				$this->CI->broadcast_model->update_campaign($cid, array(
					'status' => 'running',
					'started_at' => ! empty($campaign['started_at']) ? $campaign['started_at'] : date('Y-m-d H:i:s'),
				));
			}

			$recipient = $this->CI->broadcast_model->next_pending_recipient($cid);
			if ( ! $recipient)
			{
				$this->CI->broadcast_model->update_campaign($cid, array(
					'status' => 'completed',
					'finished_at' => date('Y-m-d H:i:s'),
				));
				continue;
			}

			$msg = trim((string) $recipient['personalized_message']);
			if ($msg === '')
			{
				$msg = $this->personalize($campaign['message'], $recipient['customer_name']);
			}
			$phone = $recipient['phone_normalized'];
			$rid = (int) $recipient['id'];

			$this->CI->broadcast_model->update_recipient($rid, array(
				'attempts' => ((int) $recipient['attempts']) + 1,
			));

			$send = $this->CI->whatsapp_bridge->send($phone, $msg);
			if ( ! empty($send['ok']))
			{
				$now = date('Y-m-d H:i:s');
				$this->CI->broadcast_model->update_recipient($rid, array(
					'status' => 'sent',
					'sent_at' => $now,
					'error_message' => '',
				));
				$this->CI->broadcast_model->update_campaign($cid, array(
					'last_sent_at' => $now,
					'consecutive_failures' => 0,
				));
				$this->CI->broadcast_model->refresh_campaign_counts($cid);
				$day_sent++;
				$hour_sent++;
				$processed++;
				continue;
			}

			$error = isset($send['error']) ? (string) $send['error'] : 'send_failed';
			// Transient: leave pending, pause campaign lightly.
			if (in_array($error, array('circuit_open', 'bridge_unreachable', 'not_ready'), TRUE))
			{
				$this->CI->broadcast_model->update_recipient($rid, array(
					'error_message' => $error,
				));
				break;
			}

			$this->CI->broadcast_model->update_recipient($rid, array(
				'status' => 'failed',
				'error_message' => substr($error, 0, 240),
			));
			$fails = ((int) $campaign['consecutive_failures']) + 1;
			$update = array('consecutive_failures' => $fails);
			if ($fails >= self::MAX_CONSECUTIVE_FAILURES)
			{
				$update['status'] = 'paused';
			}
			$this->CI->broadcast_model->update_campaign($cid, $update);
			$this->CI->broadcast_model->refresh_campaign_counts($cid);
			$processed++;
			$campaign['consecutive_failures'] = $fails;
			if ($fails >= self::MAX_CONSECUTIVE_FAILURES)
			{
				break;
			}
		}

		return array('processed' => $processed, 'day_sent' => $day_sent, 'hour_sent' => $hour_sent);
	}
}

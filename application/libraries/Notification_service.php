<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rule-based customer messaging: queue + free local WhatsApp bridge dispatch.
 */
class Notification_service
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('Notification_model', 'notification_model');
		$this->CI->load->model('Business_config_model');
	}

	/**
	 * @param string $event_type
	 * @param int $order_id
	 * @param array $extra balance, due_date, customer_id, trigger_source, defer_dispatch
	 * @return array
	 */
	public function trigger($event_type, $order_id, array $extra = array())
	{
		$result = array(
			'queued' => FALSE,
			'queue_id' => 0,
			'dispatched' => FALSE,
			'message' => '',
			'whatsapp_url' => '',
			'auto_open' => FALSE,
			'skipped' => '',
			'send_mode' => '',
		);

		if (!$this->CI->notification_model->has_tables())
		{
			$result['skipped'] = 'tables_missing';
			return $result;
		}

		$settings = $this->CI->notification_model->get_settings();
		if (!$this->is_event_enabled($event_type, $settings))
		{
			$result['skipped'] = 'disabled';
			return $result;
		}

		$order_id = (int) $order_id;
		if ($order_id <= 0)
		{
			$result['skipped'] = 'invalid_order';
			return $result;
		}

		if (empty($extra['allow_resend']) && $this->CI->notification_model->recent_duplicate_exists(
			$event_type,
			$order_id,
			isset($extra['duplicate_minutes']) ? (int) $extra['duplicate_minutes'] : 360
		))
		{
			$result['skipped'] = 'duplicate';
			return $result;
		}

		$context = $this->build_context($order_id, $extra);
		if ($context === NULL)
		{
			$result['skipped'] = 'order_not_found';
			return $result;
		}

		$phone = $this->normalize_phone($context['phone']);
		if ($phone === '' || strlen($phone) < 11)
		{
			$this->CI->notification_model->queue_insert(array(
				'event_type' => $event_type,
				'order_id' => $order_id,
				'customer_id' => (int) $context['customer_id'],
				'phone' => '',
				'message' => '',
				'status' => 'skipped',
				'error_message' => 'missing_phone',
				'trigger_source' => isset($extra['trigger_source']) ? (string) $extra['trigger_source'] : '',
			));
			$result['skipped'] = 'missing_phone';
			return $result;
		}

		$message = $this->render_template($event_type, $settings, $context);
		$queue_id = $this->CI->notification_model->queue_insert(array(
			'event_type' => $event_type,
			'order_id' => $order_id,
			'customer_id' => (int) $context['customer_id'],
			'phone' => $phone,
			'message' => $message,
			'status' => 'pending',
			'trigger_source' => isset($extra['trigger_source']) ? (string) $extra['trigger_source'] : '',
		));

		$result['queued'] = $queue_id > 0;
		$result['queue_id'] = $queue_id;
		$result['message'] = $message;
		$result['send_mode'] = isset($settings['dispatch_mode']) ? (string) $settings['dispatch_mode'] : 'whatsapp_bridge';

		if (!empty($extra['defer_dispatch']))
		{
			return $result;
		}

		$dispatch = $this->dispatch_queue_row($queue_id, $settings, $phone, $message);
		$result['dispatched'] = !empty($dispatch['ok']);
		$result['whatsapp_url'] = isset($dispatch['whatsapp_url']) ? $dispatch['whatsapp_url'] : '';
		$result['auto_open'] = $this->should_auto_open($settings, $result['whatsapp_url']);

		return $result;
	}

	/**
	 * Send pending message when print slip loads (background AJAX).
	 *
	 * @return array
	 */
	public function dispatch_for_order($order_id, $event_type = 'order_booked')
	{
		$order_id = (int) $order_id;
		$event_type = (string) $event_type;

		if (!$this->CI->notification_model->has_tables() || $order_id <= 0)
		{
			return array('success' => FALSE, 'message' => 'invalid');
		}

		$row = $this->CI->notification_model->get_pending_for_order($order_id, $event_type);
		if ($row === NULL)
		{
			return array('success' => FALSE, 'message' => 'no_pending');
		}

		$settings = $this->CI->notification_model->get_settings();
		$dispatch = $this->dispatch_queue_row(
			(int) $row->id,
			$settings,
			(string) $row->phone,
			(string) $row->message,
			isset($row->attachment_path) ? (string) $row->attachment_path : ''
		);

		return array(
			'success' => !empty($dispatch['ok']),
			'message' => !empty($dispatch['ok']) ? 'sent' : (isset($dispatch['error']) ? $dispatch['error'] : 'failed'),
			'mode' => isset($settings['dispatch_mode']) ? $settings['dispatch_mode'] : '',
			'whatsapp_url' => isset($dispatch['whatsapp_url']) ? $dispatch['whatsapp_url'] : '',
		);
	}

	public function bridge_status()
	{
		$this->CI->load->library('whatsapp_bridge');
		return $this->CI->whatsapp_bridge->status();
	}

	/**
	 * Immediately dispatch one queue row (used after order PDF enqueue).
	 *
	 * @return array{ok:bool,error?:string}
	 */
	public function dispatch_queued_id($queue_id)
	{
		$queue_id = (int) $queue_id;
		if ($queue_id <= 0 || ! $this->CI->notification_model->has_tables())
		{
			return array('ok' => FALSE, 'error' => 'invalid_queue');
		}

		$row = $this->CI->notification_model->get_queue_row($queue_id);
		if ($row === NULL)
		{
			return array('ok' => FALSE, 'error' => 'queue_not_found');
		}
		if ((string) $row->status !== 'pending')
		{
			return array('ok' => ((string) $row->status === 'sent'), 'error' => (string) $row->status);
		}

		$settings = $this->CI->notification_model->get_settings();
		return $this->dispatch_queue_row(
			$queue_id,
			$settings,
			(string) $row->phone,
			(string) $row->message,
			isset($row->attachment_path) ? (string) $row->attachment_path : ''
		);
	}

	public function process_pending($limit = 20)
	{
		if (!$this->CI->notification_model->has_tables())
		{
			return array('processed' => 0, 'urls' => array());
		}

		$this->CI->load->library('whatsapp_bridge');
		if ($this->CI->whatsapp_bridge->is_circuit_open())
		{
			return array('processed' => 0, 'urls' => array(), 'skipped' => 'circuit_open');
		}

		$settings = $this->CI->notification_model->get_settings();
		$rows = $this->CI->notification_model->get_pending_queue($limit);
		$processed = 0;
		$urls = array();

		foreach ($rows as $row)
		{
			$dispatch = $this->dispatch_queue_row(
				(int) $row->id,
				$settings,
				(string) $row->phone,
				(string) $row->message,
				isset($row->attachment_path) ? (string) $row->attachment_path : ''
			);
			if (!empty($dispatch['ok']))
			{
				$processed++;
				if (!empty($dispatch['whatsapp_url']))
				{
					$urls[] = $dispatch['whatsapp_url'];
				}
			}
			elseif (isset($dispatch['error']) && $dispatch['error'] === 'circuit_open')
			{
				break;
			}
		}

		return array('processed' => $processed, 'urls' => $urls);
	}

	/**
	 * Queue a PDF/document for WhatsApp send via cron (does not call bridge in-request).
	 *
	 * @return array{queued:bool,queue_id:int,skipped?:string,message?:string}
	 */
	public function queue_document($phone, $caption, $file_path, array $extra = array())
	{
		$result = array(
			'queued' => FALSE,
			'queue_id' => 0,
			'skipped' => '',
			'message' => (string) $caption,
		);

		if (!$this->CI->notification_model->has_tables())
		{
			$result['skipped'] = 'tables_missing';
			return $result;
		}

		$phone = $this->normalize_phone($phone);
		$file_path = (string) $file_path;
		if ($phone === '' || strlen($phone) < 11)
		{
			$result['skipped'] = 'missing_phone';
			return $result;
		}
		if ($file_path === '' || !is_file($file_path))
		{
			$result['skipped'] = 'missing_file';
			return $result;
		}

		$this->CI->notification_model->ensure_attachment_column();
		$queue_id = $this->CI->notification_model->queue_insert(array(
			'event_type' => isset($extra['event_type']) ? (string) $extra['event_type'] : 'document_send',
			'order_id' => isset($extra['order_id']) ? (int) $extra['order_id'] : 0,
			'customer_id' => isset($extra['customer_id']) ? (int) $extra['customer_id'] : 0,
			'phone' => $phone,
			'message' => (string) $caption,
			'status' => 'pending',
			'attachment_path' => $file_path,
			'trigger_source' => isset($extra['trigger_source']) ? (string) $extra['trigger_source'] : 'document_queue',
		));

		$result['queued'] = $queue_id > 0;
		$result['queue_id'] = $queue_id;
		return $result;
	}

	protected function dispatch_queue_row($queue_id, array $settings, $phone, $message, $attachment_path = '')
	{
		$queue_id = (int) $queue_id;
		if ($queue_id <= 0)
		{
			return array('ok' => FALSE, 'error' => 'invalid_queue');
		}

		$mode = isset($settings['dispatch_mode']) ? (string) $settings['dispatch_mode'] : 'whatsapp_bridge';
		$now = date('Y-m-d H:i:s');
		$attachment_path = trim((string) $attachment_path);
		$event_type = '';

		// Prefer DB row values (fixes empty attachment when column was added mid-request).
		$row = $this->CI->notification_model->get_queue_row($queue_id);
		if ($row !== NULL)
		{
			$event_type = isset($row->event_type) ? (string) $row->event_type : '';
			if ($attachment_path === '' && isset($row->attachment_path))
			{
				$attachment_path = trim((string) $row->attachment_path);
			}
			if ($phone === '' && isset($row->phone))
			{
				$phone = (string) $row->phone;
			}
			if ($message === '' && isset($row->message))
			{
				$message = (string) $row->message;
			}
		}

		if ($mode === 'log')
		{
			$this->CI->notification_model->update_queue_row($queue_id, array(
				'status' => 'sent',
				'sent_at' => $now,
				'error_message' => 'log_mode',
			));
			return array('ok' => TRUE, 'whatsapp_url' => '');
		}

		if ($mode === 'whatsapp_bridge')
		{
			$this->CI->load->library('whatsapp_bridge');
			if (!$this->CI->whatsapp_bridge->is_enabled())
			{
				$this->CI->notification_model->update_queue_row($queue_id, array(
					'status' => 'failed',
					'error_message' => 'bridge_disabled',
				));
				return array('ok' => FALSE, 'error' => 'bridge_disabled');
			}

			if ($this->CI->whatsapp_bridge->is_circuit_open())
			{
				return array('ok' => FALSE, 'error' => 'circuit_open');
			}

			$wants_document = ($attachment_path !== '' || $event_type === 'order_pdf_document');
			if ($wants_document)
			{
				if ($attachment_path === '' || ! is_file($attachment_path))
				{
					$send = array('ok' => FALSE, 'error' => 'missing_attachment');
				}
				else
				{
					$send = $this->CI->whatsapp_bridge->send_document($phone, $attachment_path, $message);
					// Caption may have delivered while PDF failed — retry PDF once without caption.
					if (
						empty($send['ok'])
						&& ! empty($send['caption_sent'])
						&& is_file($attachment_path)
					)
					{
						$retry = $this->CI->whatsapp_bridge->send_document($phone, $attachment_path, '');
						if ( ! empty($retry['ok']))
						{
							$send = $retry;
						}
					}
				}
			}
			else
			{
				$send = $this->CI->whatsapp_bridge->send($phone, $message);
			}

			if (!empty($send['ok']))
			{
				$this->CI->notification_model->update_queue_row($queue_id, array(
					'status' => 'sent',
					'sent_at' => $now,
					'error_message' => ($attachment_path !== '') ? 'bridge_document_sent' : 'bridge_sent',
				));
				return array('ok' => TRUE, 'whatsapp_url' => '');
			}

			$error = isset($send['error']) ? (string) $send['error'] : 'bridge_send_failed';
			// Keep pending when circuit opens or bridge unreachable so cron can retry.
			if ($error === 'circuit_open' || $error === 'bridge_unreachable')
			{
				$this->CI->notification_model->update_queue_row($queue_id, array(
					'error_message' => $error,
				));
				return array('ok' => FALSE, 'error' => $error);
			}

			$this->CI->notification_model->update_queue_row($queue_id, array(
				'status' => 'failed',
				'error_message' => substr($error, 0, 250),
			));
			return array('ok' => FALSE, 'error' => $error);
		}

		$url = $this->build_whatsapp_url($phone, $message);
		$this->CI->notification_model->update_queue_row($queue_id, array(
			'status' => 'sent',
			'sent_at' => $now,
			'whatsapp_url' => $url,
		));

		return array('ok' => TRUE, 'whatsapp_url' => $url);
	}

	public function scan_payment_reminders()
	{
		if (!$this->CI->notification_model->has_tables())
		{
			return 0;
		}

		$settings = $this->CI->notification_model->get_settings();
		if (empty($settings['enabled_payment_reminder']))
		{
			return 0;
		}

		$days_before = max(0, (int) $settings['payment_reminder_days_before']);
		$target_date = date('Y-m-d', strtotime('+'.$days_before.' days'));

		$this->CI->db->select('dmst.trnno, dmst.order_id, dmst.customer_id, dmst.balance, dmst.NPAID_dATE');
		$this->CI->db->from('dmst');
		$this->CI->db->where('dmst.pending', 'YES');
		$this->CI->db->where('dmst.paidstatus !=', 'YES');
		$this->CI->db->where('dmst.balance >', 0);
		$this->CI->db->where('dmst.NPAID_dATE <=', $target_date);
		$rows = $this->CI->db->get()->result();

		$count = 0;
		foreach ($rows as $row)
		{
			$order_id = (int) $row->order_id;
			if ($order_id <= 0)
			{
				continue;
			}

			$res = $this->trigger('payment_reminder', $order_id, array(
				'balance' => (float) $row->balance,
				'due_date' => (string) $row->NPAID_dATE,
				'customer_id' => (int) $row->customer_id,
				'trigger_source' => 'cron_payment_reminder',
			));

			if (!empty($res['queued']))
			{
				$count++;
			}
		}

		return $count;
	}

	public function stash_delivery_flash(array $trigger_result)
	{
		$this->CI->load->library('session');

		if (!empty($trigger_result['skipped']))
		{
			$this->CI->session->set_flashdata('notify_delivery_status', 'skipped');
			$this->CI->session->set_flashdata('notify_delivery_detail', (string) $trigger_result['skipped']);
			return;
		}

		if (!empty($trigger_result['dispatched']))
		{
			$this->CI->session->set_flashdata('notify_delivery_status', 'sent');
			return;
		}

		if (!empty($trigger_result['queued']))
		{
			$this->CI->session->set_flashdata('notify_delivery_status', 'queued');
			return;
		}

		$this->CI->session->set_flashdata('notify_delivery_status', 'failed');
	}

	public function stash_whatsapp_flash(array $trigger_result)
	{
		if (empty($trigger_result['whatsapp_url']))
		{
			return;
		}

		$this->CI->load->library('session');
		$this->CI->session->set_flashdata('notify_whatsapp_url', $trigger_result['whatsapp_url']);
		if (!empty($trigger_result['message']))
		{
			$this->CI->session->set_flashdata('notify_message_preview', $trigger_result['message']);
		}
		if (!empty($trigger_result['auto_open']))
		{
			$this->CI->session->set_flashdata('notify_auto_open', '1');
		}
	}

	protected function should_auto_open(array $settings, $whatsapp_url)
	{
		if ($whatsapp_url === '')
		{
			return FALSE;
		}

		$mode = isset($settings['dispatch_mode']) ? (string) $settings['dispatch_mode'] : '';
		if ($mode !== 'whatsapp_link')
		{
			return FALSE;
		}

		return !empty($settings['auto_open_whatsapp']);
	}

	protected function build_context($order_id, array $extra)
	{
		$order_id = (int) $order_id;
		$this->CI->load->model('Order_query_model', 'order_query');

		$header = $this->CI->order_query->get_order_header($order_id);
		if (empty($header))
		{
			return NULL;
		}

		$phone = $this->get_order_customer_phone($order_id);
		$customer_id = isset($extra['customer_id']) ? (int) $extra['customer_id'] : 0;
		if ($customer_id <= 0 && isset($header->snap_customer_code))
		{
			$customer_id = (int) $header->snap_customer_code;
		}

		// When caller passes balance (incl. 0 after full payment at delivery), keep it.
		// Old bug: `if ($balance <= 0) recalc` turned a paid-off 0 into unpaid again.
		$balance_explicit = array_key_exists('balance', $extra);
		$balance = $balance_explicit ? (float) $extra['balance'] : 0.0;
		$total = isset($header->total) ? (float) $header->total : 0;
		$advance = isset($header->advance) ? (float) $header->advance : 0;
		$discount = isset($header->Discount) ? (float) $header->Discount : 0;
		$addprice = isset($header->addprice) ? (float) $header->addprice : 0;
		$bill_total = $addprice + $total - $discount;

		$received_later = 0.0;
		if (method_exists($this->CI->order_query, 'get_order_amount_received'))
		{
			$received_later = (float) $this->CI->order_query->get_order_amount_received($order_id);
		}
		$total_paid = $advance + $received_later;
		if ($total_paid < 0)
		{
			$total_paid = 0.0;
		}

		if ($balance_explicit)
		{
			if ($balance < 0)
			{
				$balance = 0.0;
			}
			// Delivery/reminder: show total collected so far (bill − remaining).
			$advance = max(0.0, $bill_total - $balance);
		}
		else
		{
			$balance = $bill_total - $total_paid;
			if ($balance < 0)
			{
				$balance = 0.0;
			}
			$advance = $total_paid;
		}

		$items_meta = $this->build_order_items_summary($order_id);
		$suit_count = $items_meta['total'];
		if ($suit_count <= 0 && isset($header->qty))
		{
			$suit_count = (int) $header->qty;
		}
		$order_items = $items_meta['summary'];
		if ($order_items === '' && $suit_count > 0)
		{
			$order_items = $suit_count.' '.($suit_count === 1 ? 'Suit' : 'Suits');
		}

		$due_date = isset($extra['due_date']) ? (string) $extra['due_date'] : '';
		if ($due_date === '' && isset($header->RDATE))
		{
			$due_date = (string) $header->RDATE;
		}

		$business = $this->CI->Business_config_model->get_config();
		$shop_name = isset($business['location_name']) ? trim((string) $business['location_name']) : '';
		$shop_phone = isset($business['phone']) ? trim((string) $business['phone']) : '';
		$shop_address = isset($business['address_urdu']) ? trim((string) $business['address_urdu']) : '';
		if ($shop_address === '' && $shop_name !== '')
		{
			$shop_address = $shop_name;
		}

		$customer_name = '';
		if (isset($header->snap_customer_name))
		{
			$customer_name = trim((string) $header->snap_customer_name);
		}

		$year = date('Y');
		// Website line ke turant neeche; blank line; then left-aligned bold credit.
		$copyright = "Since 2005\n\n*Powered by Aziz Ur Rehman.*";

		$naap_no = '';
		if (isset($header->man_napno))
		{
			$naap_no = trim((string) $header->man_napno);
		}

		return array(
			'order_no' => $order_id,
			'naap_no' => $naap_no,
			'return_date' => $this->format_display_date(isset($header->RDATE) ? $header->RDATE : ''),
			'due_date' => $this->format_display_date($due_date),
			'total' => number_format($total, 0),
			'bill_total' => number_format($bill_total, 0),
			'advance' => number_format($advance, 0),
			'balance' => number_format($balance, 0),
			'suit_count' => (string) $suit_count,
			'order_items' => $order_items,
			'customer_name' => $customer_name,
			'customer_id' => $customer_id,
			'phone' => $phone,
			'shop_name' => $shop_name,
			'shop_phone' => $shop_phone,
			'shop_address' => $shop_address,
			'shop_website' => 'www.darzibaba.pk',
			'year' => $year,
			'copyright' => $copyright,
		);
	}

	/**
	 * Build WhatsApp line like "2 Suits, 3 Waistcoats" from order_dtl.type rows.
	 *
	 * @return array{summary:string,total:int}
	 */
	protected function build_order_items_summary($order_id)
	{
		$order_id = (int) $order_id;
		$empty = array('summary' => '', 'total' => 0);
		if ($order_id <= 0)
		{
			return $empty;
		}

		$q = $this->CI->db->select('type')
			->from('order_dtl')
			->where('order_trnno', $order_id)
			->get();
		if ($q->num_rows() === 0)
		{
			return $empty;
		}

		// Display labels + preferred order (Kurta = Suit for shop messaging).
		$label_map = array(
			'Kurta' => array('singular' => 'Suit', 'plural' => 'Suits'),
			'Waistcoat' => array('singular' => 'Waistcoat', 'plural' => 'Waistcoats'),
			'Shirt' => array('singular' => 'Shirt', 'plural' => 'Shirts'),
			'Coat' => array('singular' => 'Coat', 'plural' => 'Coats'),
			'Pent' => array('singular' => 'Pant', 'plural' => 'Pants'),
			'PentCoat' => array('singular' => 'Pant Coat', 'plural' => 'Pant Coats'),
			'Pajama' => array('singular' => 'Pajama', 'plural' => 'Pajamas'),
			'Sherwani' => array('singular' => 'Sherwani', 'plural' => 'Sherwanis'),
		);
		$order_keys = array_keys($label_map);

		$counts = array();
		$total = 0;
		foreach ($q->result() as $row)
		{
			$type = trim((string) (isset($row->type) ? $row->type : ''));
			if ($type === '' || $type === '0')
			{
				continue;
			}
			// Normalize case (DB may store waistcoat / Waistcoat).
			foreach ($label_map as $key => $_labels)
			{
				if (strcasecmp($type, $key) === 0)
				{
					$type = $key;
					break;
				}
			}
			if (!isset($counts[$type]))
			{
				$counts[$type] = 0;
			}
			$counts[$type]++;
			$total++;
		}

		if ($total <= 0)
		{
			return $empty;
		}

		$parts = array();
		foreach ($order_keys as $key)
		{
			if (empty($counts[$key]))
			{
				continue;
			}
			$qty = (int) $counts[$key];
			$label = $qty === 1 ? $label_map[$key]['singular'] : $label_map[$key]['plural'];
			$parts[] = $qty.' '.$label;
			unset($counts[$key]);
		}
		// Any unexpected type left over.
		foreach ($counts as $type => $qty)
		{
			$qty = (int) $qty;
			if ($qty <= 0)
			{
				continue;
			}
			$parts[] = $qty.' '.$type.($qty === 1 ? '' : 's');
		}

		return array(
			'summary' => implode(', ', $parts),
			'total' => $total,
		);
	}

	protected function get_order_customer_phone($order_id)
	{
		$q = $this->CI->db->select('customer.Phone')
			->from('order_mst')
			->join('measurement', 'measurement.code = order_mst.naap_no')
			->join('customer', 'customer.code = measurement.customer_code')
			->where('order_mst.trnno', (int) $order_id)
			->limit(1)
			->get();

		if ($q->num_rows() === 0)
		{
			return '';
		}

		return (string) $q->row()->Phone;
	}

	protected function render_template($event_type, array $settings, array $context)
	{
		$key = 'template_'.$event_type;
		$template = isset($settings[$key]) ? (string) $settings[$key] : '';
		if ($template === '')
		{
			$defaults = $this->CI->notification_model->get_settings();
			$template = isset($defaults[$key]) ? (string) $defaults[$key] : '';
		}

		$replacements = array(
			'{order_no}' => (string) $context['order_no'],
			'{naap_no}' => isset($context['naap_no']) ? (string) $context['naap_no'] : '',
			'{return_date}' => (string) $context['return_date'],
			'{due_date}' => (string) $context['due_date'],
			'{balance}' => (string) $context['balance'],
			'{total}' => (string) $context['total'],
			'{bill_total}' => (string) $context['bill_total'],
			'{advance}' => (string) $context['advance'],
			'{suit_count}' => (string) $context['suit_count'],
			'{order_items}' => isset($context['order_items']) ? (string) $context['order_items'] : (string) $context['suit_count'],
			'{customer_name}' => (string) $context['customer_name'],
			'{shop_name}' => (string) $context['shop_name'],
			'{shop_phone}' => (string) $context['shop_phone'],
			'{shop_address}' => (string) $context['shop_address'],
			'{shop_website}' => isset($context['shop_website']) ? (string) $context['shop_website'] : 'www.darzibaba.pk',
			'{year}' => (string) $context['year'],
			'{copyright}' => (string) $context['copyright'],
		);

		$message = $template;
		foreach ($replacements as $token => $value)
		{
			$message = str_replace($token, $value, $message);
		}

		return trim($message);
	}

	protected function is_event_enabled($event_type, array $settings)
	{
		$map = array(
			'order_booked' => 'enabled_order_booked',
			'order_ready' => 'enabled_order_ready',
			'payment_reminder' => 'enabled_payment_reminder',
			'delivery_confirm' => 'enabled_delivery_confirm',
		);

		if (!isset($map[$event_type]))
		{
			return FALSE;
		}

		return !empty($settings[$map[$event_type]]);
	}

	protected function build_whatsapp_url($phone, $message)
	{
		$phone = $this->normalize_phone($phone);
		if ($phone === '')
		{
			return '';
		}

		return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
	}

	protected function normalize_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);
		if ($digits === '')
		{
			return '';
		}
		if (strpos($digits, '92') === 0 && strlen($digits) >= 12)
		{
			return $digits;
		}
		if ($digits[0] === '0' && strlen($digits) >= 11)
		{
			return '92'.substr($digits, 1);
		}
		if (strlen($digits) === 10 && $digits[0] === '3')
		{
			return '92'.$digits;
		}

		return $digits;
	}

	protected function format_display_date($value)
	{
		$value = trim((string) $value);
		if ($value === '' || $value === '0000-00-00')
		{
			return '';
		}

		$ts = strtotime($value);
		if ($ts === FALSE)
		{
			return $value;
		}

		return date('d M Y', $ts);
	}
}
